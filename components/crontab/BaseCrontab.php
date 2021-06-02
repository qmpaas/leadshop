<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/4/30
 * Time: 17:52
 */

namespace app\components\crontab;

use yii\base\BaseObject;

/**
 * 定时任务基类
 * Class BaseSubscribeMessage
 * @package app\components\subscribe
 */
abstract class BaseCrontab extends BaseObject
{
    /*
     * 开关
     */
    public $enable = true;

    /**
     * 定时任务名称
     * @return mixed
     */
    abstract public function name();

    /**
     * 定时任务描述
     * @return mixed
     */
    abstract public function desc();

    /**
     * 定时任务逻辑
     * @return mixed
     */
    abstract public function doCrontab();
}