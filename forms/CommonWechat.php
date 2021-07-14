<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/15
 * Time: 9:11
 */

namespace app\forms;

use app\datamodel\Option;
use yii\base\BaseObject;

class CommonWechat extends BaseObject
{
    /**
     * @var mixed
     */
    private $wechat;
    /**
     * @var mixed
     */
    private $xWechatPay;
    public $AppID;

    /**
     * @return \Wehcat\WechatReceive
     * @throws \Exception
     */
    public function getWechat()
    {
        if (!$this->AppID) {
            throw new \Exception('请传入AppID');
        }
        if ($this->wechat) {
            return $this->wechat;
        }
        $mpConfig = \Yii::$app->params['apply'][\Yii::$app->params['AppType']] ?? null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            throw new \Exception('渠道参数不完整。');
        }
        $this->wechat = &load_wechat('Accesstoken',[
            'token'          => $mpConfig['token'] ?? '', // 填写你设定的key
            'appid'          => $mpConfig['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret'      => $mpConfig['AppSecret'], // 填写高级调用功能的密钥
            'encodingaeskey' => $mpConfig['encodingAesKey'] ?? '', // 填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
        ]);
        return $this->wechat;
    }

    /**
     * 自动识别授权的还是自填参数，获取access_token
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken()
    {
        return $this->getWechat()->getAccessToken();
    }

    /**
     * @param $option
     * @return mixed|\Wehcat\WechatReceive
     */
    public function getWechatPay($option = [],$appType = '')
    {
        if ($this->xWechatPay) {
            return $this->xWechatPay;
        }
        if ($option) {
            $payType['appid'] = trim($option['appid']);
            $payType['mchid'] = trim($option['mchid']);
            $payType['key'] = trim($option['key']);
            $payType['certPem'] = $option['certPem'];
            $payType['keyPem'] = $option['keyPem'];
            $payType['isService'] = false;
        } else {
            if (\Yii::$app->params['AppType'] && \Yii::$app->params['AppType'] != 'undefined') {
                $payType = \Yii::$app->params['appPay'][\Yii::$app->params['AppType']] ?? null;
            } else {
                $payType = \Yii::$app->params['appPay'][$appType] ?? null;
            }
        }
        if ($payType && $payType['appid'] && $payType['mchid'] && $payType['key']) {
            if ($payType['isService']) {
                if ($payType['serviceCertPem'] && $payType['serviceKeyPem']) {
                    list($sslCer, $sslKey) = $this->generatePem($payType['serviceCertPem'], $payType['serviceKeyPem']);
                }
                $this->xWechatPay = load_wechat('Servicepay',[
                    'appid'          => $payType['serviceAppid'],
                    'mch_id'         => $payType['serviceMchid'],
                    'partnerkey'     => $payType['serviceKey'],
                    'sub_appid'      => $payType['wechatAppid'],
                    'sub_mch_id'     => $payType['mchid'],
                    'ssl_cer'        => $sslCer ?? '',
                    'ssl_key'        => $sslKey ?? ''
                ]);
            } else {
                if ($payType['certPem'] && $payType['keyPem']) {
                    list($sslCer, $sslKey) = $this->generatePem($payType['certPem'], $payType['keyPem']);
                }
                $this->xWechatPay = load_wechat('Pay',[
                    'appid'          => trim($payType['appid']),
                    'mch_id'         => trim($payType['mchid']),
                    'partnerkey'     => trim($payType['key']),
                    'ssl_cer'        => $sslCer ?? '',
                    'ssl_key'        => $sslKey ?? ''
                ]);
            }
        } else {
            Error('微信支付尚未配置');
        }
        return $this->xWechatPay;
    }

    /**
     * @param $cert_pem
     * @param $key_pem
     */
    private function generatePem($cert_pem, $key_pem)
    {
        $pemDir = \Yii::$app->runtimePath . '/pem';
        make_dir($pemDir);
        $certPemFile = $pemDir . '/' . md5($cert_pem);
        if (!file_exists($certPemFile)) {
            file_put_contents($certPemFile, $cert_pem);
        }
        $keyPemFile = $pemDir . '/' . md5($key_pem);
        if (!file_exists($keyPemFile)) {
            file_put_contents($keyPemFile, $key_pem);
        }
        return [$certPemFile, $keyPemFile];
    }
}
