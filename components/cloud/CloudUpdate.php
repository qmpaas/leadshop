<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\cloud;

/**
 * 更新日志
 * Class CloudUpdate
 * @package app\components\cloud
 */
class CloudUpdate extends BaseCloud
{
    public function getVersionData($params = [])
    {
        return $this->httpGet('mall/update/index', $params);
    }
}
