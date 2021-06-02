<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\subscribe;

class OrderSaleVerifyMessage extends BaseSubscribeMessage
{
    public $result;
    public $orderNo;
    public $orderAmount;

    public function tplName()
    {
        return 'order_sale_verify';
    }

    public function msg()
    {
        return [
            'thing6' => [
                'value' => $this->result
            ],
            'character_string8' => [
                'value' => $this->orderNo
            ],
            'amount7' => [
                'value' => $this->orderAmount
            ]
        ];
    }

    public function desc()
    {
        return '售后状态通知';
    }
}