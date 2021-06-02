<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-05-18 08:58:49
 */
namespace framework\wechat;

use framework\wechat\Lib\Common;
use framework\wechat\Lib\Tools;

/**
 * 微信网页授权
 */
class WechatApplet extends Common
{
    const WEAPP_PREFIX             = 'https://api.weixin.qq.com';
    const WEAPP_AUTH_URL           = '/sns/jscode2session?';
    const WEAPP_USER_URL           = '/cgi-bin/user/info?';
    const E_PROXY_LOGIN_FAILED     = 'E_PROXY_LOGIN_FAILED';
    const NETWORK_TIMEOUT          = 3000;
    const WX_LOGIN_EXPIRES         = 7200;
    const WX_HEADER_CODE           = 'x-wx-code';
    const WX_HEADER_ENCRYPTED_DATA = 'x-wx-encrypted-data';
    const WX_HEADER_IV             = 'x-wx-iv';
    const WX_HEADER_SKEY           = 'x-wx-skey';

    /**
     * 用户登录接口
     * @param {string} $code        wx.login 颁发的 code
     * @param {string} $encryptedData 加密过的用户信息
     * @param {string} $iv          解密用户信息的向量
     * @return {array} { loginState, userinfo }
     */
    public function getUserInfo($code, $encryptedData, $iv)
    {
        // 1. 获取 session key
        $Session = $this->getCode2Session($code);

        if ($Session) {
            // 2. 生成 3rd key (skey)
            $skey = sha1($Session['session_key'] . mt_rand());

            /**
             * 3. 解密数据
             * 由于官方的解密方法不兼容 PHP 7.1+ 的版本
             * 这里弃用微信官方的解密方法
             * 采用推荐的 openssl_decrypt 方法（支持 >= 5.3.0 的 PHP）
             * @see http://php.net/manual/zh/function.openssl-decrypt.php
             */
            $decryptData = \openssl_decrypt(
                base64_decode($encryptedData),
                'AES-128-CBC',
                base64_decode($Session['session_key']),
                OPENSSL_RAW_DATA,
                base64_decode($iv)
            );
            $userinfo = json_decode($decryptData, true);
            if ($userinfo) {
                $userinfo['openId'] = $Session['openid'];
            }
            $sessionKey = $Session['session_key'];
            return compact('userinfo', 'skey', 'sessionKey');
        } else {
            Error($this->errMsg, $this->errCode, 'wechat');
        }

    }

    /**
     * 通过 code 获取 AccessToken 和 openid
     * @return bool|array
     */
    public function getCode2Session($code = "")
    {
        if (empty($code)) {
            Tools::log("getOauthAccessToken Fail, Because there is no access to the code value in get.", "MSG - {$this->appid}");
            return false;
        }
        $result = Tools::httpGet(self::WEAPP_PREFIX . self::WEAPP_AUTH_URL . "appid={$this->appid}&secret={$this->appsecret}&js_code={$code}&grant_type=authorization_code");
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                Tools::log("WechatOauth::getOauthAccessToken Fail.{$this->errMsg} [{$this->errCode}]", "ERR - {$this->appid}");
                return false;
            }
            return $json;
        }
        return false;
    }

    public function getMobile($code, $encryptedData, $iv)
    {
        // 1. 获取 session key
        $Session     = $this->getCode2Session($code);
        $decryptData = openssl_decrypt(
            base64_decode($encryptedData),
            'AES-128-CBC',
            base64_decode($Session['session_key']),
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );

        return json_decode($decryptData, true);
    }

    /**
     * 刷新access token并续期
     * @param string $refresh_token
     * @return bool|array
     */
    public function getOauthRefreshToken($refresh_token)
    {
        $result = Tools::httpGet(self::WEAPP_PREFIX . self::OAUTH_REFRESH_URL . "appid={$this->appid}&grant_type=refresh_token&refresh_token={$refresh_token}");
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                Tools::log("WechatOauth::getOauthRefreshToken Fail.{$this->errMsg} [{$this->errCode}]", "ERR - {$this->appid}");
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return bool|array {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getOauthUserInfo($access_token, $openid)
    {
        $result = Tools::httpGet(self::WEAPP_PREFIX . self::OAUTH_USERINFO_URL . "access_token={$access_token}&openid={$openid}");
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                Tools::log("WechatOauth::getOauthUserInfo Fail.{$this->errMsg} [{$this->errCode}]", "ERR - {$this->appid}");
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 检验授权凭证是否有效
     * @param string $access_token
     * @param string $openid
     * @return bool 是否有效
     */
    public function getOauthAuth($access_token, $openid)
    {
        $result = Tools::httpGet(self::WEAPP_PREFIX . self::OAUTH_AUTH_URL . "access_token={$access_token}&openid={$openid}");
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                Tools::log("WechatOauth::getOauthAuth Fail.{$this->errMsg} [{$this->errCode}]", "ERR - {$this->appid}");
                return false;
            } elseif (intval($json['errcode']) === 0) {
                return true;
            }
        }
        return false;
    }

}
