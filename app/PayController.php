<?php
/**
 * 支付
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\app;

use app\components\PaymentOrder;
use app\forms\Notify;
use basics\app\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

class PayController extends BasicsModules implements Map
{
    use Notify;

    /**
     * 调起支付
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $order_sn = Yii::$app->request->get('order_sn', false);
        $host = Yii::$app->request->hostInfo;
        $model = M('order', 'Order')::find()->where(['order_sn' => $order_sn])->one();
        if (empty($model)) {
            Error('订单不存在');
        }
        $basic_setting = StoreSetting('setting_collection', 'basic_setting');
        if ($basic_setting && $basic_setting['run_status'] != 1) {
            Error('店铺打烊中');
        }
        if ($model->status !== 100) {
            Error('该订单不可支付');
        }

        if ($model->pay_amount <= 0) {
            return $this->paid($model->order_sn);
        }

        $goods_name_list = M('order', 'OrderGoods')::find()->where(['order_sn' => $order_sn])->select(
            'goods_name'
        )->asArray()->all();

        $goods_name_str = '';
        foreach ($goods_name_list as $v) {
            $goods_name_str .= $v['goods_name'] . ',';
        }
        $goods_name_str = rtrim($goods_name_str, ',');
        if (mb_strlen($goods_name_str) > 30) {
            $goods_name = mb_substr($goods_name_str, 0, 30);
            $goods_name .= '...';
        } else {
            $goods_name = $goods_name_str;
        }

        $res = Yii::$app->payment->unifiedOrder(
            new PaymentOrder([
                'openid' => Yii::$app->user->identity->oauth->oauthID,
                'orderNo' => time() . $model->order_sn, //拼接时间戳,防止后期调用订单编号重复
                'amount' => (float)$model->pay_amount,
                'title' => $goods_name,
                'attach' => to_json(['appid' => $model->AppID, 'apptype' => \Yii::$app->params['AppType']]),
                'notify' => WE7_API ? WE7_API . "/app/leadmall/pay" : $host . '/pay.php',
            ])
        );
        return $res;
    }

    public function actionCreate()
    {
        $inWechatpaySignature = \Yii::$app->request->headers->get('wechatpay-signature');
        $inWechatpayTimestamp = \Yii::$app->request->headers->get('wechatpay-timestamp');
        $inWechatpayNonce = \Yii::$app->request->headers->get('wechatpay-nonce');
        $serial = \Yii::$app->request->headers->get('wechatpay-serial');
        if ($inWechatpaySignature && $inWechatpayTimestamp && $inWechatpayNonce && $serial) {
            // 目前只有单店铺模式，写死
            $AppID = '98c08c25f8136d590c';
            Yii::$app->params['AppID'] = $AppID;
            $file = __DIR__ . "/../stores/{$AppID}.json";
            Yii::$app->params = json_decode(file_get_contents($file), true);
            if (\Yii::$app->params['appPay']['weapp']['serial'] == $serial) {
                $AppType = 'weapp';
            } elseif (\Yii::$app->params['appPay']['wechat']['serial'] == $serial) {
                $AppType = 'wechat';
            } else {
                Error('找不到序列号');
            }
        } else {
            $disableEntities = libxml_disable_entity_loader(true);
            $notifyInfo = (array)simplexml_load_string(
                file_get_contents("php://input"),
                'SimpleXMLElement',
                LIBXML_NOCDATA
            );
            libxml_disable_entity_loader($disableEntities);
            $attach = to_array($notifyInfo['attach']);
            $AppID = $attach['appid'];
            $AppType = $attach['apptype'];
        }
        Yii::$app->params['AppID'] = $AppID;
        $file = __DIR__ . "/../stores/{$AppID}.json";
        if (!file_exists($file)) {
            Error('店铺不存在');
        }
        Yii::$app->params = json_decode(file_get_contents($file), true);
        Yii::$app->params['AppType'] = $AppType;
        Yii::$app->payment->notify($this, $AppID);
    }
}
