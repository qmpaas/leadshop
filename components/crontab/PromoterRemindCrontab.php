<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/4/30
 * Time: 17:52
 */

namespace app\components\crontab;

use app\components\subscribe\LevelChangeMessage;
use promoter\models\PromoterLevelChangeLog;
use Overtrue\EasySms\Message;
use Yii;

class PromoterRemindCrontab extends BaseCrontab
{
    public function name()
    {

    }

    public function desc()
    {

    }

    public function doCrontab()
    {
        $list = PromoterLevelChangeLog::find()
            ->with('user')
            ->where(['push_status' => 0])
            ->limit(100)
            ->asArray()
            ->all();
        if ($list) {
            $name          = '小店';
            $store_setting = StoreSetting('setting_collection', 'store_setting');
            if ($store_setting) {
                $name = $store_setting['name'] ?? '小店';
            }

            foreach ($list as $item) {
                try {
                    if ($item['type'] == 1) {
                        Yii::$app->subscribe->setUser($item['UID'])->setPage('promoter/pages/index')->send(new LevelChangeMessage([
                            'nowLevel'    => $item['new_level_name'],
                            'originLevel' => $item['old_level_name'],
                            'time'        => date('Y-m-d H:i', time()),
                        ]));
                    }
                } catch (\Exception $e) {
                    Yii::error('======分销商等级变化订阅消息失败======');
                    Yii::error($e);
                }
            }

            $smsConfig = StoreSetting('sms_setting');
            $setting   = \sms\api\IndexController::getSetting();
            foreach ($list as $item) {
                try {
                    $msg    = $item['type'] == 1 ? '升级' : '降级';
                    $params = ['status' => $msg, 'name' => $item['new_level_name']];

                    if (!$smsConfig || $smsConfig['status'] == 0) {
                        Error('短信尚未开启');
                    }
                    if (!$smsConfig['access_key_id'] || !$smsConfig['access_key_secret'] || !$smsConfig['template_name']) {
                        Error('短信尚未配置');
                    }
                    if (!(isset($smsConfig['level_change'])
                        && isset($smsConfig['level_change']['template_id'])
                        && $smsConfig['level_change']['template_id'])) {
                        Error($setting['level_change']['title'] . '模板ID未设置');
                    }
                    $data = [];
                    foreach ($setting['level_change']['variable'] as $value) {
                        $data[$smsConfig['level_change'][$value['key']]] = $params[$value['key']] ?? '';
                    }
                    $message = new Message([
                        'template' => $smsConfig['level_change']['template_id'],
                        'data'     => $data,
                    ]);
                    Yii::$app->sms->module('industry', [
                        'access_key_id'     => $smsConfig['access_key_id'],
                        'access_key_secret' => $smsConfig['access_key_secret'],
                        'template_name'     => $smsConfig['template_name'],
                    ])->send($item['user']['mobile'], $message);
                } catch (\Exception $e) {
                    Yii::error('======分销商等级变化短信消息失败======');
                    Yii::error($e);
                }
            }

            PromoterLevelChangeLog::updateAll(['push_status' => 1], ['id' => array_column($list, 'id')]);
        }
    }
}
