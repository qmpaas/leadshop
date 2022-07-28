<?php

namespace leadmall\app;

use app\forms\Notify;
use basics\app\BasicsController as BasicsModules;
use leadmall\Map;
use system\models\WeappPay;
use Yii;
use yii\web\Response;

class MsgController extends BasicsModules implements Map
{
    use Notify;

    public $setting;

    public function actionIndex()
    {
        return $this->msg();
    }

    public function actionCreate()
    {
        return $this->msg();
    }

    /**
     * @return false|mixed|void
     */
    public function msg()
    {
        \Yii::$app->response->format = Response::FORMAT_HTML;
        $notifyInfo = $this->getXmlData(\Yii::$app->request->rawBody);
        $AppID = '98c08c25f8136d590c';
        $AppType = 'weapp';
        Yii::$app->params['AppID'] = $AppID;
        $file = __DIR__ . "/../stores/{$AppID}.json";
        if (!file_exists($file)) {
            Error('店铺不存在');
        }
        Yii::$app->params = json_decode(file_get_contents($file), true);
        Yii::$app->params['AppType'] = $AppType;
        $this->setting = StoreSetting('weapp_server_setting');
        if (!$this->checkSignature()) {
            \Yii::error('checksign未通过');
            return false;
        }
        // 微信第一次配置时需校验
        if (isset($_GET["echostr"]) && $_GET["echostr"]) {
            return $_GET['echostr'];
        }

        if (isset($notifyInfo['Event'])) {
            switch ($notifyInfo['Event']) {
                // 小程序支付回调
                case 'funds_order_pay':
                    $weappPay = WeappPay::findOne([
                        'pay_sn' => $notifyInfo['order_info']['trade_no'],
                    ]);
                    if (!$weappPay) {
                        Error('订单不存在');
                    }
                    $weappPay->transaction_id = $notifyInfo['order_info']['transaction_id'];
                    $res = $weappPay->save();
                    if (!$res) {
                        Error('状态错误');
                    }
                    $res = $this->paid(['out_trade_no' => time() . $weappPay->order_sn]);
                    if ($res) {
                        echo 'success';
                    }
                    break;
            }
        }
        return "success";
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->setting['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if ($tmpStr == $signature ) {
            return true;
        } else {
            return false;
        }
    }

    private function getXmlData($xml)
    {
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($obj), true);
    }
}
