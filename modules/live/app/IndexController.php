<?php

namespace live\app;

use framework\common\BasicController;
use live\models\LiveForm;

class IndexController extends BasicController
{
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
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 10;
        $form = new LiveForm();
        $form->attributes = \Yii::$app->request->get();
        $form->limit = $pageSize;
        $form->apiType = 'api';
        return $this->asJson($form->getList());
    }
}