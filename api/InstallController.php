<?php
namespace leadmall\api;

use app\forms\install\InstallForm;
use basics\api\BasicsController as BasicsModules;
use leadmall\Map;

class InstallController extends BasicsModules implements Map
{
    public function actionIndex()
    {
        $result = file_exists(\Yii::$app->basePath . '/install.lock');
        return ['check' => $result, 'version' => app_version()];
    }

    public function actionCreate()
    {
        if (file_exists(\Yii::$app->basePath . '/install.lock')) {
            Error('禁止访问');
        }

        if (\Yii::$app->request->isPost) {
            $form             = new InstallForm();
            $form->attributes = \Yii::$app->request->post();
            return $form->install();
        } else {
            Error('访问错误');
        }

    }
}
