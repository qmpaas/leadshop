<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\core;

use yii\base\BaseObject;

abstract class Request extends BaseObject
{
    use HttpRequest;

    public $baseUrl = '';
    public $urlEncodeQueryString = true;
    public $requestHeader;

    /**
     * @param $url
     * @param array $params
     * @param array $data
     * @return mixed
     */
    public function httpGet($url, $params = [])
    {
        $url = $this->getUrl($url);
        $url = $this->appendParams($url);
        $res = $this->get($url, $params, $this->requestHeader);
        if ($res['code'] !== 0) {
            Error($res['msg']);
        }
        return $res['data'];
    }

    /**
     * @param $url
     * @param array $params
     * @param array $data
     * @return mixed
     */
    public function httpPost($url, $params = [], $data = [])
    {
        $url = $this->getUrl($url);
        $url = $this->appendParams($url, $params);
        $res = $this->post($url, $data, $this->requestHeader);
        if ($res['code'] !== 0) {
            Error($res['msg']);
        }
        return $res['data'];
    }

    private function getUrl($url)
    {
        if (mb_stripos($url, 'http') === 0) {
            return $url;
        }
        $url = mb_stripos($url, '/') === 0 ? mb_substr($url, 1) : $url;
        $baseUrl = base64_decode($this->baseUrl);
        $baseUrl = mb_stripos($baseUrl, '/') === (mb_strlen($baseUrl) - 1) ? $baseUrl : $baseUrl . '/';
        return $baseUrl . $url;
    }

    private function appendParams($url, $params = [])
    {
        if (!is_array($params)) {
            return $url;
        }
        if (!count($params)) {
            return $url;
        }
        $url = trim($url, '?');
        $url = trim($url, '&');
        $queryString = $this->paramsToQueryString($params);
        if (mb_stripos($url, '?')) {
            return $url . '&' . $queryString;
        } else {
            return $url . '?' . $queryString;
        }
    }

    private function paramsToQueryString($params = [])
    {
        if (!is_array($params)) {
            return '';
        }
        if (!count($params)) {
            return '';
        }
        $str = '';
        foreach ($params as $k => $v) {
            if ($this->urlEncodeQueryString) {
                $v = urlencode($v);
            }
            $str .= "{$k}={$v}&";
        }
        return trim($str, '&');
    }
}