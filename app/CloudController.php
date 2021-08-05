<?php

namespace leadmall\app;

use basics\app\BasicsController as BasicsModules;
use leadmall\Map;

class CloudController extends BasicsModules
{
    public function actionIndex()
    {
        return \Yii::$app->cloud->auth->getAuthData();
    }
}
