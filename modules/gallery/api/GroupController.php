<?php
/**
 * 素材分组管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace gallery\api;

use framework\common\BasicController;
use Yii;

class GroupController extends BasicController
{

    public $modelClass = 'gallery\models\GalleryGroup';

    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions           = parent::actions();
        $actions['create'] = [
            'class'       => 'yii\rest\CreateAction',
            'modelClass'  => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'scenario'    => 'create',
        ];
        $actions['update'] = [
            'class'       => 'yii\rest\UpdateAction',
            'modelClass'  => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'scenario'    => 'update',
        ];

        return $actions;
    }

    /**
     * 从写获取方法，获取全部分组
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $parent_id = Yii::$app->request->get('parent_id', -1);
        $parent_id = intval($parent_id);
        $type      = Yii::$app->request->get('type', -1);
        $type      = intval($type);

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];

        if ($parent_id !== -1) {
            $where['parent_id'] = $parent_id;
        }
        if ($type !== -1) {
            $where['type'] = $type;
        }
        // if (true) {
        //     $UID          = Yii::$app->user->identity->id;
        //     $where['UID'] = $UID;
        // }

        //获取顶级分组时 获取未分组
        if ($parent_id === 0 || $parent_id === -1) {
            $where = ['or', ['id' => 1], $where];
        }
        return $this->modelClass::find()->where($where)->orderBy(['sort' => SORT_DESC, 'created_time' => SORT_DESC])->asArray()->all();
    }

    /**
     * 删除重写
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->get('id', 0);
        $id = intval($id);

        if ($id === 1) {
            Error('该分组不可删除');
        }

        //判断是否存在子集
        $check = $this->modelClass::find()->where(['parent_id' => $id, 'is_deleted' => 0])->one();
        if (!empty($check)) {
            Error('存在子集，不可删除');
        }

        $model = $this->modelClass::findOne($id);
        if ($model) {
            $model->deleted_time = time();
            $model->is_deleted   = 1;
            if ($model->save()) {
                M('gallery', 'Gallery')::updateAll(['group_id' => 1], ['group_id' => $id]);
                return true;
            } else {
                Error('删除失败，请检查is_deleted字段是否存在');
            }
        } else {
            Error('删除失败，数据不存在');
        }
    }

    /**
     * 数据前置检查器
     * @param  [type]  $operation    [description]
     * @param  array   $params       [description]
     * @param  boolean $allowCaching [description]
     * @return [type]                [description]
     */
    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        switch ($operation) {
            case 'create':
                $post  = Yii::$app->request->post();
                $check = $this->modelClass::find()->where(['name' => $post['name'], 'is_deleted' => 0, 'merchant_id' => 1, 'type' => $post['type'], 'parent_id' => $post['parent_id']])->exists();
                if (!empty($check)) {
                    Error('分组名已存在');
                }
                if (!empty($post['parent_id'])) {
                    if ($post['parent_id'] === 1) {
                        Error('未分组下不含子目录');
                    }
                    $parent_info = $this->modelClass::find()->where(['id' => $post['parent_id'], 'is_deleted' => 0])->one();
                    if (!empty($parent_info)) {
                        //根据父级path中-的数量，判断时候还可以在添加
                        if (substr_count($parent_info['path'], '-') >= 2) {
                            Error('分组超过三级，无法添加');
                        }
                        $post['path'] = $parent_info['path'] . '-' . $post['parent_id'];
                    } else {
                        Error('父级分组不存在');
                    }
                }

                $post['UID']         = Yii::$app->user->identity->id;
                $post['merchant_id'] = 1;
                $post['AppID']       = Yii::$app->params['AppID'];
                Yii::$app->request->setBodyParams($post);
                break;

            case 'update':
                $get = Yii::$app->request->get();
                $id  = intval($get['id']);
                if ($id === 1) {
                    Error('该分组不可编辑');
                }
                $name = Yii::$app->request->post('name', false);
                if ($name) {
                    $type      = Yii::$app->request->post('type', false);
                    $parent_id = Yii::$app->request->post('parent_id', false);
                    $check     = $this->modelClass::find()->where(['and', ['<>', 'id', $id], ['name' => $name, 'is_deleted' => 0, 'merchant_id' => 1, 'type' => $type, 'parent_id' => $parent_id]])->exists();
                    if (!empty($check)) {
                        Error('分组名已存在');
                    }
                }

                break;
        }
    }
}
