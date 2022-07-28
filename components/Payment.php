<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components;

use app\forms\CommonWechat;
use finance\models\Finance;
use order\models\Order;
use users\models\User;
use yii\base\Component;

class Payment extends Component
{
    /**
     * @param $paymentOrder
     * @param array $option
     * @return mixed
     */
    public function unifiedOrder($paymentOrder, $option = [])
    {
        $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID'] ?? '']);
        return $wechat->unifiedOrder($paymentOrder);
    }

    public function notify($class, $AppID)
    {
        $wechat        = new CommonWechat();
        $wechat->AppID = $AppID;
        $wechat->getNotify($class, $AppID);
    }

    /**
     * 退款
     * @param Order $order 订单
     * @param string $outRefundNo 退款订单号
     * @param int $price 退款金额
     * @param mixed $callback 回调函数
     * @throws \Exception
     */
    public function refund($order, $outRefundNo, $price, $callback)
    {
        $t = \Yii::$app->db->beginTransaction();
        try {
            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            if ($price > 0) {
                $res = $wechat->refund($order['source'], $order['order_sn'], $outRefundNo, $price, $order['pay_amount']);
            } else {
                $res = true;
            }
            if ($res) {
                $res = $callback();
                $t->commit();
                return $res;
            }
        } catch (\Exception $e) {
            Error($e->getMessage());
            $t->rollBack();
        }
    }

    /**
     * @param User $user
     * @param Finance $finance
     * @param $desc
     * @param $callback
     * @return mixed
     */
    public function transfer($user, $finance, $desc, $callback)
    {
        try {
            $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID']]);
            $res  = $wechat->transfers($user->oauth->type, $user->oauth->oauthID, $finance->price * 100, $finance->order_sn, $desc);
            if ($res) {
                $res = $callback();
                return $res;
            }
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }
}
