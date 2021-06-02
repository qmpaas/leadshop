<?php
/**
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace leadmall;

use framework\common\BasicModule;

/**
 * v2 module definition class
 */
class Module extends BasicModule
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'leadmall';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    public function eventList()
    {
        $this->on('check_order', ["\order\api\IndexController", 'checkOrder']); //订单检测
        $this->on('check_evaluate', ["\order\api\EvaluateController", 'checkEvaluate']); //评价检测

        $this->on('visit', ["\statistical\api\VisitController", 'saveLog']); //访客

        $this->on('user_upload', ["\statistical\api\UploadController", 'saveLog']); //用户上传文件

        $this->on('user_statistical', ["\users\app\IndexController", 'statistical']); //用户统计

        $this->on('visit_goods', ["\statistical\api\GoodsVisitController", 'saveLog']); //浏览商品记录

        $this->on('add_order', ["\goods\app\IndexController", 'reduceStocks']); //下单商品减库存
        $this->on('add_order', ["\cart\app\IndexController", 'cartClear']); //下单清空对应购物车商品

        $this->on('cancel_order', ["\goods\app\IndexController", 'addStocks']); //取消订单返还库存
        $this->on('cancel_order', ["\coupon\api\IndexController", 'restoreUserCoupon']); //取消订单返还优惠券

        $this->on('pay_order', ["\goods\app\IndexController", 'addSales']); //付款增加商品销售额
        $this->on('pay_order', ["\coupon\api\IndexController", 'sendUserCoupon']); //下单发放优惠券
        $this->on('pay_order', ["\users\api\LabellogController", 'giveLabel']); //付款判断用户是否有新的标签
        $this->on('refunded', ["\coupon\api\IndexController", 'invalidateUserCoupon']); //退款后失效优惠券
        
        $this->on('send_sms', ["\sms\app\IndexController", 'sendSms']); //发送短信
        
    }
}
