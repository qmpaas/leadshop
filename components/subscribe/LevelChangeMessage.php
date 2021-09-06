<?php
/**
 * @copyright ©2021 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/6/5
 * Time: 12:00
 */

namespace app\components\subscribe;

class LevelChangeMessage extends BaseSubscribeMessage
{
    public $nowLevel;
    public $originLevel;
    public $time;

    public function tplName()
    {
        return 'level_change';
    }

    public function msg()
    {
        return [
            'thing2' => [
                'value' => $this->nowLevel,
            ],
            'thing4' => [
                'value' => $this->originLevel,
            ],
            'time3' => [
                'value' => $this->time
            ]
        ];
    }

    public function desc()
    {
        return '分销商升级通知';
    }
}
