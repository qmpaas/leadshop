<?php

namespace promoter\app;

use app\components\ComPromoter;
use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class OrderController extends BasicController
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
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'count':
                return $this->getCount();
                break;
            default:
                return $this->getList();
                break;
        }
    }

    /**
     * 分销订单
     */
    private function getCount()
    {
        //订单分组
        $get = Yii::$app->request->get();

        $UID = Yii::$app->user->identity->id;

        $where = ['c.beneficiary' => $UID, 'c.is_deleted' => 0];

        $time_start = 0;
        $time_end   = 0;
        $time_type  = $get['time_type'] ?? 'all';
        switch ($time_type) {
            case 'today':
                $time_start = strtotime('today');
                $time_end   = 0;
                break;
            case 'yesterday':
                $time_start = strtotime('yesterday');
                $time_end   = strtotime('today');
                break;
            case 'month':
                $time_start = strtotime(date('Y-m'));
                $time_end   = 0;
                break;
            case 'free':
                $time_start = $get['time_start'] ?? 0;
                $time_end   = $get['time_end'] ?? 0;
                break;

        }

        //时间区间
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'o.created_time', $time_start]];
        }
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'o.created_time', $time_end]];
        }

        $data = M('promoter', 'PromoterCommission')::find()
            ->alias('c')
            ->leftJoin(['o' => M('promoter', 'PromoterOrder')::tablename()], 'c.order_goods_id = o.order_goods_id')
            ->where($where)
            ->select('sum(c.commission) all_commission_amount,count(c.id) as all_order_number')
            ->asArray()
            ->one();

        $wait_data = M('promoter', 'PromoterCommission')::find()
            ->alias('c')
            ->leftJoin(['o' => M('promoter', 'PromoterOrder')::tablename()], 'c.order_goods_id = o.order_goods_id')
            ->where($where)
            ->andwhere(['o.status' => 0])
            ->select('sum(c.commission) wait_account')
            ->asArray()
            ->one();
        $data['all_commission_amount'] = $data['all_commission_amount'] ?: '0.00';
        $data['wait_account']      = $wait_data['wait_account'] ?: '0.00'; //待结算
        $data['commission_amount'] = qm_round($data['all_commission_amount'] - $data['wait_account']); //待结算

        return $data;
    }

    /**
     * 分销订单
     */
    private function getList()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        //订单分组
        $get = Yii::$app->request->get();

        $UID = Yii::$app->user->identity->id;

        $where = ['c.beneficiary' => $UID, 'c.is_deleted' => 0];

        $time_start = 0;
        $time_end   = 0;
        $time_type  = $get['time_type'] ?? 'all';
        switch ($time_type) {
            case 'today':
                $time_start = strtotime('today');
                $time_end   = 0;
                break;
            case 'yesterday':
                $time_start = strtotime('yesterday');
                $time_end   = strtotime('today');
                break;
            case 'month':
                $time_start = strtotime(date('Y-m'));
                $time_end   = 0;
                break;
            case 'free':
                $time_start = $get['time_start'] ?? 0;
                $time_end   = $get['time_end'] ?? 0;
                break;

        }

        //时间区间
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'o.created_time', $time_start]];
        }
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'o.created_time', $time_end]];
        }

        $orderBy = ['o.created_time' => SORT_DESC];

        $data = new ActiveDataProvider(
            [
                'query'      => M('promoter', 'PromoterCommission')::find()
                    ->alias('c')
                    ->joinWith([
                        'promoterOrder as o' => function ($q) {
                            $q->select('order_goods_id,status,order_sn,UID,count_rules,profits_amount,total_amount')->with(['user', 'orderGoods']);
                        },
                    ])
                    ->where($where)
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;

    }

    /**
     * 创建分销订单
     */
    public static function addPromoterOrder($event)
    {
        $setting = StoreSetting('promoter_setting');
        if ($setting['status']) {
            $order_sn = $event->pay_order_sn;
            $UID      = $event->pay_uid;
            $list     = M('order', 'OrderGoods')::find()
                ->alias('og')
                ->leftJoin(['g' => M('goods', 'Goods')::tablename()], 'g.id = og.goods_id')
                ->leftJoin(['o' => M('order', 'Order')::tablename()], 'o.order_sn = og.order_sn')
                ->where(['og.order_sn' => $order_sn, 'g.is_promoter' => 1])
                ->select('og.*,o.UID')
                ->asArray()
                ->all();

            $my_info = M('users', 'User')::findOne($UID);
            if ($my_info->parent_id < 0) {
                $parent_id          = abs($my_info->parent_id);
                $check              = M('promoter', 'Promoter')::findOne(['UID' => $parent_id, 'status' => 2]);
                $my_info->parent_id = $check ? $parent_id : 0;
                $my_info->bind_time = time();
                $my_info->save();
            }
            if (count($list)) {
                $count_rules = StoreSetting('commission_setting', 'count_rules');
                $time        = time();
                $merchant_id = 1;
                $AppID       = Yii::$app->params['AppID'];

                /**
                 * 获取系统设置的分销等级配置
                 */
                $level_data = M('promoter', 'PromoterLevel')::find()->where(['AppID' => $AppID, 'merchant_id' => $merchant_id, 'is_deleted' => 0])->select('level,first,second,third')->asArray()->All();
                $level_data = array_column($level_data, null, 'level');

                /**
                 * 获取所有可以获得佣金的上级
                 */
                $commission_users = [];
                if ($setting['self_buy'] === 2) {
                    $my_p = $my_info->promoter;
                    if ($my_p && $my_p->status === 2) {
                        array_push($commission_users, ['UID' => $UID, 'c_level' => 1, 'p_level' => $my_p->level]);
                    }
                }
                $first_p = M('users', 'User')::findOne($my_info->parent_id);
                if ($first_p) {
                    array_push($commission_users, ['UID' => $first_p->id, 'c_level' => 1, 'p_level' => $first_p->promoter->level]);
                    if ($setting['level_number'] > 1) {
                        $second_p = M('users', 'User')::findOne($first_p->parent_id);
                        if ($second_p) {
                            array_push($commission_users, ['UID' => $second_p->id, 'c_level' => 2, 'p_level' => $second_p->promoter->level]);
                            if ($setting['level_number'] > 2) {
                                $third_p = M('users', 'User')::findOne($second_p->parent_id);
                                if ($third_p) {
                                    array_push($commission_users, ['UID' => $third_p->id, 'c_level' => 3, 'p_level' => $third_p->promoter->level]);
                                }
                            }
                        }
                    }
                }

                //分销订单数据
                $order_col = ['UID', 'order_sn', 'order_goods_id', 'goods_number', 'commission_number', 'total_amount', 'profits_amount', 'count_rules', 'AppID', 'merchant_id', 'created_time'];
                $order_row = [];
                //佣金记录数据
                $commission_col = ['beneficiary', 'order_goods_id', 'commission', 'sales_amount', 'level', 'created_time'];
                $commission_row = [];
                foreach ($list as $v) {
                    $profits_amount = qm_round(($v['pay_amount'] - $v['goods_number'] * $v['goods_cost_price']), 2);
                    if ($count_rules === 2 && $profits_amount <= 0) {
                        //利润不大于0的订单  直接跳过
                        $profits_amount = 0;
                    }
                    array_push($order_row, [$v['UID'], $v['order_sn'], $v['id'], $v['goods_number'], $v['goods_number'], $v['pay_amount'], $profits_amount, $count_rules, $AppID, $merchant_id, $time]);

                    //用户分销的金额
                    if ($count_rules === 1) {
                        $commission_amount = $v['pay_amount'];
                    } else {
                        $commission_amount = $profits_amount;
                    }

                    foreach ($commission_users as $value) {
                        switch ($value['c_level']) {
                            case 1:
                                $c_level = 'first';
                                break;
                            case 2:
                                $c_level = 'second';
                                break;
                            case 3:
                                $c_level = 'third';
                                break;
                        }
                        $comission = qm_round($commission_amount * ($level_data[$value['p_level']][$c_level] / 100), 2, 'floor');
                        array_push($commission_row, [$value['UID'], $v['id'], $comission, $v['pay_amount'], $value['c_level'], $time]);
                    }

                }

                $t                = Yii::$app->db->beginTransaction();
                $prefix           = Yii::$app->db->tablePrefix;
                $order_table      = $prefix . 'promoter_order';
                $order_res        = Yii::$app->db->createCommand()->batchInsert($order_table, $order_col, $order_row)->execute();
                $commission_table = $prefix . 'promoter_commission';
                $commission_res   = Yii::$app->db->createCommand()->batchInsert($commission_table, $commission_col, $commission_row)->execute();

                if ($order_res && $commission_res) {
                    $ComPromoter = new ComPromoter();
                    $ComPromoter->setLevel(array_unique(array_column($commission_users, 'UID')), 3);
                    $t->commit();
                } else {
                    $t->rollback();
                }

            }

        }

    }

}
