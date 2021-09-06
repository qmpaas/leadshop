<?php
/**
 * 分销订单结算定时任务
 */

namespace app\components\crontab;

use promoter\models\Promoter;
use promoter\models\PromoterOrder;
use Yii;

class PromoterOrderCrontab extends BaseCrontab
{
    public function name()
    {

    }

    public function desc()
    {

    }

    public function doCrontab()
    {
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];

        $where = ['p.merchant_id' => $merchant_id, 'p.AppID' => $AppID, 'p.status' => 0, 'o.status' => 204];

        $data = PromoterOrder::find()
            ->alias('p')
            ->joinWith([
                'order as o',
                'commission',
            ])
            ->where($where)
            ->groupBy(['p.id'])
            ->asArray()
            ->all();

        $p_o_list    = [];
        $change_list = [];
        foreach ($data as $v) {
            array_push($p_o_list, $v['id']);
            $commission = $v['commission'];
            foreach ($commission as $key => $value) {
                if (isset($change_list[$value['beneficiary']])) {
                    $change_list[$value['beneficiary']] = ['commission' => qm_round($change_list[$value['beneficiary']]['commission'] + $value['commission'], 2, 'floor')];
                } else {
                    $change_list[$value['beneficiary']] = ['commission' => $value['commission']];
                }

            }
        }

        if (count($p_o_list)) {
            $t       = Yii::$app->db->beginTransaction();
            $result  = PromoterOrder::updateAll(['status' => 1], ['id' => $p_o_list]);
            $result2 = false;
            if ($result) {

                foreach ($change_list as $key => $value) {
                    $p_model = Promoter::findOne(['UID' => $key]);
                    $p_model->commission += $value['commission'];
                    $p_model->commission_amount += $value['commission'];

                    $res     = $p_model->save();
                    $result2 = $res;
                    if (!$res) {
                        break;
                    }
                }
            }

            if ($result && $result2) {
                $t->commit();
            } else {
                $t->rollBack();
            }
        }

    }
}
