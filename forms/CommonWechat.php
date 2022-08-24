<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/15
 * Time: 9:11
 */

namespace app\forms;

use app\components\PaymentOrder;
use framework\wechat\Lib\Tools;
use framework\wechat\WechatWxpay;
use GuzzleHttp\Exception\ClientException;
use order\models\Order;
use system\models\WeappPay;
use WeChatPay\Builder;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Crypto\Hash;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;
use WeChatPay\Transformer;
use WeChatPay\Util\PemUtil;
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
    public $apiVersion;
    public $payVersion;
    public $payType;

    /**
     * @return \Wehcat\WechatReceive
     * @throws \Exception
     */
    public function getWechat($apptype = '')
    {
        if ($this->wechat) {
            return $this->wechat;
        }
        if ($apptype) {
            $mpConfig = \Yii::$app->params['apply'][$apptype] ?? null;
        } else {
            $mpConfig = \Yii::$app->params['apply'][\Yii::$app->params['AppType']] ?? null;
        }
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            throw new \Exception('渠道参数不完整。');
        }
        $this->wechat = &load_wechat('Accesstoken', [
            'token' => $mpConfig['token'] ?? '', // 填写你设定的key
            'appid' => $mpConfig['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret' => $mpConfig['AppSecret'], // 填写高级调用功能的密钥
            'encodingaeskey' => $mpConfig['encodingAesKey'] ?? '', // 填写加密用的EncodingAESKey（可选，接口传输选择加密时必需）
        ]);
        return $this->wechat;
    }

    /**
     * 自动识别授权的还是自填参数，获取access_token
     * @return mixed
     * @throws \Exception
     */
    public function getAccessToken($apptype = '')
    {
        return $this->getWechat($apptype)->getAccessToken();
    }

    /**
     * @param $option
     * @return mixed|\Wehcat\WechatReceive
     */
    public function getWechatPay($option = [], $appType = '')
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
            $payType['pay_version'] = 'common';
        } else {
            if (\Yii::$app->params['AppType'] && \Yii::$app->params['AppType'] != 'undefined') {
                $payType = \Yii::$app->params['appPay'][\Yii::$app->params['AppType']] ?? null;
            } else {
                $payType = \Yii::$app->params['appPay'][$appType] ?? null;
            }
        }
        $this->payType = $payType;
        if ($payType && $payType['appid'] && $payType['mchid'] && $payType['key']) {
            if ($payType['certPem'] && $payType['keyPem']) {
                list($sslCer, $sslKey) = $this->generatePem($payType['certPem'], $payType['keyPem']);
            }
            $this->apiVersion = $payType['api_version'] ?? 'v2';
            if (isset($payType['pay_version']) && $payType['pay_version'] == 'wx') {
                $this->payVersion = 'wx';
                $this->xWechatPay = load_wechat('Wxpay', [
                    'appid' => trim($payType['appid']),
                    'mch_id' => trim($payType['mchid']),
                    'partnerkey' => trim($payType['key']),
                    'ssl_cer' => $sslCer ?? '',
                    'ssl_key' => $sslKey ?? ''
                ]);
                $this->xWechatPay->access_token = $this->getAccessToken($appType);
            } else {
                $this->payVersion = 'common';
                if ($this->apiVersion == 'v3') {
                    // 从「商户证书」中获取「证书序列号」
                    $serialNo = PemUtil::parseCertificateSerialNo($payType['certPem']);
                    // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
                    $merchantPrivateKeyFilePath = $payType['keyPem'];
                    $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath, Rsa::KEY_TYPE_PRIVATE);
                    if (!$payType['publicPem'] || !$payType['serial']) {
                        throw new \Exception('请完成支付配置');
                    }
                    $platformCertificateFilePath = $payType['publicPem'];
                    $platformPublicKeyInstance = Rsa::from($platformCertificateFilePath, Rsa::KEY_TYPE_PUBLIC);
                    // 从「微信支付平台证书」中获取「证书序列号」
                    $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificateFilePath);
                    // 构造一个 APIv3 客户端实例
                    $this->xWechatPay = Builder::factory([
                        'mchid' => $payType['mchid'],
                        'serial' => $serialNo,
                        'privateKey' => $merchantPrivateKeyInstance,
                        'certs' => [
                            $platformCertificateSerial => $platformPublicKeyInstance,
                        ],
                    ]);
                } elseif ($this->apiVersion == 'v2') {
                    // 工厂方法构造一个V2实例
                    $this->xWechatPay = Builder::factory([
                        'mchid' => $payType['mchid'],
                        'serial' => 'nop',
                        'privateKey' => 'any',
                        'certs' => ['any' => null],
                        'secret' => $payType['key'],
                        'merchant' => [
                            'cert' => $sslCer ?? '',
                            'key' => $sslKey ?? '',
                        ],
                    ]);
                }
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

    /**
     * @param PaymentOrder $paymentOrder
     * @return array|void
     * @throws \Exception
     */
    public function unifiedOrder($paymentOrder)
    {
        $pay = $this->getWechatPay();
        if (!$pay) {
            throw new \Exception('请联系管理员配置支付信息');
        }
        if ($this->payVersion == 'wx') {
            $wxpsn = get_sn('wxpsn');
            /**@var WechatWxpay $pay */
            $res = $pay->unifiedOrder(
                $paymentOrder->openid,
                mb_substr($paymentOrder->title, 0, 20),
                $wxpsn,
                intval(strval($paymentOrder->amount * 100))
            );
            if ($res === false) {
                Error($pay->errMsg);
            }

            $model = WeappPay::findOne([
                'order_sn' => $paymentOrder->orderNo
            ]);
            if (!$model) {
                $model = new WeappPay();
                $model->order_sn = substr($paymentOrder->orderNo, 10);
                $model->pay_sn = $wxpsn;
                if (!$model->save()) {
                    throw new \Exception($this->getErrorMsg($model));
                }
            }
            $payData = $res['payment_params'];
            $payData['orderInfo'] = [];
            return $payData;
        } elseif ($this->payVersion == 'common') {
            if ($this->apiVersion == 'v3') {
                try {
                    $result = $pay->chain('v3/pay/transactions/jsapi')
                        ->post([
                            'json' => [
                                'mchid' => $this->payType['mchid'],
                                'payer' => [
                                    'openid' => $paymentOrder->openid,
                                ],
                                'out_trade_no' => $paymentOrder->orderNo,
                                'appid' => $this->payType['appid'],
                                'description' => $paymentOrder->title,
                                'notify_url' => $paymentOrder->notify,
                                'amount' => [
                                    'total' => intval(strval($paymentOrder->amount * 100)),
                                    'currency' => 'CNY'
                                ],
                                'attach' => $paymentOrder->attach,
                            ]
                        ]);
                    $result = json_decode($result->getBody()->getContents(), true);
                    $merchantPrivateKeyFilePath = $this->payType['keyPem'];
                    $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath);
                    $params = [
                        'appId' => $this->payType['appid'],
                        'timeStamp' => (string)Formatter::timestamp(),
                        'nonceStr' => Formatter::nonce(),
                        'package' => 'prepay_id=' . $result['prepay_id'],
                    ];
                    $params += [
                        'paySign' => Rsa::sign(
                            Formatter::joinedByLineFeed(...array_values($params)),
                            $merchantPrivateKeyInstance
                        ),
                        'signType' => 'RSA'
                    ];
                    return $params;
                } catch (ClientException $clientException) {
                    Error($clientException->getMessage());
                } catch (\Exception $e) {
                    Error($e->getMessage());
                }
            } elseif ($this->apiVersion == 'v2') {
                $result = $pay
                    ->v2->pay->unifiedorder
                    ->postAsync([
                        'xml' => [
                            'appid' => $this->payType['appid'],
                            'mch_id' => $this->payType['mchid'],
                            'openid' => $paymentOrder->openid,
                            'body' => $paymentOrder->title,
                            'out_trade_no' => $paymentOrder->orderNo,
                            'total_fee' => (string)($paymentOrder->amount * 100),
                            'notify_url' => $paymentOrder->notify,
                            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//终端IP
                            'trade_type' => 'JSAPI',//交易类型
                            'attach' => $paymentOrder->attach,
                        ]
                    ])
                    ->then(static function ($response) {
                        return Transformer::toArray((string)$response->getBody());
                    })
                    ->otherwise(static function ($e) {
                        \Yii::error($e);
                        // 更多`$e`异常类型判断是必须的，这里仅列出一种可能情况，请根据实际对接过程调整并增加
                        if ($e instanceof \GuzzleHttp\Promise\RejectionException) {
                            Error((string)$e->getReason()->getBody());
                        }
                        $res = Transformer::toArray($e->getBody()->getContents());
                        Error($res['return_msg'] ?? '请检查支付配置');
                    })
                    ->wait();
                $params = [
                    'appId' => $this->payType['appid'],
                    'timeStamp' => (string)Formatter::timestamp(),
                    'nonceStr' => Formatter::nonce(),
                    'package' => 'prepay_id=' . $result['prepay_id'],
                    'signType' => 'MD5'
                ];
                $params += [
                    'paySign' => Tools::getPaySign($params, $this->payType['key']),
                ];
                \Yii::debug($params);
                return $params;
            }
        } else {
            Error('支付接口版本错误，请重置配置支付设置');
        }
    }

    public function getNotify($class, $AppID)
    {
        $this->AppID = $AppID;
        $pay = $this->getWechatPay();
        if (!$pay) {
            throw new \Exception('请联系管理员配置支付信息');
        }
        if ($this->apiVersion == 'v2') {
            $apiv2Key = $this->payType['key'];// 在商户平台上设置的APIv2密钥
            $inBodyArray = Transformer::toArray(\Yii::$app->request->rawBody);
            \Yii::error($inBodyArray);
            $sign = $inBodyArray['sign'];
            $signType = 'MD5';
            $calculated = Hash::sign(
                $signType ?? Hash::ALGO_MD5,// 如没获取到`sign_type`，假定默认为`MD5`
                Formatter::queryStringLike(Formatter::ksort($inBodyArray)),
                $apiv2Key
            );
            $signatureStatus = Hash::equals($calculated, $sign);
            if ($signatureStatus) {
                // 支付状态完全成功，可以更新订单的支付状态了
                // @todo 这里去完成你的订单状态修改操作
                $res = $class->paid($inBodyArray);
                if ($res) {
                    $xml = Transformer::toXml([
                        'return_code' => 'SUCCESS',
                        'return_msg' => 'OK',
                    ]);
                    echo $xml;
                }
            }
        } elseif ($this->apiVersion == 'v3') {
            $inWechatpaySignature = \Yii::$app->request->headers->get('wechatpay-signature');
            $inWechatpayTimestamp = \Yii::$app->request->headers->get('wechatpay-timestamp');
            $inWechatpayNonce = \Yii::$app->request->headers->get('wechatpay-nonce');
            $inBody = \Yii::$app->request->rawBody;
            $payment = $this->payType;
            $apiv3Key = $payment['key'];// 在商户平台上设置的APIv3密钥
            // 根据通知的平台证书序列号，查询本地平台证书文件，
            $platformPublicKeyInstance = Rsa::from($payment['publicPem'], Rsa::KEY_TYPE_PUBLIC);
            // 检查通知时间偏移量，允许5分钟之内的偏移
            $timeOffsetStatus = 300 >= abs(Formatter::timestamp() - (int)$inWechatpayTimestamp);
            $verifiedStatus = Rsa::verify(
            // 构造验签名串
                Formatter::joinedByLineFeed($inWechatpayTimestamp, $inWechatpayNonce, $inBody),
                $inWechatpaySignature,
                $platformPublicKeyInstance
            );
            if ($timeOffsetStatus && $verifiedStatus) {
                // 转换通知的JSON文本消息为PHP Array数组
                $inBodyArray = (array)json_decode($inBody, true);
                // 使用PHP7的数据解构语法，从Array中解构并赋值变量
                [
                    'resource' => [
                        'ciphertext' => $ciphertext,
                        'nonce' => $nonce,
                        'associated_data' => $aad
                    ]
                ] = $inBodyArray;
                // 加密文本消息解密
                $inBodyResource = AesGcm::decrypt($ciphertext, $apiv3Key, $nonce, $aad);
                // 把解密后的文本转换为PHP Array数组
                $inBodyResourceArray = (array)json_decode($inBodyResource, true);
                \Yii::error($inBodyResource);
                \Yii::error($inBodyResourceArray);
                \Yii::error($inBodyArray);
                // 支付状态完全成功，可以更新订单的支付状态了
                // @todo 这里去完成你的订单状态修改操作
                $res = $class->paid($inBodyResourceArray);
                if ($res) {
                    return true;
                }
            }
        }
        \Yii::$app->end();
    }

    /**
     * @param string $type 渠道
     * @param string $orderSn 商户订单号
     * @param string $outRefundNo 商户退款订单号
     * @param int $total 商户订单总金额
     * @param int $refund 退款金额，不可大于订单总金额
     * @return bool|void
     * @throws \Exception
     */
    public function refund($type, $orderSn, $outRefundNo, $total, $refund)
    {
        $pay = $this->getWechatPay([], $type);
        if (!$pay) {
            throw new \Exception('请联系管理员配置支付信息');
        }
        if ($this->payVersion == 'wx') {
            /**@var Order $order*/
            $order = Order::findOne(['pay_number' => $orderSn]);
            /**@var WeappPay $weappOrder*/
            $weappOrder = WeappPay::findOne(['order_sn' => substr($orderSn, 10)]);
            /**@var WechatWxpay $pay */
            return $pay->refund($order->oauth->oauthID, $weappOrder->pay_sn, $outRefundNo, $weappOrder->transaction_id, $total * 100, $refund * 100);
        } elseif ($this->payVersion == 'common') {
            if ($this->apiVersion == 'v3') {
                try {
                    $result = $pay->chain('v3/refund/domestic/refunds')
                        ->post([
                            'json' => [
                                'out_trade_no' => $orderSn,
                                'out_refund_no' => $outRefundNo,
                                'amount' => [
                                    // 退款金额
                                    'refund' => intval(strval($refund * 100)),
                                    // 原订单金额
                                    'total' => intval(strval($total * 100)),
                                    // 退款币种
                                    'currency' => 'CNY'
                                ]
                            ]
                        ]);
                    if ($result->getStatusCode() != 200) {
                        throw new \Exception('退款失败,请检查参数');
                    }
                    $result = json_decode($result->getBody()->getContents(), true);
                    return true;
                } catch (ClientException $clientException) {
                    Error($clientException->getMessage());
                } catch (\Exception $e) {
                    Error($e->getMessage());
                }
            } elseif ($this->apiVersion == 'v2') {
                try {
                    $result = $pay
                        ->v2->secapi->pay->refund
                        ->postAsync([
                            'xml' => [
                                'appid' => $this->payType['appid'],
                                'mch_id' => $this->payType['mchid'],
                                'out_trade_no' => $orderSn,
                                'out_refund_no' => $outRefundNo,
                                'total_fee' => (string)($total * 100),
                                'refund_fee' => (string)($refund * 100)
                            ],
                            'security' => true, //请求需要双向证书
                        ])
                        ->then(static function ($response) {
                            return Transformer::toArray((string)$response->getBody());
                        })
                        ->wait();
                    return true;
                } catch (ClientException $clientException) {
                    Error($clientException->getMessage());
                } catch (\Exception $e) {
                    Error($e->getMessage());
                }
            }
        }
    }

    /**
     * @param $type
     * @param $openid
     * @param $amount
     * @param $transferOrderSn
     * @param $desc
     * @return bool|void
     * @throws \Exception
     */
    public function transfers($type, $openid, $amount, $transferOrderSn, $desc = '转账')
    {
        $pay = $this->getWechatPay([], $type);
        if (!$pay) {
            throw new \Exception('请联系管理员配置支付信息');
        }
        if ($this->payVersion == 'wx') {
            Error('暂不支持该功能');
        } elseif ($this->payVersion == 'common') {
            if ($this->apiVersion == 'v3') {
                try {
                    $res = $pay->chain('v3/transfer/batches')
                        ->post([
                            'json' => [
                                'appid' => $this->payType['appid'],
                                'out_batch_no' => $transferOrderSn,
                                'batch_name' => $desc,
                                'batch_remark' => $desc,
                                'total_amount' => $amount * 100, // 企业付款金额，单位为分
                                'total_num' => 1, // 默认一笔
                                'transfer_detail_list' => [
                                    [
                                        'out_detail_no' => $transferOrderSn,
                                        'transfer_amount' => $amount * 100,
                                        'transfer_remark' => $desc,
                                        'openid' => $openid
                                    ]
                                ]
                            ]
                        ]);
                    if ($res->getStatusCode() != 200) {
                        throw new \Exception('打款失败,请检查参数');
                    }
                } catch (\GuzzleHttp\Exception\ClientException $guzzleException) {
                    $res = json_decode($guzzleException->getResponse()->getBody()->getContents(), true);
                    Error($res['message'] ?? $guzzleException->getMessage());
                } catch (\Exception $e) {
                    Error($e->getMessage());
                }
            } elseif ($this->apiVersion == 'v2') {
                try {
                    $res = $pay
                        ->v2->mmpaymkttransfers->promotion->transfers
                        ->postAsync([
                            'xml' => [
                                'mch_appid' => $this->payType['appid'],
                                'mchid' => $this->payType['mchid'],// 注意这个商户号，key是`mchid`非`mch_id`
                                'partner_trade_no' => $transferOrderSn,
                                'openid' => $openid,
                                'check_name' => 'NO_CHECK',
                                'amount' => (string)($amount * 100),
                                'desc' => $desc,
                                'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//终端IP
                            ],
                            'security' => true, //请求需要双向证书
                        ])
                        ->then(static function ($response) {
                            return Transformer::toArray((string)$response->getBody());
                        })
                        ->otherwise(static function ($e) {
                            // 更多`$e`异常类型判断是必须的，这里仅列出一种可能情况，请根据实际对接过程调整并增加
                            if ($e instanceof \GuzzleHttp\Promise\RejectionException) {
                                return Transformer::toArray((string)$e->getReason()->getBody());
                            }
                            return [];
                        })
                        ->wait();
                    if (isset($res['result_code']) && $res['result_code'] == 'FAIL') {
                        Error($res['err_code_des'] ?? '打款失败,请检查配置');
                    }
                    return true;
                } catch (ClientException $clientException) {
                    Error($clientException->getMessage());
                } catch (\Exception $e) {
                    Error($e->getMessage());
                }
            }
        }
    }

    /**
     * 分账
     * @param WeappPay $order
     * @param String $share
     * @return bool
     * @throws \Exception
     */
    public function profitsharingorder($order, $share)
    {
        $pay = $this->getWechatPay();
        if (!$pay) {
            Error('请联系管理员配置支付信息');
        }
        if ($this->payVersion != 'wx') {
            Error('当前未选择支付管理');
        }
        /**@var WechatWxpay $pay */
        $res = $pay->profitSharingOrder(
            $order->order->oauth->oauthID,
            $order->pay_sn,
            $order->transaction_id,
            $share
        );
        if ($res === false) {
            return $pay->errMsg;
        }
        return $res;
    }
}
