<?php
/**
 * @copyright ©2021 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/6/5
 * Time: 12:00
 */

namespace app\components\subscribe;

/**
 * Class OrderPayMessage
 * @package app\components\subscribe
 */
class PromoterVerifyMessage extends BaseSubscribeMessage
{
    public $result;
    public $name;
    public $time;

    public function tplName()
    {
        return 'promoter_verify';
    }

    public function msg()
    {
        return [
            'thing3' => [
                'value' => $this->result,
            ],
            'thing1' => [
                'value' => $this->name,
            ],
            'time2' => [
                'value' => $this->time
            ]
        ];
    }

    public function desc()
    {
        return '分销商申请结果通知';
    }
}