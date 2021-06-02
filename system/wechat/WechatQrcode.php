<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   wiki
 * @Last Modified time: 2021-03-13 16:49:09
 */
namespace framework\wechat;

use framework\wechat\Lib\Common;
use framework\wechat\Lib\Tools;

/**
 * 微信网页授权
 */
class WechatQrcode extends Common
{
    const WEAPP_PREFIX             = 'https://api.weixin.qq.com';
    const WEAPP_AUTH_URL           = '/wxa/getwxacodeunlimit?';

    /**
     * 创建二维码
     * @param array $data 二维码数据
     */
    public function createQrcode($data = '')
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::WEAPP_PREFIX . self::WEAPP_AUTH_URL . "access_token={$this->access_token}", Tools::json_encode($data));
        if ($result) {
            $json = json_decode($result, true);
            if (isset($json['errcode']) && $json['errcode']>0) {
                if ($json['errcode'] == '41030') {
                    Error('线上小程序暂未发布，无法获取页面信息');
                }
                $errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                Error($errMsg);
            }
            return $result;
        } else {
            Error('调用失败');
        }
    }

}
