<?php

namespace goods\api;

use framework\common\BasicController;
use goods\models\GoodsArgs;
use Yii;
use yii\data\ActiveDataProvider;

class ArgsController extends BasicController
{
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 10;
        $where    = ['AppID' => $AppID, 'is_deleted' => 0, 'merchant_id' => $merchant_id];

        $get    = Yii::$app->request->get();
        $search = $get['search'] ?? '';
        if ($search) {
            $where = ['and', $where, ['like', 'title', $search]];
        }
        $sort                    = $get['sort'] ?? 'DESC';
        $orderBy['created_time'] = $sort === 'ASC' ? SORT_ASC : SORT_DESC;
        $query                   = GoodsArgs::find()->where($where)->orderBy($orderBy)->asArray()->select('id,title,content,created_time');

        $behavior                = Yii::$app->request->get('behavior', '');
        if ($behavior == 'option') {
            $list = $query->all();
            foreach ($list as $key => &$value) {
                $value['content'] = to_array($value['content']);
            }
            return $list;
        }

        $data = new ActiveDataProvider(
            [
                'query'      => $query,
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['content'] = to_array($value['content']);
        }
        $data->setModels($list);

        return $data;
    }

    public function actionView()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = GoodsArgs::findOne($id);
        if (!$model) {
            Error('参数模板不存在');
        }
        $data            = $model->toArray();
        $data['content'] = to_array($data['content']);
        return $data;
    }

    public function actionCreate()
    {
        $post = Yii::$app->request->post();

        $post['content'] = to_json($post['content']);

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
        $post['AppID']       = Yii::$app->params['AppID'];

        $model = new GoodsArgs;
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
        $model = GoodsArgs::findOne($id);

        $post['content'] = to_json($post['content']);

        $model->setScenario('update');
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

    public function actionDelete()
    {
        $ids = Yii::$app->request->get('id', false);
        $id  = explode(',', $ids);
        $res = GoodsArgs::updateAll(['is_deleted' => 1, 'deleted_time' => time()], ['id' => $id]);
        if ($res) {
            return true;
        } else {
            Error('删除失败');
        }

    }

}
