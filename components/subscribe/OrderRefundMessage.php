<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\subscribe;

class OrderRefundMessage extends BaseSubscribeMessage
{
    public $refundAmount;
    public $orderNo;
    public $goodsName;
    public $applyTime;

    public function tplName()
    {
        return 'order_refund_tpl';
    }

    public function msg()
    {
        return [
            'amount6' => [
                'value' => $this->refundAmount
            ],
            'character_string2' => [
                'value' => $this->orderNo
            ],
            'thing3' => [
                'value' => $this->goodsName
            ],
            'time7' => [
                'value' => $this->applyTime
            ]
        ];
    }

    public function desc()
    {
        return '退款成功通知';
    }
}