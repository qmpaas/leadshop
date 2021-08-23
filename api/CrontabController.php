<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use leadmall\Map;
use setting\models\Setting;

class CrontabController extends BasicsModules implements Map
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

    public function actionCreate()
    {
        $behavior = \Yii::$app->request->get('behavior');
        $name     = \Yii::$app->request->post('name');
        $name && \Yii::$app->crontab->getCrontab($name);
        $setting = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'merchant_id' => 1, 'keyword' => 'crontab_access_token']);
        if (!$setting || $behavior == 'reset') {
            $accessToken = $this->generateAccessToken($setting);
        } else {
            $accessToken = $setting['content'];
        }
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/index.php?q=api/leadmall/crontab&access_token=' . $accessToken . '&appid=' . \Yii::$app->params['AppID'];
        if ($name) {
            return $url . '&name=' . $name;
        }
        return $url;
    }

    private function generateAccessToken($setting)
    {
        if (!$setting) {
            $setting = new Setting();
        }
        $accessToken          = \Yii::$app->security->generateRandomString(16);
        $setting->AppID       = \Yii::$app->params['AppID'];
        $setting->merchant_id = 1;
        $setting->keyword     = 'crontab_access_token';
        $setting->content     = $accessToken;
        if (!$setting->save()) {
            Error($setting->getErrorMsg());
        }
        return $accessToken;
    }

    public function actionIndex()
    {
        $this->checkAccessToken();
        $name = \Yii::$app->request->get('name');
        if ($name) {
            \Yii::$app->crontab->doOneCrontab($name);
        } else {
            \Yii::$app->crontab->doAllCrontab();
        }
    }

    private function checkAccessToken()
    {
        $appid = \Yii::$app->request->get('appid');
        if (!$appid) {
            Error('店铺AppID不存在');
        }
        $file = \Yii::$app->basePath . "/stores/{$appid}.json";
        if (!file_exists($file)) {
            Error('店铺不存在');
        }
        \Yii::$app->params = json_decode(file_get_contents($file), true);
        $accessToken       = \Yii::$app->request->get('access_token');
        if (!$accessToken) {
            Error('定时任务access_token不存在');
        }
        $res = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'keyword' => 'crontab_access_token']);
        if (!$res) {
            Error('定时任务access_token尚未配置');
        }
        if ($res['content'] != $accessToken) {
            Error('定时任务access_token不正确');
        }

    }
}
