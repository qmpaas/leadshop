<?php
/**
 * 设置管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace sms\app;

use framework\common\BasicController;
use Overtrue\EasySms\Message;
use Yii;

class IndexController extends BasicController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 创建短信
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $post = Yii::$app->request->post();

        $mobile = $post['mobile'] ?? '';
        $time   = time() - 60;
        $check  = M('sms', 'SmsLog')::find()->where(['and', ['mobile' => $mobile, 'type' => 1], ['>=', 'created_time', $time]])->one();
        if ($check) {
            Error('发送过于频繁');
        }

        $post['code']       = $this->getCode();
        $post['valid_time'] = 600;
        $post['AppID']      = Yii::$app->params['AppID'];

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        $model       = M('sms', 'SmsLog', true);
        $model->setScenario('create');
        $model->setAttributes($post);
        if ($model->validate()) {
            if ($model->save()) {
                $this->module->event->sms = [
                    'type'   => 'captcha',
                    'debug'  => true,
                    'mobile' => [$post['mobile']],
                    'params' => [
                        'code' => $post['code'],
                    ],
                ];
                try {
                    $this->module->trigger('send_sms');
                    $transaction->commit(); //事务执行
                    return true;
                } catch (\Exception $exception) {
                    $transaction->rollBack(); //事务回滚
                    Error('发送失败' . $exception->getMessage());
                }
            } else {
                Error('操作失败');
            }
        } else {
            Error($model->getErrorMsg());
        }

    }

    public function getCode($length = 6)
    {

        $key     = '';
        $pattern = '1234567890';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern[mt_rand(0, 9)];
        }

        return $key;
    }

    public static function sendSms($event)
    {
        Yii::info('触发短信事件');
        try {
            $smsConfig = [];
            $model     = StoreSetting('sms_setting');
            if ($model) {
                $smsConfig = $model;
            }
            $setting = \sms\api\IndexController::getSetting();
            if (!$smsConfig || $smsConfig['status'] == 0) {
                Error('短信尚未开启');
            }
            if (!$smsConfig['access_key_id'] || !$smsConfig['access_key_secret'] || !$smsConfig['template_name']) {
                Error('短信尚未配置');
            }
            if (!(isset($smsConfig[$event->sms['type']])
                && isset($smsConfig[$event->sms['type']]['template_id'])
                && $smsConfig[$event->sms['type']]['template_id'])) {
                Error($setting[$event->sms['type']]['title'] . '模板ID未设置');
            }
            $data = [];
            foreach ($setting[$event->sms['type']]['variable'] as $value) {
                $data[$smsConfig[$event->sms['type']][$value['key']]] = $event->sms['params'][$value['key']] ?? '';
            }
            $message = new Message([
                'template' => $smsConfig[$event->sms['type']]['template_id'],
                'data'     => $data,
            ]);
            if (!is_array($event->sms['mobile'])) {
                $event->sms['mobile'] = [$event->sms['mobile']];
            }
            $res      = false;
            $errorMsg = '';
            foreach ($event->sms['mobile'] as $mobile) {
                try {
                    $res = Yii::$app->sms->module('industry', [
                        'access_key_id'     => $smsConfig['access_key_id'],
                        'access_key_secret' => $smsConfig['access_key_secret'],
                        'template_name'     => $smsConfig['template_name'],
                    ])->send($mobile, $message);
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    continue;
                }
            }
            if ($res) {
                return true;
            } else {
                throw new \Exception($errorMsg);
            }
        } catch (\Exception $exception) {
            Yii::error('==============sms error begin============');
            Yii::error('发送短信失败');
            Yii::error($exception->getMessage());
            Yii::error($exception);
            Yii::error('==============sms error end============');
            if (isset($event->sms['debug']) && $event->sms['debug'] == true) {
                Error($exception->getMessage());
            }
            return $exception->getMessage();
        }
    }
}
