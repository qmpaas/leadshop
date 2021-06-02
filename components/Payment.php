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
use framework\wechat\Lib\Tools;
use order\models\Order;
use yii\base\Component;
use yii\web\Response;

class Payment extends Component
{
    /**@var PaymentOrder $paymentOrder * */
    public $paymentOrder;
    public $option;

    /**
     * @param $paymentOrder
     * @param array $option
     * @return mixed
     */
    public function unifiedOrder($paymentOrder, $option = [])
    {
        $this->paymentOrder = $paymentOrder;
        $this->option       = $option;
        $action             = $paymentOrder->payType;
        return $this->$action();
    }

    /**
     * 微信支付
     * @return array|bool
     * @throws \Exception
     */
    public function wechat()
    {
        $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID'] ?? '']);
        $pay    = $wechat->getWechatPay($this->option);
        if (!$pay) {
            throw new \Exception('请联系管理员配置支付信息');
        }
        //$openid, $body, $out_trade_no, $total_fee, $notify_url
        $res = $pay->getPrepayId(
            $this->paymentOrder->openid,
            $this->paymentOrder->title,
            $this->paymentOrder->orderNo,
            $this->paymentOrder->amount * 100,
            $this->paymentOrder->notify,
            $this->paymentOrder->attach,
            $this->option['trade_type'] ?? 'JSAPI'
        );
        if ($res === false) {
            Error($pay->errMsg);
        }
        $appPayData = [
            'appId'     => $pay->appid,
            'timeStamp' => (string) time(),
            'nonceStr'  => md5(uniqid()),
            'package'   => 'prepay_id=' . $res,
            'signType'  => "MD5",
        ];
        $appPayData['paySign'] = Tools::getPaySign($appPayData, $pay->config['partnerkey']);
        return $appPayData;
    }

    public function notify($class, $AppID)
    {
        $wechat        = new CommonWechat();
        $wechat->AppID = $AppID;
        $pay = $wechat->getWechatPay();
        if (!$pay) {
            throw new \Exception('请联系管理员配置支付信息');
        }
        $notifyInfo = $pay->getNotify();
        if ($notifyInfo === false) {
            // 接口失败的处理
            \Yii::error('支付失败：' . $pay->errCode . "===MSG:" . $pay->errMsg);
            echo $pay->errMsg;
        } else {
            //支付通知数据获取成功
            if ($notifyInfo['result_code'] == 'SUCCESS' && $notifyInfo['return_code'] == 'SUCCESS') {
                // 支付状态完全成功，可以更新订单的支付状态了
                // @todo 这里去完成你的订单状态修改操作
                $res = $class->paid($notifyInfo);
                if ($res) {
                    // 回复xml，replyXml方法是终态方法
                    $content                     = $pay->replyXml(['return_code' => 'SUCCESS', 'return_msg' => 'DEAL WITH SUCCESS'], true);
                    \Yii::$app->response->format = Response::FORMAT_XML;
                    echo $content;
                }
            }
        }
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
            $pay    = $wechat->getWechatPay([], $order['source']);
            if (!$pay) {
                throw new \Exception('请联系管理员配置支付信息');
            }
            if ($price > 0) {
                $res = $pay->refund($order['order_sn'], false, $outRefundNo, $order['pay_amount'] * 100, $price * 100);
            } else {
                $res = true;
            }
            if ($res) {
                $res = $callback();
                $t->commit();
                return $res;
            } else {
                Error($pay->errMsg);
            }
        } catch (\Exception $e) {
            Error($e->getMessage());
            $t->rollBack();
        }
    }
}
