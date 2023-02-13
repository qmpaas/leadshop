<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace users\app;

use app\components\ComPromoter;
use coupon\models\Coupon;
use framework\common\BasicController;
use users\models\User;
use Yii;
use \framework\common\TokenHttpException;

/**
 * 后台用户管理器
 */
class IndexController extends BasicController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'visit': //用户访问
                return $this->visit();
                break;
            case 'info': //用户详情
                $UID = Yii::$app->user->identity->id ?? null;
                if (!$UID) {
                    throw new TokenHttpException('Token validation timeout', 419);
                }
                $user                   = User::findOne($UID);
                $res                    = $user->toArray();
                $res['promoter_status'] = 0;
                $center_show            = StoreSetting('promoter_setting', 'center_show');
                $res['promoter_show']   = $center_show === 2 ? 2 : 0;
                $res['recruiting_show'] = 0;
                $promoter               = $user->promoter;
                if ($promoter) {
                    $res['promoter_status'] = $promoter->status;
                    if ($promoter->repel_time || $promoter->status === 2) {
                        $res['promoter_show'] = 1;
                    }
                    if ($promoter->status < 0 || $promoter->status === 1 || $promoter->status === 3) {
                        $res['recruiting_show'] = 1;
                    }
                }
                return $res;
                break;
            case 'simple_info':
                $UID = Yii::$app->request->get('UID', false);
                if (!$UID) {
                    Error('缺少ID');
                }
                return User::find()->where(['id' => $UID])->select('id,nickname,avatar')->one();
                break;
            default:
                Error('未定义操作');
                break;
        }
    }

    public function visit()
    {
        $AppID = Yii::$app->params['AppID'];
        $UID   = Yii::$app->user->identity->id ?? null;
        $res   = true;
        if ($UID) {
            $this->module->event->visit_info = ['UID' => $UID, 'AppID' => $AppID];
            $this->module->trigger('visit');

            $this->module->event->user_statistical = ['UID' => $UID, 'last_visit_time' => time()];
            $this->module->trigger('user_statistical');

            $setting = StoreSetting('promoter_setting');
            //无需审核,无需申请,无条件时可自动成为分销商
            if ($setting && $setting['need_check'] === 0 && $setting['need_apply'] === 0 && $setting['conditions']['type'] === 1) {
                $data = M('promoter', 'Promoter')::findOne(['UID' => $UID]);
                if (empty($data) || ($data->status !== 1 && $data->status !== 2)) {
                    $time = time();
                    if (empty($data)) {
                        $model               = M('promoter', 'Promoter', true);
                        $model->UID          = $UID;
                        $model->created_time = $time;
                        $data                = $model;
                    }
                    $data->status     = 2;
                    $data->invite_id  = 0;
                    $data->apply_time = $time;
                    $data->join_time  = $time;
                    if ($data->save()) {
                        $ComPromoter = new ComPromoter();
                        $ComPromoter->setLevel([$UID], 3);
                        $res = 'promoter';
                    } else {
                        Yii::error('自动成分销商失败');
                    }
                }

            }
        }

        return $res;
    }

    /**
     * 用户注册
     * @return [type] [description]
     */
    public function actionRegister()
    {
        //调用模型
        $model    = new User;
        $postData = Yii::$app->request->post();
        //加载数据
        $model->load($postData);
        $model->attributes = $postData;
        //密码Hash加密处理
        $model->password = MD5($postData['password']);
        //密码Hash加密处理
        $model->password_compare = MD5($postData['password_compare']);
        //执行数据保存
        $result = $model->save();
        if ($result) {
            return $model->attributes['id'];
        } else {
            //统一报错处理
            $errors = $model->getFirstErrors();
            Error(is_array($errors) ? array_shift($errors) : $errors);
        }
    }

    /**
     * 后台登录
     * @return [type] [description]
     */
    public function actionReset()
    {
        $post = Yii::$app->request->post();
        $data = User::find()->where(['mobile' => $post['mobile'], 'password' => $post['password']])->one();
        if ($data) {
            $token         = $this->getToken($data['id']);
            $data['token'] = $token;
            return $data;
        } else {
            return null;
        }
    }

    /**
     * 用户修改
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'setting': //用户设置
                return $this->setting();
                break;
            case 'bindMobile': //绑定手机
                return $this->bindMobile();
                break;
            case 'removeMobile': //绑定手机
                return $this->removeMobile();
                break;
            default:
                Error('未定义操作');
                break;
        }
    }

    /**
     * 设置用户信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function setting()
    {
        $UID   = Yii::$app->user->identity->id;
        $post  = Yii::$app->request->post();
        $model = M('users', 'User')::findOne($UID);
        $model->setScenario('setting');
        $model->setAttributes($post);
        $model->is_edit = 1;
        $this->plugins("task", ["score", ["perfect", 1, $UID]]);
        return $model->save();
    }

    /**
     * 解绑手机
     * @return [type] [description]
     */
    public function removeMobile()
    {
        $UID = Yii::$app->user->identity->id;

        $model = M('users', 'User')::findOne($UID);
        if (empty($model)) {
            Error('用户不存在');
        }
        $model->mobile = null;
        return $model->save();
    }

    /**
     * 绑定手机
     * @return [type] [description]
     */
    public function bindMobile()
    {
        $UID = Yii::$app->user->identity->id;

        $model = M('users', 'User')::findOne($UID);
        if (empty($model)) {
            Error('用户不存在');
        }

        $mobile = $this->getMobile();

        if (!$mobile) {
            Error('手机号获取失败');
        }

        $check = M('users', 'User')::find()->where(['and', ['mobile' => $mobile], ['<>', 'id', $UID]])->with(['oauth' => function ($query) {
            $query->select('UID,type');
        }])->asArray()->all();
        if (!empty($check)) {
            foreach ($check as $value) {
                if ($value['oauth']['type'] === $model->oauth->type) {
                    Error('手机号已存在');
                }
            }
        }

        $data = ['mobile' => $mobile];

        $model->setScenario('setting');
        $model->setAttributes($data);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                return $model;
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    public function getMobile()
    {
        $type = Yii::$app->request->get('type', false);
        $post = Yii::$app->request->post();
        switch ($type) {
            case 'code':
                $mobile     = $post['mobile'] ?? '';
                $code       = $post['code'] ?? '';
                $time       = time() - 600;
                $check_code = M('sms', 'SmsLog')::find()->where(['and', ['mobile' => $mobile, 'type' => 1], ['>=', 'created_time', $time]])->orderBy(['created_time' => SORT_DESC])->one();
                if ($check_code) {
                    if ($check_code->code == $code) {
                        return $mobile;
                    } else {
                        Error('验证码错误');
                    }

                } else {
                    Error('验证码失效');
                }
                break;

            default:
                //SDK实例对象
                $mp = &load_wechat('Applet', [
                    'appid'     => Yii::$app->params['apply']['weapp']['AppID'], // 填写高级调用功能的app id, 请在微信开发模式后台查询
                    'appsecret' => Yii::$app->params['apply']['weapp']['AppSecret'], // 填写高级调用功能的密钥
                ]);

                // 执行接口操作
                $mobile = $mp->getMobile($post['code'], $post['encryptedData'], $post['iv']);
                // 数据查询接口
                if (!isset($mobile['phoneNumber'])) {
                    return false;
                }
                return $mobile['phoneNumber'];
                break;
        }

    }

    public static function statistical($event)
    {
        $data  = $event->user_statistical;
        $check = M('users', 'UserStatistical')::find()->where(['UID' => $data['UID']])->one();
        if ($check) {
            if (isset($data['buy_number'])) {
                $data['buy_number'] += $check->buy_number;
            }
            if (isset($data['buy_amount'])) {
                $data['buy_amount'] += $check->buy_amount;
            }
            $check->setScenario('save');
            $check->setAttributes($data);
            $check->save();
        } else {
            $model = M('users', 'UserStatistical', true);
            $model->setScenario('save');
            $model->setAttributes($data);
            $model->save();
        }

    }

    /**
     * 获取Token
     * @return [type] [description]
     */
    public function actionToken()
    {
        $post = Yii::$app->request->post();
        $UID  = $post['UID'];
        return $this->getToken($UID);
    }

    /**
     * 获取Token信息
     * @param  string $id [description]
     * @return [type]      [description]
     */
    public function getToken($id = '')
    {
        /** @var Jwt $jwt */
        $jwt    = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key    = $jwt->getKey();
        $time   = time();
        // Adoption for lcobucci/jwt ^4.0 version
        $token = $jwt->getBuilder()
            ->issuedBy('http://www.heshop.com') // Configures the issuer (iss claim)
            ->permittedFor('http://mail.heshop.com') // Configures the audience (aud claim)
            ->identifiedBy('jub4q3yrgto', true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
            ->withClaim('id', $id) // Configures a new claim, called "id"
            ->getToken($signer, $key); // Retrieves the generated token
        return (string) $token;
    }

    public static function register($event)
    {
        Yii::info('触发用户注册事件');
        $coupons = Coupon::find()->where([
            'AND',
            [
                'AppID'      => Yii::$app->params['AppID'],
                'status'     => 1,
                'is_deleted' => 0,
            ],
            ['>', 'over_num', 0],
            ['>', 'register_limit', 0],
            ['>', 'end_time', time()],
        ])->all();
        $success = [];
        /**@var Coupon $coupon*/
        foreach ($coupons as $coupon) {
            try {
                $result  = Coupon::obtain($coupon, [Yii::$app->user], 4, $coupon->register_limit, 2);
                $success = array_merge($success, $result);
            } catch (\Exception $exception) {
                Yii::error('==============user register error begin============');
                Yii::error($exception->getMessage());
                Yii::error($exception);
                Yii::error('==============user register error end============');
            }
        }
        Yii::$app->cache->set('user_register_send_' . Yii::$app->user->id . '_' . Yii::$app->params['AppID'], $success);
        return $success;
    }
}
