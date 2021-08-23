<?php

namespace app\components;

use GuzzleHttp\Exception\TransferException;

class WaybillPrint extends Waybill
{
    protected $url = 'https://kop.kuaidihelp.com/api';

    /**@var string $agentId 目标云打印机的访问密钥*/
    protected $agentId;
    /**@var int $printType 打印类型，1：仅生成底单，jpg格式； 2：仅打印； 3：打印并生成jpg格式底单； 7：仅生成底单，pdf格式；默认为3*/
    protected $printType = 3;

    public function getResult()
    {
        $params = [
            'print_type' => $this->printType,
            'print_data' => [
                [
                    'tid' => $this->orderSn,
                    'waybill_code' => '',
                    'cp_code' => $this->shipperType,
                    'sender' => [
                        'name' => $this->bName,
                        'mobile' => $this->bMobile,
                        'phone' => $this->bMobile,
                        'address' => [
                            'province' => $this->bProvince,
                            'city' => $this->bCity,
                            'district' => $this->bDistrict,
                            'detail' => $this->bAddress
                        ]
                    ],
                    'recipient' => [
                        'name' => $this->name,
                        'mobile' => $this->mobile,
                        'phone' => $this->mobile,
                        'address' => [
                            'province' => $this->province,
                            'city' => $this->city,
                            'district' => $this->district,
                            'detail' => $this->address
                        ]
                    ],
                    'routing_info' => [],
                    'goods_name' => $this->tradeName
                ]
            ],
        ];
        $config = $this->getConfig();
        if (!isset($config['app_id']) || empty($config['app_key']) || empty($config['agent_id'])) {
            Error('请配置快宝开放平台');
        }
        $params['agent_id'] = $config['agent_id'];
        $header = [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        ];
        $method = 'cloud.print.waybill';
        $time = time();
        $body = [
            "app_id" => $config['app_id'],
            "method" => $method,
            "sign" => md5($config['app_id'] . $method . $time . $config['app_key']),
            "ts" => $time,
            "data" => json_encode($params)
        ];
        try {
            return $this->post($this->url, $body, $header);
        } catch (TransferException $e) {
            $httpCode = $e->getResponse()->getStatusCode();
            $headers = $e->getResponse()->getHeaders();
            $msg = [
                'code' => $httpCode,
                'header' => $headers,
                'msg' => '"参数名错误 或 其他错误"',
            ];
            $this->returnError($msg);
        } catch (\Exception $e) {
            $this->returnError($e->getMessage());
        }
    }

    public function parseResult($content)
    {
        if ($content['code'] == 221009) {
            $errMsg = $content['data'][$this->orderSn]['message'] ?? $content['msg'] ?? '请检查快宝配置';
            Error($errMsg);
        }
        if ($content['code'] != 0) {
            Error($content['msg']);
        }
        if ($content['data'][$this->orderSn]['status'] == 'success') {
            return [
                'freight_sn' => $content['data'][$this->orderSn]['task_info']['waybill_code'] ?? '',
                'preview_image' => $content['data'][$this->orderSn]['preview_image'] ?? ''
            ];
        } elseif ($content['data'][$this->orderSn]['status'] == 'failure') {
            if ($content['data'][$this->orderSn]['message'] == '已有相同tid的任务在执行,请30秒后重试') {
              Error('频繁获取电子面单，请30秒后重试');
            }
            Error($content['data'][$this->orderSn]['message']);
        } else {
            Error('获取电子面单出错');
        }
    }
}
