<?php

namespace goods\api;

use framework\common\BasicController;
use goods\models\GoodsParamTemplate;
use yii\data\ActiveDataProvider;

class TemplateController extends BasicController
{
    public $modelClass = 'goods\models\GoodsParamTemplate';

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

    public function actionIndex()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $get      = \Yii::$app->request->get();
        $query    = GoodsParamTemplate::find()
            ->where(['AppID' => \Yii::$app->params['AppID'], 'merchant_id' => 1, 'is_deleted' => 0]);
        $name = $get['name'] ?? false;
        if ($name) {
            $query->andWhere(['like', 'param_name', $name]);
        }
        $data = new ActiveDataProvider(
            [
                'query'      => $query->orderBy(['created_time' => SORT_DESC]),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );
        $newList = [];
        $list    = $data->getModels();
        if ($list) {
            foreach ($list as $item) {
                $newItem['id']         = $item['id'];
                $newItem['param_name'] = $item['param_name'];
                $newItem['param_data'] = json_decode($item['param_data'], true);
                $newList[]             = $newItem;
            }
        }

        //将所有返回内容中的本地地址代替字符串替换为域名
        $newList = str2url($newList);
        $data->setModels($newList);
        return $data;
    }

    public function actionCreate()
    {
        $post = \Yii::$app->request->post();
        $this->checkData($post);
        $template              = new GoodsParamTemplate();
        $template->AppID       = \Yii::$app->params['AppID'];
        $template->merchant_id = 1;
        $template->param_name  = $post['param_name'];
        $template->param_data  = json_encode($post['param_data']);
        $res                   = $template->save();
        if (!$res) {
            Error($template->getErrorMsg());
        }
        return $template->id;
    }

    public function actionUpdate()
    {
        $id       = \Yii::$app->request->get('id', false);
        $post     = \Yii::$app->request->post();
        $template = GoodsParamTemplate::findOne(['id' => $id, 'is_deleted' => 0]);
        if (!$template) {
            Error('规格模板不存在');
        }
        $this->checkData($post);
        $template->param_name = $post['param_name'];
        $template->param_data = json_encode($post['param_data']);
        $res                  = $template->save();
        if (!$res) {
            Error($template->getErrorMsg());
        }
        return true;
    }

    public function actionView()
    {
        $id       = \Yii::$app->request->get('id', false);
        $template = GoodsParamTemplate::findOne(['id' => $id, 'is_deleted' => 0]);
        if (!$template) {
            Error('规格模板不存在');
        }
        $template['param_data'] = json_decode($template['param_data'], true);
        return $template;
    }

    public function actionDelete()
    {
        $ids = \Yii::$app->request->get('id', false);
        $id  = explode(',', $ids);
        GoodsParamTemplate::updateAll(['is_deleted' => 1, 'deleted_time' => time()], ['id' => $id]);
        return true;
    }

    private function checkData($post)
    {
        if (!N('param_name', 'string')) {
            Error('规格名缺失或不规范');
        }

        if (!N('param_data', 'array')) {
            Error('规格值缺失或不规范');
        }

        foreach ($post['param_data'] as $param) {
            $check = strpos($param, '_');
            if ($check) {
                Error('规格值不允许出现下划线');
            }
        }
        return true;
    }
}
