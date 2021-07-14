<?php

namespace sms\api;

use framework\common\BasicController;
use setting\models\Setting;

class IndexController extends BasicController
{
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        $smsConfig = $this->getSmsConfig();
        return [
            'detail'  => $smsConfig,
            'setting' => $this->getSetting(),
        ];
    }

    public function actionCreate()
    {
        $behavior = \Yii::$app->request->get('behavior', '');
        if ($behavior == 'test_sms') {
            $type    = \Yii::$app->request->post('type');
            $mobiles = \Yii::$app->request->post('mobile');
            if (!$type) {
                Error('请选择消息类型');
            }
            if (!$mobiles) {
                Error('请填写测试手机号');
            }
            if (!is_array($mobiles)) {
                $mobiles = [$mobiles];
            }
            $setting = IndexController::getSetting();
            if (!isset($setting[$type])) {
                Error('请选择消息类型');
            }

            $params = [];
            foreach ($setting[$type]['variable'] as $item) {
                if ($item['key'] == 'date' || $item['key'] == 'time') {
                    $params[$item['key']] = date('Y-m-d H:i:s', time());
                } else {
                    $params[$item['key']] = get_random(5);
                }
            }
            $this->module->event->sms = [
                'type'   => $type,
                'debug'  => true,
                'mobile' => $mobiles,
                'params' => $params,
            ];
            try {
                $this->module->trigger('send_sms');
                return true;
            } catch (\Exception $e) {
                Error($e->getMessage());
            }
        } else {
            $json  = \Yii::$app->request->post();
            $model = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'merchant_id' => 1, 'keyword' => 'sms_setting']);
            if (!$model) {
                $model              = new Setting();
                $model->AppID       = \Yii::$app->params['AppID'];
                $model->keyword     = 'sms_setting';
                $model->merchant_id = 1;
            }
            $model->content = json_encode($json, true);
            return $model->save();
        }
    }

    public function getSmsConfig()
    {
        $option = [];
        $config = M('setting', 'Setting')::find()->where(['keyword' => 'sms_setting', 'merchant_id' => 1, 'AppID' => \Yii::$app->params['AppID']])->select('content')->asArray()->one();
        if ($config) {
            $option = json_decode($config['content'], true);
        }
        return $this->check($option, $this->getDefault());
    }

    private function getDefault()
    {
        $setting = $this->getSetting();
        $result  = [
            'status'            => '0',
            'platform'          => 'aliyun', // 短信默认支持阿里云
            'mobile_list'       => [],
            'access_key_id'     => '',
            'access_key_secret' => '',
            'template_name'     => '',
        ];
        foreach ($setting as $index => $item) {
            $newItem = [
                'template_id' => '',
            ];
            foreach ($item['variable'] as $value) {
                $newItem[$value['key']] = '';
            }
            $result[$index] = $newItem;
        }
        return $result;
    }

    public static function getSetting()
    {
        return [
            'order_refund'         => [
                'title'    => '买家申请售后提醒',
                'content'  => '例如：模板内容：有买家申请售后，请登录商城后台查看。',
                'loading'  => false,
                'variable' => [],
                'key'      => 'business',
            ],
            'order_pay_business'   => [
                'title'    => '新订单提醒',
                'content'  => '例如：模板内容：商城来新订单了，${code}，请登录商城后台查看。',
                'loading'  => false,
                'variable' => [
                    [
                        'key'   => 'code',
                        'value' => '模板变量',
                        'desc'  => '例如：商城来新订单了，${code}，请登录商城后台查看。',
                    ],
                ],
                'key'      => 'business',
            ],
            'captcha'              => [
                'title'    => '绑定手机号',
                'content'  => '例如：模板内容：您的验证码为${code}，请勿告知他人。',
                'loading'  => false,
                'variable' => [
                    [
                        'key'   => 'code',
                        'value' => '模板变量',
                        'desc'  => '例如：模板内容：您的验证码为${code}，请勿告知他人。，则只需填写code',
                    ],
                ],
                'key'      => 'buyer',
            ],
            'order_pay'            => [
                'title'    => '付款成功提醒',
                'content'  => '例如：模板内容：亲爱的会员，您在${name}的订单提交成功。我们会尽快发货，记得关注我们的商城喔～感谢您的支持！',
                'loading'  => false,
                'variable' => [
                    [
                        'key'   => 'name',
                        'value' => '模板变量',
                        'desc'  => '例如：模板内容：亲爱的会员，您在${name}的订单提交成功。我们会尽快发货，记得关注我们的商城喔～感谢您的支持！则只需填写name',
                    ],
                ],
                'key'      => 'buyer',
            ],
            'order_send'           => [
                'title'    => '订单发货提醒',
                'content'  => '例如：模板内容：亲爱的用户，您的尾号为${code}的订单已经发出，请注意查收。',
                'loading'  => false,
                'variable' => [
                    [
                        'key'   => 'code',
                        'value' => '模板变量',
                        'desc'  => '例如：亲爱的用户，您的尾号为${code}的订单已经发出，请注意查收。则只需填写code',
                    ],
                ],
                'key'      => 'buyer',
            ],
            'order_verify'         => [
                'title'    => '商家审核售后提醒',
                'content'  => '例如：模板内容：您申请的售后请求已被${status}，请前往查看。',
                'loading'  => false,
                'variable' => [
                    [
                        'key'   => 'status',
                        'value' => '模板变量',
                        'desc'  => '例如：模板内容：您申请的售后请求已被${status}，请前往查看。则只需填写status',
                    ],
                ],
                'key'      => 'buyer',
            ],
            'order_refund_success' => [
                'title'    => '退款成功提醒',
                'content'  => '例如：模板内容：您的尾号为${code}的订单，商家已退款',
                'loading'  => false,
                'variable' => [
                    [
                        'key'   => 'code',
                        'value' => '模板变量',
                        'desc'  => '例如：模板内容：您的尾号为${code}的订单，商家已退款。则只需填写code',
                    ],
                ],
                'key'      => 'buyer',
            ],
            'score_changes'        => [
                'title'    => '积分变动提醒',
                'content'  => '例如：您的积分${name1}了${name2}，剩余${name3}',
                'loading'  => false,
                'variable' => [
                    [
                        'key'   => 'name1',
                        'value' => '模板变量',
                        'desc'  => '例如：模板内容：您的积分${name1}了${name2}，剩余${name3},则只需要填写${name1},${name2},${name3}',
                    ],
                    [
                        'key'   => 'name2',
                        'value' => '模板变量',
                        'desc'  => '例如：模板内容：您的积分${name1}了${name2}，剩余${name3},则只需要填写${name1},${name2},${name3}',
                    ],
                    [
                        'key'   => 'name3',
                        'value' => '模板变量',
                        'desc'  => '例如：模板内容：您的积分${name1}了${name2}，剩余${name3},则只需要填写${name1},${name2},${name3}',
                    ],
                ],
                'key'      => 'score',
            ],
            'score_due'            => [
                'title'    => '积分到期提醒',
                'content'  => '例如：示例：您在X年X月X日前获得的${number}积分即将到期，请及时使用',
                'loading'  => false,
                'variable' => [
                    [
                        'key'   => 'date',
                        'value' => '时间变量',
                        'desc'  => '例如：模板内容：您在${date}前获得的${number}积分即将到期，请及时使用,则只需填写date,number',
                    ],
                    [
                        'key'   => 'code',
                        'value' => '积分变量',
                        'desc'  => '例如：模板内容：您在${date}前获得的${code}积分即将到期，请及时使用,则只需填写date,code',
                    ],
                ],
                'key'      => 'score',
            ],
        ];
    }

    /**
     * 已存储数据和默认数据对比，以默认数据字段为准
     * @param $list
     * @param $default
     * @return mixed
     */
    public function check($list, $default)
    {
        foreach ($default as $key => $value) {
            if (!isset($list[$key])) {
                $list[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                $list[$key] = self::check($list[$key], $value);
            }
        }
        return $list;
    }
}
