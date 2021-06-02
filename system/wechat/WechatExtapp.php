<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2020/12/28
 * Time: 10:15
 */

namespace framework\wechat;

use framework\wechat\Lib\Tools;
use framework\wechat\WechatService;

class WechatExtapp extends WechatService
{
    // 第三方微信接口基础
    const THIRD_URL_PREFIX = 'https://api.weixin.qq.com/wxa';

    // 设置小程序服务器地址
    const MODIFY_DOMAIN = '/modify_domain?';
    // 设置小程序业务域名
    const SET_WEBVIEW_DOMAIN = '/setwebviewdomain?';
    // 成员管理，绑定小程序体验者
    const BIND_TESTER = '/bind_tester?';
    // 成员管理，解绑定小程序体验者
    const UNBIND_TESTER = '/unbind_tester?';
    // 成员管理，获取小程序体验者列表
    const MEMBER_AUTH = '/memberauth?';
    // 获取代码模板列表
    const GET_TEMPLATE_LIST = '/gettemplatelist?';
    // 删除指定代码模板
    const DELETE_TEMPLATE = '/deletetemplate?';
    // 获取代码草稿列表
    const GET_TEMPLATE_DRAFT_LIST = '/gettemplatedraftlist?';
    // 将草稿添加到代码模板库
    const ADD_TO_TEMPLATE = '/addtotemplate?';
    // 为授权的小程序帐号上传小程序代码
    const COMMIT = '/commit?';
    // 获取体验小程序的体验二维码
    const GET_QRCODE = '/get_qrcode?';
    // 提交审核
    const SUBMIT_AUDIT = '/submit_audit?';
    // 小程序审核撤回
    const UNDOCODEAUDIT = '/undocodeaudit?';
    // 查询指定版本的审核状态
    const GET_AUDITSTATUS = '/get_auditstatus?';
    // 查询最新一次提交的审核状态
    const GET_LATEST_AUDITSTATUS = '/get_latest_auditstatus?';
    // 发布已通过审核的小程序
    const RELEASE = '/release?';
    // 获取授权小程序帐号的可选类目
    const GET_CATEGORY = '/get_category?';
    // 获取小程序的第三方提交代码的页面配置
    const GET_PAGE = '/get_page?';
    // 快速创建小程序
    const FAST_REGISTER_WEAPP = '/fastregisterweapp?';

    /**@var string $authorizer_appid 授权给第三方平台的小程序access_token**/
    protected $authorizer_access_token;

    public function setToken($token)
    {
        $this->authorizer_access_token = $token;
    }

