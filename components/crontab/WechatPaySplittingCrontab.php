<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2022/8/15
 * Time: 16:35
 */

namespace app\components\crontab;

use app\forms\CommonWechat;
use system\models\WeappPay;

class WechatPaySplittingCrontab extends BaseCrontab
{
    public function name()
    {
        return '微信小程序支付分账';
    }

    public function desc()
    {
        return '微信小程序支付分账';
    }

    public function doCrontab()
    {
        $wechat = new CommonWechat(['AppID' => \Yii::$app->params['AppID'] ?? '']);
        /**@var WeappPay[] $list*/
        $list = WeappPay::find()->where([
            'AND',
            ['!=', 'transaction_id', ''],
            ['profit_id' => ''],
            ['<=', 'created_time', time() - 3600 * 24 * 7],
            ['error_msg' => '']
        ])->with(['order.oauth'])->limit(3)->all();
        foreach ($list as $item) {
            try {
                if ($item->order->status != 204) {
                    continue;
                }
                $share = get_sn('share');
                $res = $wechat->profitsharingorder($item, $share);
                if (!isset($res['errcode'])) {
                    $item->error_msg = $res;
                }
                $item->profit_id = $share;
                if (!$item->save()) {
                    Error($item->getErrorMsg());
                }
            } catch (\Exception $e) {
                \Yii::error($e);
            }
        }
        return true;
    }
}
