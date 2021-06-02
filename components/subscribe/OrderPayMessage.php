<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\subscribe;

/**
 * Class OrderPayMessage
 * @package app\components\subscribe
 */
class OrderPayMessage extends BaseSubscribeMessage
{
    public $amount;
    public $payTime;
    public $businessName;
    public $orderNo;

    public function tplName()
    {
        return 'order_pay';
    }

    public function msg()
    {
        return [
            'amount2' => [
                'value' => $this->amount,
            ],
            'date4' => [
                'value' => $this->payTime,
            ],
            'thing6' => [
                'value' => $this->businessName
            ],
            'character_string8' => [
                'value' => $this->orderNo
            ],
        ];
    }

    public function desc()
    {
        return '付款成功通知';
    }
}