    /**
     * 设置小程序服务器地址
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function setServerDomain($data)
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::MODIFY_DOMAIN . "access_token={$this->authorizer_access_token}", Tools::json_encode($data));
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("设置小程序服务器地址失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 设置小程序业务域名
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function setBusinessDomain($data)
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::SET_WEBVIEW_DOMAIN . "access_token={$this->authorizer_access_token}", Tools::json_encode($data));
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("设置小程序业务域名失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 成员管理，绑定小程序体验者
     * @param $wechatid
     * @return bool
     * @throws \Exception
     */
    public function bindMember($wechatid)
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::BIND_TESTER . "access_token={$this->authorizer_access_token}", Tools::json_encode(['wechatid' => $wechatid]));
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("绑定小程序体验者操作失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 成员管理，解绑定小程序体验者
     * @params string $wechatid : 体验者的微信号
     * */
    public function unBindMember($wechatid)
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::UNBIND_TESTER . "access_token={$this->authorizer_access_token}", Tools::json_encode(['wechatid' => $wechatid]));
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("解绑定小程序体验者操作失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 成员管理，获取小程序体验者列表
     * */
    public function listMember()
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::MEMBER_AUTH . "access_token={$this->authorizer_access_token}", Tools::json_encode(['action' => 'get_experiencer']));
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret->members;
        } else {
            $this->errorLog("获取小程序体验者列表操作失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 获取代码模板列表
     * @return bool|mixed
     * @throws \Exception
     */
    public function templateList()
    {
        if (!$this->component_access_token && !$this->getComponentAccessToken()) {
            return false;
        }
        $result = Tools::httpGet(self::THIRD_URL_PREFIX . self::GET_TEMPLATE_LIST . "access_token={$this->component_access_token}");
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("获取代码模板列表失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 删除指定代码模板
     * @param $template_id
     * @return bool
     * @throws \Exception
     */
    public function deletetemplate($template_id)
    {
        if (!$this->component_access_token && !$this->getComponentAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::DELETE_TEMPLATE . "access_token={$this->component_access_token}", Tools::json_encode(['template_id' => $template_id]));
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("获取代码模板列表失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 获取代码草稿列表
     * @return bool|mixed
     * @throws \Exception
     */
    public function templatedraftlist()
    {
        if (!$this->component_access_token && !$this->getComponentAccessToken()) {
            return false;
        }
        $result = Tools::httpGet(self::THIRD_URL_PREFIX . self::GET_TEMPLATE_DRAFT_LIST . "access_token={$this->component_access_token}");
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("获取代码草稿列表失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 将草稿添加到代码模板库
     * @param $draft_id
     * @return bool
     * @throws \Exception
     */
    public function addtotemplate($draft_id)
    {
        if (!$this->component_access_token && !$this->getComponentAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::ADD_TO_TEMPLATE . "access_token={$this->component_access_token}", Tools::json_encode(['draft_id' => $draft_id]));
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("将草稿添加到代码模板库失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * todo 待定
     * 为授权的小程序帐号上传小程序代码
     * @params int $template_id : 模板ID
     * @params json $ext_json : 小程序配置文件，json格式
     * @params string $user_version : 代码版本号
     * @params string $user_desc : 代码描述
     * */
    public function uploadCode($template_id, $is_plugin = 0, $user_version = 'v1.0.0', $user_desc = "小程序模板库")
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $live = [
            "live-player-plugin" => [
                "version"  => "1.2.5",
                "provider" => "wx2b03c6e691cd7370",
            ],
        ];
        $plugin = $is_plugin ? $live : (object) [];
        //todo  AppID 写死了
        $ext    = [
            'extEnable' => true,
            'extAppid'  => $this->authorizer_appid,
            'ext'       => [
                'AppID' => 1,
            ],
            'plugins'   => $plugin,
        ];
        $ext_json = json_encode($ext);
        $url      = "https://api.weixin.qq.com/wxa/commit?access_token=" . $this->authorizer_access_token;
        $data     = json_encode([
            'template_id'  => $template_id,
            'ext_json'     => $ext_json,
            'user_version' => $user_version,
            'user_desc'    => $user_desc,
        ], JSON_UNESCAPED_UNICODE);
        $result = Tools::httpPost($url, $data);
        $ret = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("为授权的小程序帐号上传小程序代码操作失败,appid:" . $this->authorizer_appid . $ret->errmsg . $ret->errcode, $ret);
            return false;
        }
    }

    /**
     * 获取体验小程序的体验二维码
     * @params string $path :   指定体验版二维码跳转到某个具体页面
     * */
    public function getExpVersion($path = '')
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        if ($path) {
            $url = self::THIRD_URL_PREFIX . self::GET_QRCODE . "access_token=" . $this->authorizer_access_token . "&path=" . urlencode(
                $path
            );
        } else {
            $url = self::THIRD_URL_PREFIX . self::GET_QRCODE . "access_token=" . $this->authorizer_access_token;
        }
        $result = Tools::httpGet($url);
        $ret    = json_decode($result);
        if (isset($ret->errcode)) {
            $this->errorLog("获取体验小程序的体验二维码操作失败,appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        } else {
            return $result;
        }
    }

    /**
     * 提交审核
     * @params string $tag : 小程序标签，多个标签以空格分开
     * @params strint $title : 小程序页面标题，长度不超过32
     * */
    public function submitReview($tag = "商城", $title = "小程序开发")
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $first_class  = '';
        $second_class = '';
        $first_id     = 0;
        $second_id    = 0;
        $address      = "pages/index/index";
        $category     = $this->getCategory();
        if (!empty($category)) {
            $first_class  = $category[0]->first_class ? $category[0]->first_class : '';
            $second_class = $category[0]->second_class ? $category[0]->second_class : '';
            $first_id     = $category[0]->first_id ? $category[0]->first_id : 0;
            $second_id    = $category[0]->second_id ? $category[0]->second_id : 0;
        }
        $getpage = $this->getPage();
        if (!empty($getpage) && isset($getpage[0])) {
            $address = $getpage[0];
        }
        $url  = self::THIRD_URL_PREFIX . self::SUBMIT_AUDIT . "access_token=" . $this->authorizer_access_token;
        $data = '{
                "item_list":[{
                    "address":"' . $address . '",
                    "tag":"' . $tag . '",
                    "title":"' . $title . '",
                    "first_class":"' . $first_class . '",
                    "second_class":"' . $second_class . '",
                    "first_id":"' . $first_id . '",
                    "second_id":"' . $second_id . '"
                }]
            }';
        $result = Tools::httpPost($url, $data);
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret->auditid;
        } else {
            $this->errorLog("小程序提交审核操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 小程序审核撤回
     * 单个帐号每天审核撤回次数最多不超过1次，一个月不超过10次。
     * */
    public function unDoCodeAudit()
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpGet(self::THIRD_URL_PREFIX . self::UNDOCODEAUDIT . "access_token={$this->authorizer_access_token}");
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("小程序审核撤回操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 查询指定版本的审核状态
     * @params string $auditid : 提交审核时获得的审核id
     * */
    public function getAuditStatus($auditid)
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::GET_AUDITSTATUS . "access_token={$this->authorizer_access_token}", Tools::json_encode(['auditid' => $auditid]));
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("查询指定版本的审核状态操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 查询最新一次提交的审核状态
     * */
    public function getLastAudit()
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpGet(self::THIRD_URL_PREFIX . self::GET_LATEST_AUDITSTATUS . "access_token={$this->authorizer_access_token}");
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("查询最新一次提交的审核状态操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 发布已通过审核的小程序
     * */
    public function release()
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpPost(self::THIRD_URL_PREFIX . self::RELEASE . "access_token={$this->authorizer_access_token}", '{}');
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("发布已通过审核的小程序操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return $ret->errcode;
        }
    }

    /**
     * 获取授权小程序帐号的可选类目
     * */
    private function getCategory()
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpGet(self::THIRD_URL_PREFIX . self::GET_CATEGORY . "access_token={$this->authorizer_access_token}");
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret->category_list;
        } else {
            $this->errorLog("获取授权小程序帐号的可选类目操作失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 获取小程序的第三方提交代码的页面配置
     * */
    private function getPage()
    {
        if (!$this->authorizer_access_token && !$this->getAccessToken()) {
            return false;
        }
        $result = Tools::httpGet(self::THIRD_URL_PREFIX . self::GET_PAGE . "access_token={$this->authorizer_access_token}");
        $ret    = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret->page_list;
        } else {
            $this->errorLog("获取小程序的第三方提交代码的页面配置失败，appid:" . $this->authorizer_appid . $ret->errmsg, $ret);
            return false;
        }
    }

    /**
     * 创建小程序
     * @param $name
     * @param $code
     * @param $code_type
     * @param $legal_persona_wechat
     * @param $legal_persona_name
     * @param $component_phone
     * @return bool
     */
    public function fastCreate($name, $code, $code_type, $legal_persona_wechat, $legal_persona_name, $component_phone)
    {
        $result = Tools::httpPost(self::URL_PREFIX . self::FAST_REGISTER_WEAPP . "access_token={$this->component_access_token}", json_encode([
            'name'                 => $name,
            'code'                 => $code,
            'code_type'            => $code_type,
            'legal_persona_wechat' => $legal_persona_wechat,
            'legal_persona_name'   => $legal_persona_name,
            'component_phone'      => $component_phone,
        ], true));
        $ret = json_decode($result);
        if ($ret->errcode == 0) {
            return true;
        } else {
            $this->errorLog("创建小程序失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * 查询创建任务状态
     * @param $name
     * @param $legal_persona_wechat
     * @param $legal_persona_name
     * @return mixed
     * @throws \Exception
     */
    public function getFastCreate($name, $legal_persona_wechat, $legal_persona_name)
    {
        $result = Tools::httpPost(self::URL_PREFIX . self::FAST_REGISTER_WEAPP . "access_token={$this->component_access_token}", json_encode([
            'name'                 => $name,
            'legal_persona_wechat' => $legal_persona_wechat,
            'legal_persona_name'   => $legal_persona_name,
        ], JSON_UNESCAPED_UNICODE));
        $ret = json_decode($result);
        if ($ret->errcode == 0) {
            return $ret;
        } else {
            $this->errorLog("查询创建任务状态失败," . $ret->errmsg . $ret->errcode, $ret);
        }
    }

    /**
     * @param $msg
     * @param string $ret
     * @throws \Exception
     */
    private function errorLog($msg, $ret = '')
    {
        $error = [
            '-1'    => '系统繁忙',
            '80082' => '没有权限使用该插件',
            '85009' => '已经有正在审核的版本',
            '85012' => '无效的审核 id',
            '85019' => '无效的自定义配置',
            '85013' => '没有审核版本',
            '87013' => '撤回次数达到上限（每天一次，每个月 10 次）',
            '85052' => '该版本小程序已经发布',
            '85017' => '请联系平台管理员，确认小程序已经添加了域名或该域名是否没有在第三方平台添加',
            '89021' => '请求保存的域名不是第三方平台中已设置的小程序业务域名或子域名',
            '89231' => '个人小程序不支持调用 setwebviewdomain 接口',
            '86004' => '无效微信号',
            '61070' => '法人姓名与微信号不一致',
        ];
        \Yii::error('==========error log=========');
        \Yii::error($msg);
        \Yii::error($ret);
        if (isset($ret->errcode) && isset($error[$ret->errcode])) {
            $msg = $error[$ret->errcode];
        }
        throw new \Exception($msg);
    }
}
