<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/29
 * Time: 10:32
 */

namespace app\components;

use GuzzleHttp\Exception\ClientException;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use yii\base\Component;

class Sms extends Component
{
    const MODULE_ADMIN = 'admin';
    const MODULE_INDUSTRY = 'industry';

    /** @var EasySms $easySms */
    private $easySms;
    private $moduleSmsList = [];

    /**
     * @param string $module Sms::MODULE_ADMIN 或 Sms::MODULE_INDUSTRY
     * @param array  $params
     * @return mixed
     * @throws \Exception
     */
    public function module($module, $params = [])
    {
        if (isset($this->moduleSmsList[$module])) {
            return $this->moduleSmsList[$module];
        }
        switch ($module) {
            case static::MODULE_ADMIN:
                break;
            case static::MODULE_INDUSTRY:
                 $option = $params;
                 if ($option) {
                    $smsConfig = \Yii::$app->params['appSms']['aliyun'] ?? [];
                    $moduleSms = $this->getSms(
                        [
                            'aliyun' => [
                                'access_key_id'     => isset($option['access_key_id']) ? $option['access_key_id'] : $smsConfig['access_key_id'],
                                'access_key_secret' => isset($option['access_key_secret']) ?
                                    $option['access_key_secret'] : $smsConfig['access_key_secret'],
                                'sign_name'         => isset($option['template_name']) ? $option['template_name'] : $smsConfig['sign_name'],
                            ],
                        ]
                    );
                } else {
                    throw new \Exception('短信信息尚未配置。');
                }

                $this->moduleSmsList[$module] = $moduleSms;
                break;
            default:
                throw new \Exception('尚未支持的module: ' . $module);
                break;
        }
        return $this->moduleSmsList[$module];
    }

    public function send($mobile, $message)
    {
        try {
            $res =  $this->easySms->send($mobile, $message);
            //返回是否成功,临时待定
            if ($res['aliyun']['status'] == 'success') {
                return true;
            } else {
                return false;
            }
            

        } catch (NoGatewayAvailableException $exception) {
            $es = $exception->getExceptions();
            foreach ($es as $e) {
                /** @var GatewayErrorException $e */
                if (isset($e->raw) && isset($e->raw['Code']) && $e->raw['Code'] == 'isv.MOBILE_NUMBER_ILLEGAL') {
                    throw new GatewayErrorException("无效的号码 {$mobile}", $e->getCode(), $e->raw);
                }
                if ($e instanceof ClientException) {
                    $result = json_decode($e->getResponse()->getBody()->getContents(), true);
                    if ($result && is_array($result) && isset($result['Message']) && isset($result['Code'])) {
                        if ($result['Code'] == 'InvalidAccessKeyId.NotFound') {
                            throw new GatewayErrorException("无效的AccessKeyId", $e->getCode(), $result);
                        }
                        if ($result['Code'] == 'SignatureDoesNotMatch') {
                            throw new GatewayErrorException(
                                "Signature不匹配，请检查AccessKeySecret是否正确",
                                $e->getCode(),
                                $result
                            );
                        }
                        throw new GatewayErrorException($result['Message'], $e->getCode(), $result);
                    }
                }
                throw $e;
            }
        }
    }

    private function getSms($gateways)
    {
        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout'  => 5.0,
            // 默认发送配置
            'default'  => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
                // 默认可用的发送网关
                'gateways' => ['aliyun',],
            ],
            // 可用的网关配置
            'gateways' => [],
        ];
        if ($gateways && is_array($gateways)) {
            foreach ($gateways as $name => $gateway) {
                $config['gateways'][$name] = $gateway;
            }
        }
        $this->easySms = new EasySms($config);
        return $this;
    }
}
