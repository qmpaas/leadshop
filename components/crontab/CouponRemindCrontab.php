<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/4/30
 * Time: 17:52
 */

namespace app\components\crontab;

use app\components\subscribe\CouponExpireMessage;
use coupon\models\Coupon;
use coupon\models\UserCoupon;
use setting\models\Setting;

class CouponRemindCrontab extends BaseCrontab
{
    public function name()
    {
        return '优惠券到期提醒';
    }

    public function desc()
    {
        return '优惠券到期提醒';
    }

    public function doCrontab()
    {
        $date = date('Y-m-d H:i:s', time());
        $userCoupon = UserCoupon::find()
            ->alias('uc')
            ->leftJoin(['c' => Coupon::tableName()], 'c.id = uc.coupon_id')
            ->where(['uc.status' => 0, 'uc.is_remind' => 0, 'uc.is_deleted' => 0])
            ->andWhere(['>', 'uc.end_time', time()])
            ->andWhere(['<=', 'DATE_SUB(FROM_UNIXTIME(uc.end_time),INTERVAL c.expire_remind DAY)', $date])
            ->limit(100)
            ->all();
        if ($userCoupon) {
            $name = '小店';
            $res = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'keyword' => 'setting_collection']);
            if ($res) {
                $info = to_array($res['content']);
                $name = $info['store_setting']['name'] ?? '小店';
            }
            /**@var UserCoupon $item */
            foreach ($userCoupon as $item) {
                try {
                    \Yii::$app->subscribe->setUser($item->UID)->setPage('pages/coupon/detail?id=' . $item->id)->send(new CouponExpireMessage([
                        'couponName' => $item->coupon->name,
                        'expire' => date('Y-m-d H:i', $item->end_time),
                        'businessName' => $name,
                        'remind' => '优惠券到期提醒'
                    ]));
                    $item->is_remind = 1;
                    $item->save();
                } catch (\Exception $e) {
                    \Yii::error('======优惠券到期提醒执行失败======');
                    \Yii::error($e);
                }
            }
        }
    }
}