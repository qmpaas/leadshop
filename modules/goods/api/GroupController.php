<?php
/**
 * 商品分组管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace goods\api;

use framework\common\BasicController;
use Yii;

class GroupController extends BasicController
{
    public $modelClass = 'goods\models\GoodsGroup';

    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 从写获取方法，获取全部分组
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $get         = Yii::$app->request->get();
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];
        if (isset($get['parent'])) {
            $where['parent_id'] = 0;
        }
        if (isset($get['is_show'])) {
            $where['is_show'] = 1;
        }
        $data = $this->modelClass::find()->where($where)->orderBy(['id' => SORT_ASC])->select('id,name,parent_id,goods_show,icon,image,path,type,sort,is_show')->asArray()->all();
        //将所有返回内容中的本地地址代替字符串替换为域名
        $data = str2url($data);
        return $data;
    }

    public function actionView()
    {
        $id = Yii::$app->request->get('id', 0);
        $id = intval($id);

        $result = $this->modelClass::find()->where(['id' => $id])->asArray()->one();

        if (empty($result)) {
            Error('内容不存在');
        }

        $goods_check = M('goods', 'Goods')::find()->where(['and', ['is_deleted' => 0], ['like', 'group', '-' . $result['id'] . '-']])->exists();

        $result['goods_check'] = $goods_check;

        return str2url($result);
    }

    /**
     * 删除重写
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $get = Yii::$app->request->get();

        $id = intval($get['id']);

        //判断是否存在子集
        $check = $this->modelClass::find()->where(['parent_id' => $id, 'is_deleted' => 0])->exists();
        if ($check) {
            Error('存在子分类，不可删除');
        }

        //判断是否存在商品
        $check_goods = M('goods', 'Goods')::find()->where(['and', ['like', 'group', '-' . $id . '-'], ['is_deleted' => 0]])->exists();
        if ($check_goods) {
            Error('存在商品，不可删除');
        }

        $model = $this->modelClass::findOne($id);
        if ($model) {
            $model->deleted_time = time();
            $model->is_deleted   = 1;
            if ($model->save()) {
                return true;
            } else {
                Error('删除失败，请检查is_deleted字段是否存在');
            }
        } else {
            Error('删除失败，数据不存在');
        }
    }

    public function actionCreate()
    {
        $post = Yii::$app->request->post();

        $post = url2str($post);

        if (!empty($post['parent_id'])) {
            $parent_info = $this->modelClass::find()->where(['id' => $post['parent_id'], 'is_deleted' => 0])->asArray()->one();
            if (!empty($parent_info)) {
                //根据父级path中-的数量，判断时候还可以在添加
                if (substr_count($parent_info['path'], '-') >= 2) {
                    Error('分组超过三级，无法添加');
                }
                $post['path'] = $parent_info['path'] . '-' . $post['parent_id'];
                $post['type'] = $parent_info['type'];
            } else {
                Error('父级分组不存在');
            }
        }

        $merchant_id         = 1;
        $post['merchant_id'] = $merchant_id;
        $check               = $this->modelClass::find()->where(['name' => $post['name'], 'parent_id' => $post['parent_id'], 'is_deleted' => 0, 'merchant_id' => $merchant_id])->exists();
        if ($check) {
            Error('同一级分类名不能重复');
        }
        $post['AppID'] = Yii::$app->params['AppID'];

        $model = M('goods', 'GoodsGroup', true);
        $model->setScenario('create');
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                return true;
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    public function actionUpdate()
    {
        $post = Yii::$app->request->post();

        $id    = Yii::$app->request->get('id');
        $model = $this->modelClass::findOne($id);
        if (N('name')) {
            $check = $this->modelClass::find()->where(['and', ['name' => $post['name'], 'parent_id' => $model->parent_id, 'is_deleted' => 0, 'merchant_id' => $model->merchant_id], ['<>', 'id', $id]])->exists();
            if ($check) {
                Error('同一级分类名不能重复');
            }
        }

        $is_show = $model->is_show;

        $t    = Yii::$app->db->beginTransaction();
        $post = url2str($post);
        $model->setScenario('update');
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                if (isset($post['is_show']) && $post['is_show'] != $is_show) {
                    $children = $this->modelClass::find()->where(['parent_id' => $id,'is_show'=>$is_show])->select('id')->asArray()->all();
                    $children = array_column($children, 'id');
                    if (count($children)) {
                        $children2 = $this->modelClass::find()->where(['parent_id' => $children,'is_show'=>$is_show])->select('id')->asArray()->all();
                        $children2 = array_column($children2, 'id');
                        $children  = array_merge($children, $children2);
                        $batch_res = $this->modelClass::updateAll(['is_show' => $post['is_show']], ['id' => $children]);
                        if ($batch_res != count($children)) {
                            $t->rollBack();
                            Error('保存失败');
                        }
                    }
                }
                $t->commit();
                return true;
            } else {
                $t->rollBack();
                Error('保存失败');
            }

        }
        return $model;
    }

}
