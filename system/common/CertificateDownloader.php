<?php

/**
 * @link https://www.zjhejiang.com/
 * @author 浙江禾匠信息科技有限公司
 * @copyright Copyright ©2022 浙江禾匠信息科技有限公司
 */

namespace framework\common;

use app\components\core\HttpRequest;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Util\PemUtil;
use Yii;

class CertificateDownloader
{
    use HttpRequest;

    /**
     * @param $paymentSetting
     * @return array
     * @throws \yii\base\Exception
     */
    public function getCert($paymentSetting)
    {
        $url = 'https://api.mch.weixin.qq.com/v3/certificates';
        $merchantId = $paymentSetting['mchid'];
        // 从「商户证书」中获取「证书序列号」
        $serialNo = PemUtil::parseCertificateSerialNo($paymentSetting['certPem']);
        // Authorization: <schema> <token>
        $mchPrivateKey = $paymentSetting['keyPem'];
        $timestamp = time();
        $nonce = Yii::$app->security->generateRandomString(10);
        $message = "GET\n" .
            "/v3/certificates\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            "\n";
        openssl_sign($message, $rawSign, $mchPrivateKey, 'sha256WithRSAEncryption');
        $sign = base64_encode($rawSign);
        $schema = 'WECHATPAY2-SHA256-RSA2048';
        $token = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $merchantId,
            $nonce,
            $timestamp,
            $serialNo,
            $sign
        );
        $headers = [
            'Authorization' => $schema . ' ' . $token,
            'Accept' => 'application/json'
        ];
        $result = $this->get($url, [], $headers);
        $res = $result['data'][0];
        $ciphertext = $res['encrypt_certificate']['ciphertext'];
        $key = $paymentSetting['key'];
        $nonce = $res['encrypt_certificate']['nonce'];
        $associated_data = 'certificate';
        return [AesGcm::decrypt($ciphertext, $key, $nonce, $associated_data), $res['serial_no']];
    }
}
