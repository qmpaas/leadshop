<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\subscribe;

use yii\base\BaseObject;

/**
 * Class BaseSubscribeMessage
 * @package app\components\subscribe
 */
abstract class BaseSubscribeMessage extends BaseObject
{
    /**
     * 模板名称
     * @return mixed
     */
    abstract public function tplName();

    /**
     * 订阅消息参数
     * @return mixed
     */
    abstract public function msg();

    /**
     * 订阅消息描述
     * @return mixed
     */
    abstract public function desc();
}