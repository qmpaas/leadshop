<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\express;

use GuzzleHttp\Exception\TransferException;

class AliExpress extends BaseExpress
{
    protected $url = 'https://wdexpress.market.alicloudapi.com/gxali';

    protected $status = [
        '-1' => '单号或快递公司代码错误',
        '0' => '暂无轨迹',
        '1' => '快递收件',
        '2' => '在途中',
        '3' => '签收',
        '4' => '问题件',
        '5' => '疑难件',
        '6' => '退件签收'
    ];

    public function wrap($param)
    {
        if (!isset($param['name']) || !isset($param['no']) || !isset($param['mobile'])) {
            Error('参数不完整');
        }
        if (strpos($param['name'], '顺丰') !== false) {
            if (!preg_match('/^1\d{10}$/', $param['mobile'])) {
                Error('手机号格式不正确');
            }
            $mobile = substr($param['mobile'], -4);
            $this->no = $param['no'] . ":" . $mobile;
        } else {
            $this->no = $param['no'];
        }
    }

    public function getResult()
    {
        $params = [
            'n' => $this->no,
        ];
        $config = $this->getConfig();
        if (!isset($config['code']) || empty($config['code'])) {
            Error('请配置物流接口');
        }
        $header = [
            'Authorization' => 'APPCODE ' . $config['code'],
        ];
        try {
            $response = $this->get($this->url, $params, $header);
            if (isset($response['returnCode']) && $response['returnCode'] != 200) {
                Error($response['message']);
            }
            return $response;
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
            Error($msg);
        } catch (\Exception $e) {
            Error($e->getMessage());
        }
    }

    public function parseResult($content)
    {
        return [
            'state' => (int)$content['State'],
            'message' => $this->status[$content['State']],
            'list' =>  array_map(function ($item) {
                return [
                    'desc' => $item['AcceptStation'],
                    'datetime' => $item['AcceptTime'],
                ];
            }, $content['Traces']),
        ];
    }
}