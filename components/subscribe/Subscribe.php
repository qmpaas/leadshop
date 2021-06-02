<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 */

namespace app\components\subscribe;

use subscribe\models\SubscribeTemplate;
use users\models\Oauth;
use yii\base\Component;

/**
 * 订阅消息组件
 * Class Subscribe
 * @package app\components\subscribe
 */
class Subscribe extends Component
{
    public $sender;
    public $message;
    private $user;
    private $page = '';

    /**
     * 设置接收用户
     * @param $uid
     * @return $this
     */
    public function setUser($uid)
    {
        $this->user = Oauth::findOne(['UID' => $uid, 'is_deleted' => 0]);
        return $this;
    }

    /**
     * @return Oauth
     */
    public function getUser()
    {
        if (empty($this->user) || !($this->user instanceof Oauth)) {
            Error('发送对象不正确');
        }
        return $this->user;
    }

    /**
     * 设置小程序页面
     * @param $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    public function getPage()
    {
        if ($this->getUser()->type == 'wechat') {
            return \Yii::$app->request->hostInfo . '/index.php?r=wechat#/' . $this->page;
        }
        return $this->page;
    }

    /**
     * 订阅消息发送
     * @param BaseSubscribeMessage $message
     */
    public function send(BaseSubscribeMessage $message)
    {
        try {
            $data = $message->msg();
            \Yii::info('======订阅消息发送前======');
            \Yii::info($data);
            foreach ($data as $key => &$item) {
                $item['value'] = $this->checkData($key, $item['value']);
            }
            \Yii::info('======订阅消息发送后======');
            \Yii::info($data);
            unset($item);
            $this->sender = $this->getSender();
            $arg['touser'] = $this->getUser()->oauthID;
            $arg['template_id'] = $this->getTemplateId($message->tplName());
            $arg['page'] = $this->getPage();
            $arg['data'] = $data;
            $arg['platform'] = $this->user->type;
            $this->sender->send($arg);
        } catch (\Exception $e) {
            \Yii::error('订阅消息发送失败');
            \Yii::error($arg ?? '');
            \Yii::error($message);
            \Yii::error($e->getMessage());
            \Yii::error($e);
        }
    }

    /**
     * 获取发送者对象
     * @return \Wehcat\WechatReceive
     * @throws \Exception
     */
    private function getSender()
    {
        $type = $this->user->type;
        $mpConfig = \Yii::$app->params['apply'][$type] ?? null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            throw new \Exception('渠道参数不完整。');
        }
        $weapp = &load_wechat('Subscribe', [
            'appid' => $mpConfig['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret' => $mpConfig['AppSecret'], // 填写高级调用功能的密钥
        ]);
        return $weapp;
    }

    /**
     * 获取订阅消息id
     * @param $tplName
     * @return string
     */
    private function getTemplateId($tplName)
    {
        \Yii::error(['tpl_name' => $tplName, 'platform' => $this->user->type, 'is_deleted' => 0]);
        $template = SubscribeTemplate::findOne(['tpl_name' => $tplName, 'platform' => $this->user->type, 'is_deleted' => 0]);
        if (!$template) {
            Error('模板尚未设置');
        }
        return $template->tpl_id;
    }

    /**
     * @param string $key 参数键值
     * @param string $value
     * @return string
     */
    public function checkData($key, $value)
    {
        if (preg_match('/^[a-z]+[_]?[a-z]+/i', $key, $arr)) {
            switch ($arr[0]) {
                case 'thing': // 20个以内字符 可汉字、数字、字母或符号组合
                    $value = mb_substr($value, 0, 20);
                    break;
                case 'number': // 32位以内数字 只能数字，可带小数
                    $value = mb_substr($value, 0, 32);
                    break;
                case 'letter': // 32位以内字母 只能字母
                    $value = mb_substr($value, 0, 32);
                    break;
                case 'symbol': // 5位以内符号 只能符号
                    $value = mb_substr($value, 0, 5);
                    break;
                case 'character_string': // 32位以内数字、字母或符号 可数字、字母或符号组合
                    $value = preg_replace('/[\x{4e00}-\x{9fff}]/u', '', $value);
                    $value = mb_substr($value, 0, 32);
                    break;
                case 'time': // 24小时制时间格式（支持+年月日） 例如：15:01，或：2019年10月1日 15:01
                    break;
                case 'date': // 年月日格式（支持+24小时制时间） 例如：2019年10月1日，或：2019年10月1日 15:01
                    break;
                case 'amount': // 1个币种符号+10位以内纯数字，可带小数，结尾可带“元” 可带小数
                    break;
                case 'phone_number': // 17位以内，数字、符号 电话号码，例：+86-0766-66888866
                    $value = mb_substr($value, 0, 17);
                    break;
                case 'car_number': // 8位以内，第一位与最后一位可为汉字，其余为字母或数字 车牌号码：粤A8Z888挂
                    $value = mb_substr($value, 0, 8);
                    break;
                case 'name': // 10个以内纯汉字或20个以内纯字母或符号 中文名10个汉字内；纯英文名20个字母内；中文和字母混合按中文名算，10个字内
                    $value = preg_replace('/[0-9]/u', '', $value);
                    $max = 20;
                    if (preg_match('/[\x{4e00}-\x{9fff}]/u', $value)) {
                        $max = 10;
                    }
                    $value = mb_substr($value, 0, $max);
                    break;
                case 'phrase': // 5个以内汉字 5个以内纯汉字，例如：配送中
                    $value = preg_replace('/[^\x{4e00}-\x{9fff}]/u', '', $value);
                    $value = mb_substr($value, 0, 5);
                    break;
            }
        }
        return $value;
    }
}