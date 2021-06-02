<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   wiki
 * @Last Modified time: 2021-03-13 16:37:16
 */
namespace framework\wechat\Lib;

use CURLFile;

/**
 * 微信接口通用类
 *
 * @category WechatSDK
 * @subpackage library
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/05/28 11:55
 */
class Tools
{

    /**
     * 判断字符串是否经过编码方法
     * @param string $str
     * @return bool
     */
    public static function isBase64($str)
    {
        if ($str == base64_encode(base64_decode($str))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 产生随机字符串
     * @param int $length 指定字符长度
     * @param string $str 字符串前缀
     * @return string
     */
    public static function createNoncestr($length = 32, $str = "")
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 数据生成签名
     * @param array $data 签名数组
     * @param string $method 签名方法
     * @return bool|string 签名值
     */
    public static function getSignature($data, $method = "sha1")
    {
        if (!function_exists($method)) {
            return false;
        }
        ksort($data);
        $params = array();
        foreach ($data as $key => $value) {
            $params[] = "{$key}={$value}";
        }
        return $method(join('&', $params));
    }

    /**
     * 生成支付签名
     * @param array $option
     * @param string $partnerKey
     * @return string
     */
    public static function getPaySign($option, $partnerKey)
    {
        ksort($option);
        $buff = '';
        foreach ($option as $k => $v) {
            $buff .= "{$k}={$v}&";
        }
        return strtoupper(md5("{$buff}key={$partnerKey}"));
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $id 数字索引子节点key转换的属性名
     * @return string
     */
    public static function arr2xml($data, $root = 'xml', $item = 'item', $id = 'id')
    {
        return "<{$root}>" . self::_data_to_xml($data, $item, $id) . "</{$root}>";
    }

    /**
     * XML内容生成
     * @param array $data 数据
     * @param string $item 子节点
     * @param string $id 节点ID
     * @param string $content 节点内容
     * @return string
     */
    private static function _data_to_xml($data, $item = 'item', $id = 'id', $content = '')
    {
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "{$item} {$id}=\"{$key}\"";
            $content .= "<{$key}>";
            if (is_array($val) || is_object($val)) {
                $content .= self::_data_to_xml($val);
            } elseif (is_numeric($val)) {
                $content .= $val;
            } else {
                $content .= '<![CDATA[' . preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $val) . ']]>';
            }
            list($_key) = explode(' ', $key . ' ');
            $content .= "</$_key>";
        }
        return $content;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @return array
     */
    public static function xml2arr($xml)
    {
        $disableEntities = libxml_disable_entity_loader(true);
        $result          = json_decode(Tools::json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        libxml_disable_entity_loader($disableEntities);
        return $result;
    }

    /**
     * 生成安全JSON数据
     * @param array $array
     * @return string
     */
    public static function json_encode($array)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function ($matches) {
            return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
        }, json_encode($array));
    }

    /**
     * 以get方式提交请求
     * @param $url
     * @return bool|mixed
     */
    public static function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 以post方式提交请求
     * @param string $url
     * @param array|string $data
     * @return bool|mixed
     */
    public static function httpPost($url, $data, $header = false)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, self::_buildPost($data));
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 使用证书，以post方式提交xml到对应的接口url
     * @param string $url POST提交的内容
     * @param array $data 请求的地址
     * @param string $ssl_cer 证书Cer路径 | 证书内容
     * @param string $ssl_key 证书Key路径 | 证书内容
     * @param int $second 设置请求超时时间
     * @return bool|mixed
     */
    public static function httpsPost($url, $data, $ssl_cer = null, $ssl_key = null, $second = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $second);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!is_null($ssl_cer) && file_exists($ssl_cer) && is_file($ssl_cer)) {
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $ssl_cer);
        }
        if (!is_null($ssl_key) && file_exists($ssl_key) && is_file($ssl_key)) {
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $ssl_key);
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, self::_buildPost($data));
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * POST数据过滤处理
     * @param array $data
     * @return array
     */
    private static function _buildPost(&$data)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                if (is_string($value) && $value[0] === '@' && class_exists('CURLFile', false)) {
                    $filename                        = realpath(trim($value, '@'));
                    file_exists($filename) && $value = new CURLFile($filename);
                }
            }
        }
        return $data;
    }

    /**
     * 读取微信客户端IP
     * @return null|string
     */
    public static function getAddress()
    {
        foreach (array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR') as $header) {
            if (!isset($_SERVER[$header]) || ($spoof = $_SERVER[$header]) === null) {
                continue;
            }
            sscanf($spoof, '%[^,]', $spoof);
            if (!filter_var($spoof, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $spoof = null;
            } else {
                return $spoof;
            }
        }
        return '0.0.0.0';
    }

    /**
     * 设置缓存，按需重载
     * @param string $cachename
     * @param mixed $value
     * @param int $expired
     * @return bool
     */
    public static function setCache($cachename, $value, $expired = 0)
    {
        return Cache::set($cachename, $value, $expired);
    }

    /**
     * 获取缓存，按需重载
     * @param string $cachename
     * @return mixed
     */
    public static function getCache($cachename)
    {
        return Cache::get($cachename);
    }

    /**
     * 清除缓存，按需重载
     * @param string $cachename
     * @return bool
     */
    public static function removeCache($cachename)
    {
        return Cache::del($cachename);
    }

    /**
     * SDK日志处理方法
     * @param string $msg 日志行内容
     * @param string $type 日志级别
     */
    public static function log($msg, $type = 'MSG')
    {
        Cache::put($type . ' - ' . $msg);
    }

}
