<?php
/**
 * 搜索
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use leadmall\Map;

class DemoController extends BasicsModules implements Map
{

    public function actionIndex()
    {
        // $event = array('sms' => []);

        // $event = json_decode(json_encode($event));

        // $event->sms = array(
        //     'type'        => 'score_due',
        //     'mobile'      => ['18957301032'],
        //     'params'      => [
        //         'code' => '111',
        //     ],
        //     'template_id' => 'SMS_215333695',
        // );
        // P($event);
        // $res = (new smsController($this->id, $this->module))->sendSms($event);
        // P($res);
        //exit();
        // return $this->plugins("task", "config.integral_return");
        // return $this->plugins("task", ["scoreadd", [-2, 94, 0, 'del', '后台手动扣减']]);

        // $order_sn = "osn628884227247724";

        // $M     = '\order\models\Order';
        // $model = $M::find()->where(['order_sn' => $order_sn])->one();
        // //判断插件已经安装，则执行
        // if ($this->plugins("task", "status")) {
        //     //判断是否积分订单
        //     if ($model->total_score > 0) {
        //         //执行下单操作减积分操作
        //         $this->plugins("task", ["order", [
        //             $model->total_score,
        //             $model->UID,
        //             $order_sn,
        //             "order",
        //         ]]);
        //     }
        //     P(123);
        //     //执行下单操作
        //     $this->plugins("task", ["score", [
        //         "goods",
        //         $model->total_amount,
        //         $model->UID,

        //     ]]);
        //     P(345);
        //     //执行下单操作
        //     $this->plugins("task", ["score", [
        //         "order",
        //         $model->total_amount,
        //         $model->UID,
        //     ]]);
        //     P(456);
        // }
        // osn713704239689910

        //执行下单操作
        $this->plugins("task", ["score", ["goods", 0.03, 4, 'osn713704239689910']]);
        P2("完成");

        //执行下单操作
        // $this->plugins("task", ["score", ["signin", 1, 4]]);
        // P2("完成");

        // //执行下单操作
        // $this->plugins("task", ["score", ["perfect", 4, 4]]);
        // P2("完成");

        // //执行下单操作
        // $this->plugins("task", ["score", ["browse", time(), 4]]);
        // P2("完成");

        // //执行下单操作
        // $this->plugins("task", ["score", ["share", 1, 4]]);
        // P2("完成");
        // exit();
        //执行下单操作
        // $this->plugins("task", ["score", ["order", 1, 45, "SSSSSS"]]);
        // P2("完成");
        // exit();
        // $this->plugins("task", ["score", ["invite", time(), 4]]);
        // P2("完成");
        exit();
    }

}
