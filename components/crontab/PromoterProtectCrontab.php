<?php
/**
 * 分销保护期模式定时器
 */

namespace app\components\crontab;

use app\components\ComPromoter;
use users\models\User;
use Yii;

class PromoterProtectCrontab extends BaseCrontab
{
    public function name()
    {

    }

    public function desc()
    {

    }

    public function doCrontab()
    {
        $setting = StoreSetting('promoter_setting');

        if ($setting['status'] && $setting['bind_type'] == 2) {
            $AppID        = Yii::$app->params['AppID'];
            $days         = (float) $setting['bind_days'];
            $time         = time() - $days * 86400;
            $protect_time = $setting['protect_time'];
            //当前时间超过  开启保护模式时间+保护天数   时才开始清除
            if (($protect_time + $days * 86400) < time()) {
                try {
                    $where       = ['and', ['AppID' => $AppID], ['<>', 'parent_id', 0], ['<=', 'bind_time', $time]];
                    $parent_list = User::find()->where($where)->select('id,parent_id')->asArray()->all();
                    $res         = User::updateAll(['parent_id' => 0, 'bind_time' => null], $where);
                    if ($res) {
                        $parent_id   = array_column($parent_list, 'parent_id');
                        $ComPromoter = new ComPromoter();
                        //保存分销商失去下级记录
                        $ComPromoter->loseLog($parent_list, 3);
                        //对那些被解绑的父级,重新进行分销等级评估
                        $ComPromoter->setLevel($parent_id, 2);
                    }
                } catch (\Exception $e) {
                    \Yii::error($e);
                }
            }
        }

    }

}
