<?php

namespace framework\wechat;

use framework\wechat\Lib\Common;
use framework\wechat\Lib\Tools;

class WechatSubscribe extends Common
{

    /**
     * @param $tid integer 模板标题 id，可通过接口获取，也可登录小程序后台查看获取 例如AT0002
     * @param $kidList array 开发者自行组合好的模板关键词列表，关键词顺序可以自由搭配（例如 [3,5,4] 或 [4,5,3]），最多支持5个，最少2个关键词组合
     * @param $sceneDesc string 服务场景描述，15个字以内
     * @throws \Exception
     * 组合模板并添加至帐号下的个人模板库
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.addTemplate.html
     */
    public function addTemplate($tid, $kidList, $sceneDesc)
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $api = "https://api.weixin.qq.com/wxaapi/newtmpl/addtemplate?access_token={$this->access_token}";

        $result = Tools::httpPost($api, Tools::json_encode([
            'tid'       => $tid,
            'kidList'   => $kidList,
            'sceneDesc' => $sceneDesc,
        ]), true);
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || (!empty($json['errcode']) && $json['errcode'] != '200022')) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * @param $priTmplId string 要删除的模板id 例如wDYzYZVxobJivW9oMpSCpuvACOfJXQIoKUm0PY397Tc
     * @return array|bool
     * @throws \Exception
     * 删除帐号下的某个模板
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.deleteTemplate.html
     */
    public function deleteTemplate($priTmplId)
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $api    = "https://api.weixin.qq.com/wxaapi/newtmpl/deltemplate?access_token={$this->access_token}";
        $result = Tools::httpPost($api, Tools::json_encode([
            'priTmplId' => $priTmplId,
        ]));
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return true;
        }
        return false;
    }

    /**
     * @return array|bool
     * @throws \Exception
     * 获取小程序账号的类目
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getCategory.html
     */
    public function getCategory()
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $api    = "https://api.weixin.qq.com/wxaapi/newtmpl/getcategory?access_token={$this->access_token}";
        $result = Tools::httpGet($api);
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * @param $tid string 模板标题 id，可通过接口获取
     * @return array|bool
     * @throws \Exception
     * 获取模板标题下的关键词列表
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getPubTemplateKeyWordsById.html
     */
    public function getPubTemplateKeyWordsById($tid)
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $api    = "https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatekeywords?access_token={$this->access_token}";
        $result = Tools::httpGet($api . '&tid=' . $tid);
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * @param $ids string 类目 id，多个用逗号隔开
     * @param $start number 用于分页，表示从 start 开始。从 0 开始计数。
     * @param $limit number 用于分页，表示拉取 limit 条记录。最大为 30。
     * @return array|bool
     * @throws \Exception
     * 获取帐号所属类目下的公共模板标题
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getPubTemplateTitleList.html
     */
    public function getPubTemplateTitleList($ids, $start, $limit)
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        if ($limit >= 30) {
            $limit = 30;
        }
        $api    = "https://api.weixin.qq.com/wxaapi/newtmpl/getpubtemplatetitles?access_token={$this->access_token}";
        $result = Tools::httpGet($api);
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * @return array|bool
     * @throws \Exception
     * 获取当前帐号下的个人模板列表
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.getTemplateList.html
     */
    public function getTemplateList()
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $api    = "https://api.weixin.qq.com/wxaapi/newtmpl/gettemplate?access_token={$this->access_token}";
        $result = Tools::httpGet($api);
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * @param $arg array
     * @return array|bool
     * @throws \Exception
     * 发送订阅消息
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.send.html
     */
    public function send($arg = array())
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        if (!isset($arg['touser']) || !$arg['touser']) {
            throw new \Exception('touser字段缺失，请填写接收者（用户）的 openid');
        }
        if (!isset($arg['template_id']) || !$arg['template_id']) {
            throw new \Exception('template_id字段缺失，请填写所需下发的订阅消息的id');
        }
        //todo 解耦
        if (isset($arg['platform'])) {
            if ($arg['platform'] == 'weapp') {
                $api = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$this->access_token}";
            } elseif ($arg['platform'] == 'wechat') {
                $api = "https://api.weixin.qq.com/cgi-bin/message/subscribe/bizsend?access_token={$this->access_token}";
            }
            unset($arg['platform']);
        } else {
            $api = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$this->access_token}";
        }

        \Yii::error($api);
        \Yii::error(Tools::json_encode($arg));
        $res = Tools::httpPost($api, Tools::json_encode($arg));
        \Yii::error($res);
        $res = json_decode($res, true);
        switch ($res['errcode']) {
            case 0:
                return true;
            case 40003:
                throw new \Exception('用户为空或者不正确');
                break;
            case 40037:
                throw new \Exception('订阅模板id不正确');
                break;
            case 43101:
                throw new \Exception('用户拒绝接受消息，如果用户之前曾经订阅过，则表示用户取消了订阅关系');
                break;
            case 47003:
                throw new \Exception('模板参数不准确，可能为空或者不满足规则，errmsg会提示具体是哪个字段出错' . $res['errmsg']);
                break;
            case 41030:
                throw new \Exception('跳转路径不正确，需要保证在线上版本小程序中存在');
                break;
            default:
                throw new \Exception($res['errmsg']);
        }
    }

    /**
     * @param $args array
     * @return bool
     * @throws \Exception
     * 获取urlScheme
     * https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/url-scheme/urlscheme.generate.html
     */
    public function getScheme($args = array())
    {
        if (!$this->access_token && !$this->getAccessToken()) {
            return false;
        }
        $api    = "https://api.weixin.qq.com/wxa/generatescheme?access_token={$this->access_token}";
        $result = Tools::httpPost($api, Tools::json_encode($args));
        if ($result) {
            $json = json_decode($result, true);
            if (empty($json) || !empty($json['errcode'])) {
                $this->errCode = isset($json['errcode']) ? $json['errcode'] : '505';
                $this->errMsg  = isset($json['errmsg']) ? $json['errmsg'] : '无法解析接口返回内容！';
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }
}
