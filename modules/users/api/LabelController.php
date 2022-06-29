<?php
/**
 * 用户标签控制器
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace users\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class LabelController extends BasicController
{
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 标签列表
     */
    public function actionSearch()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['AppID' => $AppID, 'merchant_id' => $merchant_id, 'is_deleted' => 0];

        $type = Yii::$app->request->post('type', false);
        if ($type) {
            $where = ['and', $where, ['type' => $type]];
        }

        $name = Yii::$app->request->post('name', false);
        if ($name) {
            $where = ['and', $where, ['like', 'name', $name]];
        }

        //处理排序
        $sort    = Yii::$app->request->post('sort', []);
        $sort    = is_array($sort) ? $sort : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['created_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                if (!sql_check($key)) {
                    $orderBy[$key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
                }
            }
        }

        $data = new ActiveDataProvider(
            [
                'query'      => M('users', 'Label')::find()
                    ->select('id,name,users_number,status,conditions_status,conditions_setting,created_time')
                    ->where($where)
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['conditions_setting'] = to_array($value['conditions_setting']);
        }
        $data->setModels($list);
        return $data;

    }

    public function actionIndex()
    {
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['AppID' => $AppID, 'merchant_id' => $merchant_id, 'is_deleted' => 0];

        $type = Yii::$app->request->get('type', false);
        if ($type) {
            $where = ['and', $where, ['type' => $type]];
        }

        $name = Yii::$app->request->get('name', false);
        if ($name) {
            $where = ['and', $where, ['like', 'name', $name]];
        }

        return M('users', 'Label')::find()->select('id,name')->where($where)->asArray()->all();
    }

    /**
     * 标签详情
     */
    public function actionView()
    {
        $id          = Yii::$app->request->get('id', 0);
        $id          = intval($id);
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $data        = M('users', 'Label')::find()->where(['id' => $id, 'is_deleted' => 0])->asArray()->one();
        if (empty($data)) {
            Error('标签不存在');
        }

        if ($data['type'] == 2) {
            $data['filter_user']        = to_array($data['filter_user']);
            $data['conditions_setting'] = to_array($data['conditions_setting']);

            if (!empty($data['filter_user'])) {
                $users               = M('users', 'User')::find()->where(['id' => $data['filter_user'], 'AppID' => $AppID])->select('id,nickname')->asArray()->all();
                $data['filter_user'] = $users;
            }

            $setting = $data['conditions_setting'];
            $where   = [
                'is_deleted'  => 0,
                'merchant_id' => $merchant_id,
                'AppID'       => $AppID,
            ];
            if (!empty($setting['shopping_goods']['value'])) {
                $goods = M('goods', 'Goods')::find()->where(['and', $where, ['id' => $setting['shopping_goods']['value']]])->select('id,name,slideshow,price,stocks')->asArray()->all();
                foreach ($goods as &$v) {
                    $v['slideshow'] = str2url(to_array($v['slideshow']));
                }
                $setting['shopping_goods']['value'] = $goods;
            }
            if (!empty($setting['shopping_group']['value'])) {
                $group                              = M('goods', 'GoodsGroup')::find()->where(['and', $where, ['id' => $setting['shopping_group']['value']]])->select('id,name')->asArray()->all();
                $setting['shopping_group']['value'] = $group;
            }

            $data['conditions_setting'] = $setting;

        }

        return $data;
    }

    /**
     * 创建标签
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $post        = Yii::$app->request->post();
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];

        $check = M('users', 'Label')::find()->where(['is_deleted' => 0, 'merchant_id' => $merchant_id, 'AppID' => $AppID, 'name' => $post['name']])->exists();
        if ($check) {
            Error('标签名称不能重复');
        }

        if ($post['type'] === 1) {
            $data = [
                'name' => $post['name'],
            ];
        } else {
            $users = [];
            if (!empty($post['filter_user'])) {
                foreach ($post['filter_user'] as $v) {
                    array_push($users, $v['id']);
                }
            }
            $post['filter_user'] = to_json($users);

            $setting = $post['conditions_setting'];
            $goods   = [];
            if (!empty($setting['shopping_goods']['value'])) {
                foreach ($setting['shopping_goods']['value'] as $v2) {
                    array_push($goods, $v2['id']);
                }
            }
            $setting['shopping_goods']['value'] = $goods;
            $group                              = [];
            if (!empty($setting['shopping_group']['value'])) {
                foreach ($setting['shopping_group']['value'] as $v2) {
                    array_push($group, $v2['id']);
                }
            }
            $setting['shopping_group']['value'] = $group;
            $post['conditions_setting']         = to_json($setting);
            $data                               = [
                'name'               => $post['name'],
                'status'             => $post['status'],
                'conditions_status'  => $post['conditions_status'],
                'conditions_setting' => $post['conditions_setting'],
                'filter_user'        => $post['filter_user'],
            ];
        }

        $data['type']        = $post['type'];
        $data['merchant_id'] = $merchant_id;
        $data['AppID']       = $AppID;
        $model               = M('users', 'Label', true);
        $model->setScenario('create');
        $model->setAttributes($data);
        if ($model->validate()) {
            if ($model->save()) {
                return true;
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    /**
     * 标签编辑
     */
    public function actionUpdate()
    {
        $id          = Yii::$app->request->get('id', 0);
        $id          = intval($id);
        $post        = Yii::$app->request->post();
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];

        $model = M('users', 'Label')::find()->where(['id' => $id, 'is_deleted' => 0])->one();
        if (empty($model)) {
            Error('标签不存在');
        }

        $check = M('users', 'Label')::find()->where(['and', ['is_deleted' => 0, 'merchant_id' => $merchant_id, 'AppID' => $AppID, 'name' => $post['name']], ['<>', 'id', $id]])->exists();
        if ($check) {
            Error('标签名称不能重复');
        }

        if ($model->type === 1) {
            $data = [
                'name' => $post['name'],
            ];
        } else {
            $users = [];
            if (!empty($post['filter_user'])) {
                foreach ($post['filter_user'] as $v) {
                    array_push($users, $v['id']);
                }
            }
            $post['filter_user'] = to_json($users);

            $setting = $post['conditions_setting'];
            $goods   = [];
            if (!empty($setting['shopping_goods']['value'])) {
                foreach ($setting['shopping_goods']['value'] as $v2) {
                    array_push($goods, $v2['id']);
                }
            }
            $setting['shopping_goods']['value'] = $goods;
            $group                              = [];
            if (!empty($setting['shopping_group']['value'])) {
                foreach ($setting['shopping_group']['value'] as $v2) {
                    array_push($group, $v2['id']);
                }
            }
            $setting['shopping_group']['value'] = $group;
            $post['conditions_setting']         = to_json($setting);
            $data                               = [
                'name'               => $post['name'],
                'status'             => $post['status'],
                'conditions_status'  => $post['conditions_status'],
                'conditions_setting' => $post['conditions_setting'],
                'filter_user'        => $post['filter_user'],
            ];
        }

        $model->setScenario('update');
        $model->setAttributes($data);
        if ($model->validate()) {
            if ($model->save()) {
                if ($post['type'] == 2 && !empty($users)) {
                    $count = M('users', 'LabelLog')::deleteAll(['label_id' => $id, 'UID' => $users]);
                    if ($count > 0) {
                        M('users', 'Label')::updateAllCounters(['users_number' => (0 - $count)], ['id' => $id]);
                    }
                }
                return true;
            } else {
                Error('保存失败');
            }

        }
        return $model;

    }

    /**
     * 删除标签
     */
    public function actionDelete()
    {
        $id    = Yii::$app->request->get('id', 0);
        $id    = intval($id);
        $model = M('users', 'Label')::find()->where(['id' => $id, 'is_deleted' => 0])->one();
        if (empty($model)) {
            Error('标签不存在');
        }

        $time                = time();
        $model->is_deleted   = 1;
        $model->deleted_time = $time;
        $res                 = $model->save();
        if ($res) {
            M('users', 'LabelLog')::deleteAll(['label_id' => $id]);
        }

    }
}
