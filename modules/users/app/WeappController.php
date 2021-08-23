<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace users\app;

use users\models\LoginUserInfo;
use Yii;

/**
 * 后台用户管理器
 */
class WeappController extends LoginController
{
    public $modelClass = 'users\models\Oauth';

    public function actionUpdate()
    {
        return $this->action->id;
    }

    /**
     * 搜索数据
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function actionSearch($value = '')
    {
        $post       = Yii::$app->request->post();
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

    public function getUserInfo()
    {
        $res                = $this->actionWeapp();
        $userInfo           = new LoginUserInfo();
        $userInfo->unionId  = $res['userinfo']['unionId'] ?? '';
        $userInfo->openId   = $res['userinfo']['openId'] ?? '';
        $userInfo->gender   = $res['userinfo']['gender'];
        $userInfo->avatar   = $res['userinfo']['avatarUrl'];
        $userInfo->nickname = $res['userinfo']['nickName'];
        return $userInfo;
    }

    /**
     * 微信登录查询
     * @return [type] [description]
     */
    public function actionWeapp()
    {
        //SDK实例对象
        $oauth = &load_wechat('Applet', [
            'appid'     => Yii::$app->params['apply']['weapp']['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
            'appsecret' => Yii::$app->params['apply']['weapp']['AppSecret'], // 填写高级调用功能的密钥
        ]);
        $post = Yii::$app->request->post();
        // 执行接口操作
        $result = $oauth->getUserInfo($post['code'], $post['encryptedData'], $post['iv']);
        // 数据查询接口
        return $result;
    }

}
