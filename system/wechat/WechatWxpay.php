<?php

namespace framework\wechat;

use framework\wechat\Lib\Tools;

class WechatWxpay
{
    /** 支付接口基础地址 */
    const MCH_BASE_URL = 'https://api.weixin.qq.com';

    /** 公众号appid */
    public $appid;

    /** 公众号配置 */
    public $config;

    /** 商户身份ID */
    public $mch_id;

    /** 商户支付密钥Key */
    public $partnerKey;

    /** 证书路径 */
    public $ssl_cer;
    public $ssl_key;

    /** 执行错误消息及代码 */
    public $errMsg;
    public $errCode;

    public $access_token;

    public $pay_version = 'wx';

    /**
     * WechatPay constructor.
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->config     = Loader::config($options);
        $this->appid      = isset($this->config['appid']) ? $this->config['appid'] : '';
        $this->mch_id     = isset($this->config['mch_id']) ? $this->config['mch_id'] : '';
        $this->partnerKey = isset($this->config['partnerkey']) ? $this->config['partnerkey'] : '';
        $this->ssl_cer    = isset($this->config['ssl_cer']) ? $this->config['ssl_cer'] : '';
        $this->ssl_key    = isset($this->config['ssl_key']) ? $this->config['ssl_key'] : '';
    }

    /**
     * 获取当前错误内容
     * @return string
     */
    public function getError()
    {
        return $this->errMsg;
    }

    /**
     * 当前当前错误代码
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errCode;
    }

    /**
     * 获取当前操作公众号APPID
     * @return string
     */
    public function getAppid()
    {
        return $this->appid;
    }

    /**
     * 获取SDK配置参数
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * POST提交XML
     * @param array $data
     * @param string $url
     * @return mixed
     */
    public function postJson($data, $url)
    {
        return Tools::httpPost($url, $data, true);
    }

    /**
     * 使用证书post请求XML
     * @param array $data
     * @param string $url
     * @return mixed
     */
    public function postJsonSSL($data, $url)
    {
        return Tools::httpsPost($url, $data, $this->ssl_cer, $this->ssl_key);
    }

    /**
     * POST提交获取Array结果
     * @param array $data 需要提交的数据
     * @param string $url
     * @param string $method
     * @return array
     */
    public function getArrayResult($data, $url, $method = 'postJson')
    {
        return $this->$method($data, $url);
    }

    /**
     * 解析返回的结果
     * @param array $result
     * @return bool|array
     */
    protected function _parseResult($result)
    {
        if (empty($result)) {
            $this->errCode = 'result error';
            $this->errMsg  = '解析返回结果失败';
            return false;
        }
        if (isset($result['errcode']) && $result['errcode'] != 0) {
            $this->errMsg  = $result['errmsg'] ?? '支付返回信息错误';
            $this->errCode = $result['errcode'];
            return false;
        }
        return $result;
    }

    /**
     * 下单
     * @param $openid
     * @param $body
     * @param $out_trade_no
     * @param $total_fee
     * @return mixed
     */
    public function unifiedOrder($openid, $body, $out_trade_no, $total_fee)
    {
        $data = [
            'openid' => $openid,
            'combine_trade_no' => $out_trade_no,
            'sub_orders' => [
                [
                    'mchid' => $this->mch_id,
                    'amount' => $total_fee,
                    'trade_no' => $out_trade_no,
                    'description' => $body
                ]
            ]
        ];
        $result = $this->getArrayResult(json_encode($data, JSON_UNESCAPED_UNICODE), self::MCH_BASE_URL . '/shop/pay/createorder?access_token=' . $this->access_token);
        $result = json_decode($result, true);
        if (false === $this->_parseResult($result)) {
            return false;
        }
        return $result;
    }

    /**
     * 关闭订单
     * @param string $out_trade_no
     * @return bool
     */
    public function closeOrder($out_trade_no)
    {
        $data   = array('out_trade_no' => $out_trade_no);
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/pay/closeorder');
        if (false === $this->_parseResult($result)) {
            return false;
        }
        return ($result['return_code'] === 'SUCCESS');
    }

    /**
     * 查询订单详情
     * @param $out_trade_no
     * @return bool|array
     */
    public function queryOrder($out_trade_no)
    {
        $data   = array('out_trade_no' => $out_trade_no);
        $result = $this->getArrayResult($data, self::MCH_BASE_URL . '/pay/orderquery');
        if (false === $this->_parseResult($result)) {
            return false;
        }
        return $result;
    }

    /**
     * 订单退款接口
     * @param string $openid 用户openid
     * @param string $out_trade_no 商户订单号
     * @param string $out_refund_no 商户退款订单号
     * @param int $total_fee 商户订单总金额
     * @param int $refund_fee 退款金额，不可大于订单总金额
     * @return bool
     */
    public function refund($openid, $out_trade_no, $out_refund_no, $transaction_id, $total_fee, $refund_fee)
    {
        $data = [
            'openid' => $openid,
            'mchid' => $this->mch_id,
            'trade_no' => $out_trade_no,
            'transaction_id' => $transaction_id,
            'refund_no' => $out_refund_no,
            'total_amount' => $total_fee,
            'refund_amount' => $refund_fee
        ];
        $result = $this->getArrayResult(json_encode($data), self::MCH_BASE_URL . '/shop/pay/refundorder?access_token=' . $this->access_token);
        if (false === $this->_parseResult($result)) {
            return false;
        }
        $result = json_decode($result, true);
        return ($result['errcode'] === 0);
    }

    /**
     * 退款查询接口
     * @param string $out_trade_no
     * @return bool|array
     */
    public function refundQuery($out_trade_no)
    {

    }

    /**
     * 企业付款
     * @param string $openid 红包接收者OPENID
     * @param int $amount 红包总金额
     * @param string $billno 商户订单号
     * @param string $desc 备注信息
     * @return bool|array
     * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_2
     */
    public function transfers($openid, $amount, $billno, $desc)
    {
        Error('暂不支持');
    }

    /**
     * 企业付款查询
     * @param string $billno
     * @return bool|array
     * @link https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=14_3
     */
    public function queryTransfers($billno)
    {
        Error('暂不支持');
    }

}
