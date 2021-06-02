<?php

namespace subscribe\app;

use framework\common\BasicController;
use subscribe\models\SubscribeTemplate;

class IndexController extends BasicController
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
        $AppID = \Yii::$app->params['AppID'];
        $list =  SubscribeTemplate::find()->where(['AppID' => $AppID, 'platform' => \Yii::$app->params['AppType'], 'is_deleted' => 0])->all();
        if ($list) {
            $list = array_column($list, null, 'tpl_name');
        }
        $newList = [];
        $default = \subscribe\api\IndexController::getSetting();
        foreach (array_keys($default) as $v) {
            $newList[$v] = $list[$v]['tpl_id'] ?? '';;
        }
        return $newList;
    }

    public static function sendSubscribe($event)
    {
        try {

        } catch (\Exception $exception) {

            return false;
        }
    }
}
