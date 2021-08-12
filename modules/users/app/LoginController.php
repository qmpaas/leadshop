<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/27
 * Time: 9:41
 */

namespace users\app;

use framework\common\BasicController;
use framework\common\GenerateIdentify;
use sizeg\jwt\Jwt;
use users\models\LoginUserInfo;
use users\models\Oauth;
use users\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use \framework\common\TokenHttpException;

/**
 * Class LoginController
 * @package users\app
 */
abstract class LoginController extends BasicController
{
    use GenerateIdentify;

    public $modelClass = 'users\models\Oauth';

    /**
     * @return LoginUserInfo
     */
    abstract protected function getUserInfo();

    public function actionCreateorupdate()
    {
        $register = false;
        $t        = \Yii::$app->db->beginTransaction();
        $userInfo = $this->getUserInfo();
        $user     = User::find()->alias('u')->joinWith(['oauth as o'])->where([
            'u.AppID'      => \Yii::$app->params['AppID'],
            'u.is_deleted' => 0,
            'o.oauthID'    => $userInfo->openId,
        ])->one();
        if (!$user) {
            $register           = true;
            $user               = new User();
            $user->mobile       = null;
            $user->created_time = time();
            $user->updated_time = time();
        }
        if (!$user->oauth) {
            $oauth = new Oauth();
        } else {
            $oauth = $user->oauth;
        }
        $user->nickname = $userInfo->nickname;
        $user->avatar   = $userInfo->avatar;
        $user->gender   = $userInfo->gender;
        $user->AppID    = \Yii::$app->params['AppID'];
        if (!$user->save()) {
            Error($user->getFirstErrors());
        }
        $oauth->oauthID = $userInfo->openId;
        $oauth->UID     = $user->id;
        $oauth->unionID = $userInfo->unionId ?? '';
        $oauth->type    = \Yii::$app->params['AppType'];
        $oauth->format  = to_json($userInfo);
        if (!$oauth->save()) {
            Error($oauth->getFirstErrors());
        }
        $t->commit();
        $res             = ArrayHelper::toArray($user);
        $res['token']    = $this->getToken($user->id);
        $res['register'] = ['coupon_list' => []];
        if ($register) {
            //先登录用户
            Yii::$app->user->login($user);
            $this->module->event->param = $user;
            $this->module->trigger('user_register');
            $cacheKey   = 'user_register_send_' . Yii::$app->user->id . '_' . Yii::$app->params['AppID'];
            $couponList = Yii::$app->cache->get($cacheKey);
            if ($couponList && count($couponList) > 0) {
                $res['register']['coupon_list'] = $couponList;
                \Yii::$app->cache->delete($cacheKey);
            }
        }
        return $res;
    }

    /**
     * 重置
     * @return [type] [description]
     */
    public function actionReset()
    {
        //调用模型
        $model    = new $this->modelClass();
        $postData = Yii::$app->request->post();
        $token    = $postData['token'] ? $postData['token'] : "";
        $token    = Yii::$app->jwt->getParser()->parse((string) $token);
        $data     = Yii::$app->jwt->getValidationData();
        $AppID    = Yii::$app->params['AppID'] ? Yii::$app->params['AppID'] : '';
        $host     = Yii::$app->request->hostInfo;
        $origin   = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $data->setIssuer($host);
        $data->setAudience($origin);
        $data->setId($AppID);
        $data->setCurrentTime(time());
        if ($token->validate($data)) {
            $id = $token->getClaim('id');
            if ($id) {
                $data          = $model::findOne($id)->toArray();
                $data['token'] = (string) $token;
                return $data;
            } else {
                return null;
            }
        } else {
            if ($token->getClaim('jti') !== $AppID) {
                throw new TokenHttpException('Leadshop应用ID验证错误', 419);
            } else {
                $data->setCurrentTime(time() - 30);
                if ($token->validate($data)) {
                    $id = $token->getClaim('id');
                    if ($id) {
                        $data          = $model::findOne($id)->toArray();
                        $data['token'] = (string) $this->getToken($id);
                        return $data;
                    } else {
                        return null;
                    }
                } else {
                    throw new TokenHttpException('Token validation timeout', 419);
                }
            }
        }
    }

    protected function getAppId()
    {
        if (!isset(\Yii::$app->params['AppType']) || empty(\Yii::$app->params['AppType'])) {
            Error('未知来源');
        }
        //来源
        $appType = \Yii::$app->params['AppType'];
        if (!isset(\Yii::$app->params['apply'][$appType]) || empty(\Yii::$app->params['apply'][$appType])) {
            Error('未知渠道');
        }
        $apply = \Yii::$app->params['apply'][$appType];
        if (!isset($apply['AppID']) || empty($apply['AppID'])) {
            Error('未知AppID');
        }
        return $apply['AppID'];
    }

    /**
     * 获取Token信息
     * 超时时间:1036800
     * @param  string $id [description]
     * @return [type]      [description]
     */
    public function getToken($id = '')
    {
        /** @var Jwt $jwt */
        $jwt      = Yii::$app->jwt;
        $signer   = $jwt->getSigner('HS256');
        $identify = $this->getIdentify();
        $key      = $jwt->getKey($identify);
        $time     = time();
        $host     = Yii::$app->request->hostInfo;
        $origin   = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        // Adoption for lcobucci/jwt ^4.0 version
        $token = $jwt->getBuilder()
            ->issuedBy($host) // Configures the issuer (iss claim)
            ->permittedFor($origin) // Configures the audience (aud claim)
            ->identifiedBy(Yii::$app->params['AppID'] ? Yii::$app->params['AppID'] : '', true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->expiresAt($time + 1036800) // Configures the expiration time of the token (exp claim)
            ->withClaim('id', $id) // Configures a new claim, called "id"
            ->getToken($signer, $key); // Retrieves the generated token
        return (string) $token;
    }

}
