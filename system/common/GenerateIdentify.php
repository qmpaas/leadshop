<?php

namespace framework\common;

use setting\models\Setting;

trait GenerateIdentify
{
    /**
     * 返回签名实体
     * @return mixed|string
     */
    public function getIdentify()
    {
        $identified = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'merchant_id' => 1, 'keyword' => 'leadshop_identified_by']);
        if (!$identified) {
            $identity = self::generateAccessToken();
        } else {
            $identity = $identified['content'];
        }
        return $identity;
    }

    public function generateAccessToken()
    {
        $setting = new Setting();
        $identified = \Yii::$app->security->generateRandomString(16);
        $setting->AppID = \Yii::$app->params['AppID'];
        $setting->merchant_id = 1;
        $setting->keyword = 'leadshop_identified_by';
        $setting->content = $identified;
        if (!$setting->save()) {
            Error($setting->getErrorMsg());
        }
        return $identified;
    }
}
