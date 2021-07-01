<?php

namespace live\api;

use framework\common\BasicController;
use live\models\LiveAddGoods;
use live\models\LiveEditForm;
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
        \Yii::$app->params['AppType'] = 'weapp';
        $behavior = \Yii::$app->request->get('behavior', 'list');
        $form = new LiveForm();
        $form->attributes = \Yii::$app->request->get();
        switch ($behavior) {
            case 'list':
                return $this->asJson($form->getList());
            case 'qrcode':
                return $this->asJson($form->getQrCode());
            default:
                Error('未定义操作');
        }
    }

    public function actionCreate()
    {
        \Yii::$app->params['AppType'] = 'weapp';
        $behavior = \Yii::$app->request->get('behavior', 'create');
        switch ($behavior) {
            case 'create':
                $form = new LiveEditForm();
                $form->attributes = \Yii::$app->request->post();
                $form->room_id = \Yii::$app->request->post('roomid', 0);
                return $this->asJson($form->save());
            case 'delete':
                $form = new LiveEditForm();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->delete());
            case 'add':
                $form = new LiveAddGoods();
                $form->attributes = \Yii::$app->request->post();
                return $this->asJson($form->save());
            default:
                Error('未定义操作');
        }

    }
}