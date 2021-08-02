<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\cloud;

use yii\base\Component;

/**
 * 云服务组件
 * Class Cloud
 * @package app\components\cloud
 */
class Cloud extends Component
{
    /** @var CloudUpdate $update */
    public $update;
    /** @var CloudAuth $auth */
    public $auth;

    public function init()
    {
        parent::init();
        $this->update = new CloudUpdate();
        $this->auth = new CloudAuth();
    }
}
