<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\subscribe;

class CouponExpireMessage extends BaseSubscribeMessage
{
    public $couponName;
    public $expire;
    public $businessName;
    public $remind;

    public function tplName()
    {
        return 'coupon_expire';
    }

    public function msg()
    {
        return [
            'thing5' => [
                'value' => $this->couponName
            ],
            'time3' => [
                'value' => $this->expire
            ],
            'thing1' => [
                'value' => $this->businessName
            ],
            'thing9' => [
                'value' => $this->remind
            ],
        ];
    }

    public function desc()
    {
        return '优惠券到期提醒';
    }
}