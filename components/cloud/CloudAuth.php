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
      return $this->httpGet('mall/auth/index', $params);
    }
}
