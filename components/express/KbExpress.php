<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/5/20
 * Time: 13:14
 */

namespace app\components\express;


use GuzzleHttp\Exception\TransferException;

class KbExpress extends BaseExpress
{
    protected $url = 'https://kop.kuaidihelp.com/api';

    protected $status = [
        'collected' => '收件',
        'sending' => '运送中',
        'delivering' => '派件中',
        'signed' => '签收',
        'question' => '问题件',
        'allograph' => '代收'
    ];

    public function wrap($param)
    {
        if ((!isset($param['code']) && !isset($param['name'])) || !isset($param['no']) || !isset($param['mobile'])) {
            $this->returnError('参数不完整');
        }
        if (!isset($param['code']) || empty($param['code'])) {
            $code = $this->getCode($param['name']);
            if (!$code) {
                $this->returnError('暂不支持该物流公司');
            }
            $param['code'] = $code;
        }
        if ($param['code'] == 'sf' || $param['code'] == 'sfky') {
            if (!preg_match('/^1\d{10}$/', $param['mobile'])) {
                $this->returnError('手机号格式不正确');
            }
            $mobile = substr($param['mobile'], -4);
            $this->no = $param['no'];
            $this->mobile = $mobile;
        } else {
            $this->mobile = $param['mobile'];
            $this->no = $param['no'];
        }
        $this->code = $param['code'];
    }

    private function getCode($name)
    {
        $express = \Yii::$app->basePath . '/modules/setting/app/express.json';
        $list = json_decode(file_get_contents($express),true);
        foreach ($list as $item) {
            if ($item['name'] == $name) {
                return $item['code'];
            }
        }
        return null;
    }

    public function getResult()
    {
        $params = [
            'waybill_no' => $this->no,
            'exp_company_code' => $this->code,
            'result_sort' => $this->sort,
            'phone' => $this->mobile
        ];
        $cacheKey = 'LEADSHOP_KB_EXPRESS_BY_' . md5(json_encode($params));
        $res = \Yii::$app->cache->get($cacheKey);
        if ($res) {
            return $res;
        }
        $config = $this->getConfig();
        if (!isset($config['app_id']) || empty($config['app_key'])) {
            Error('请配置物流接口');
        }
        $header = [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        ];
        $method = 'express.info.get';
        $time = time();
        $body = [
            "app_id" => $config['app_id'],
            "method" => $method,
            "sign" => md5($config['app_id'] . $method . $time . $config['app_key']),
            "ts" => $time,
            "data" => json_encode($params)
        ];
        try {
            $res = $this->post($this->url, $body, $header);
            \Yii::$app->cache->set($cacheKey, $res, 60 * 5);
            return $res;
        } catch (TransferException $e) {
            $httpCode = $e->getResponse()->getStatusCode();
            $headers = $e->getResponse()->getHeaders();
            $header = '';
            foreach ($headers as $key => $item) {
                $header .= $key . ': ' . ucwords(join(';', $item)) . "\r\n";
            }

            if ($httpCode == 400 && strpos($header, "Invalid Param Location") !== false) {
                $msg = '参数错误';
            } elseif ($httpCode == 400 && strpos($header, "Invalid AppCode") !== false) {
                $msg = "AppCode错误";
            } elseif ($httpCode == 400 && strpos($header, "Invalid Url") !== false) {
                $msg = "请求的 Method、Path 或者环境错误";
            } elseif ($httpCode == 403 && strpos($header, "Unauthorized") !== false) {
                $msg = "服务未被授权（或URL和Path不正确）";
            } elseif ($httpCode == 403 && strpos($header, "Quota Exhausted") !== false) {
                $msg = "套餐包次数用完";
            } elseif ($httpCode == 500) {
                $msg = "API网关错误";
            } elseif ($httpCode == 0) {
                $msg = "URL错误";
            } else {
                $msg = "参数名错误 或 其他错误";
                print($httpCode);
                $headers = explode("\r\n", $header);
                $headList = array();
                foreach ($headers as $head) {
                    $value = explode(':', $head);
                    $headList[$value[0]] = $value[1];
                }
                $msg = $headList['x-ca-error-message'];
            }
            $this->returnError($msg);
        } catch (\Exception $e) {
            $this->returnError($e->getMessage());
        }
    }

    public function parseResult($content)
    {
        $code = (int)$content['code'];
        if ($code == 0) {
            return [
                'state' => (int)$content['code'],
                'message' => $this->status[$content['data'][0]['status']],
                'list' =>  array_map(function ($item) {
                    return [
                        'desc' => $item['context'],
                        'datetime' => $item['time'],
                    ];
                }, $content['data'][0]['data']),
            ];
        }
        return [
            'state' => $code,
            'message' => $content['msg'],
            'list' =>  [],
        ];
    }

    private function returnError($msg)
    {
        \Yii::$app->response->data = [
            'code' => -1,
            'message' => $msg
        ];
        \Yii::$app->end();
    }
}
