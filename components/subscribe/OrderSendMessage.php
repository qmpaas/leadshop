<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\subscribe;

class OrderSendMessage extends BaseSubscribeMessage
{
    public $expressName;
    public $expressNo;
    public $address;
    public $orderNo;

    public function tplName()
    {
        return 'order_send';
    }

    public function msg()
    {
        return [
            'thing7' => [
                'value' => $this->expressName
            ],
            'character_string4' => [
                'value' => $this->expressNo
            ],
            'thing11' => [
                'value' => $this->address
            ],
            'character_string1' => [
                'value' => $this->orderNo
            ],
        ];
    }

    public function desc()
    {
        return '订单发货通知';
    }
}