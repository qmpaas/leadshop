<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use leadmall\Map;
use setting\models\Setting;
use Yii;

class WeappserverController extends BasicsModules implements Map
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
        $setting = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'keyword' => 'weapp_server_setting']);
        if ($setting) {
            $setting = to_array($setting['content']);
        }
        $apiRoot = str_replace('http://', 'https://', \Yii::$app->request->hostInfo);
        $rootUrl = rtrim(dirname(\Yii::$app->request->baseUrl), '/');
        $list['server'] = $apiRoot . $rootUrl . '/msg.php';
        $list['token'] = $setting['token'] ?? Yii::$app->security->generateRandomString(32);
        $list['encodingAESKey'] = $setting['encodingAESKey'] ?? Yii::$app->security->generateRandomString(43);
        return $list;
    }
}
