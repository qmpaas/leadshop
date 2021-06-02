<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\cloud;

use app\components\core\Request;

/**
 * 云服务基础
 * Class BaseCloud
 * @package app\components\cloud
 */
abstract class BaseCloud extends Request
{
    public $baseUrl = 'aHR0cHM6Ly9jbG91ZC5sZWFkc2hvcC52aXA=';

    public function init()
    {
        $this->requestHeader = [
            'qm-domain' => \Yii::$app->request->hostName,
            'x-version' => app_version(),
        ];
        parent::init();
    }
}