<?php
/**
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/7/30
 * Time: 10:42
 */

namespace app\components\cloud;


class CloudAuth extends BaseCloud
{
    public function getAuthData($params = [])
    {
        $cacheKey = "LEADSHOP_AUTH_BY_" . md5(json_encode($params));
        $res = \Yii::$app->cache->get($cacheKey);
        if ($res) {
            return $res;
        }
        $res = $this->httpGet('mall/auth/index', $params);
        \Yii::$app->cache->set($cacheKey, $res, 60 * 5);
        return $res;
    }
}
