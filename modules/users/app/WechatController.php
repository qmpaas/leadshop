<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/25
 * Time: 15:36
 */

namespace users\app;

use users\models\LoginUserInfo;

class WechatController extends LoginController
{
    public $modelClass = 'users\models\Oauth';

    /**
     * 搜索数据
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function actionSearch($value = '')
    {
        $post       = \Yii::$app->request->post();
        $modelClass = $this->modelClass;
        $where      = array(
            'is_deleted' => 0,
            'type'       => $post['type'],
            'oauthID'    => $post['oauthID'],
            'unionID'    => $post['unionID'],
        );
        //查询获取当前菜单的所有子菜单
        return $modelClass::find()->where($where)->one();
    }

    /**
     * 获取前端跳转的url
     */
    public function actionUrl()
    {
        $get = \Yii::$app->request->get();
        if (!$get['url']) {
            Error('缺少url参数');
        }
        if (!$get['scope']) {
            Error('缺少scope参数');
        }
        $uri = 'https://open.weixin.qq.com/connect/oauth2/authorize';
        $args = [
            'appid' => $this->getAppId(),
            'redirect_uri' => urlencode($get['url']),
            'response_type' => 'code',
            'scope' => $get['scope'],
            'state' => \Yii::$app->params['AppID'],
        ];
        $params = '';
        foreach ($args as $key => $item) {
            $params .= $key . '=' . $item . '&';
        }
        $url = $uri . '?' . rtrim($params, '&') . '#wechat_redirect';
        return [
            'appid' => $this->getAppId(),
            'state' => \Yii::$app->params['AppID'],
            'url' => $url
        ];
    }

    public function getUserInfo()
    {
        $res =  $this->actionWechat();
        $userInfo = new LoginUserInfo();
        $userInfo->nickname = $res['nickname'];
        $userInfo->avatar = $res['headimgurl'];
        $userInfo->gender = $res['sex'];
        $userInfo->openId = $res['openid'];
        $userInfo->unionId = $res['unionid'] ?? '';
        return $userInfo;
    }

    public function actionWechat()
    {
        $post = \Yii::$app->request->post();
        $mpConfig = \Yii::$app->params['apply'][\Yii::$app->params['AppType']] ?? null;
        if (!$mpConfig || !$mpConfig['AppID'] || !$mpConfig['AppSecret']) {
            throw new \Exception('渠道参数不完整。');
        }
        $mp = &load_wechat('Oauth', [
            'appid'          => $mpConfig['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret'      => $mpConfig['AppSecret'], // 填写高级调用功能的密钥
        ]);
        $access = $mp->getOauthAccessToken($post['code']);
        if ($access === false) {
            Error($mp->errMsg);
        }
        $userInfo = $mp->getOauthUserInfo($access['access_token'], $access['openid']);
        if ($userInfo === false) {
            Error($mp->errMsg);
        }
        return $userInfo;
    }
}
