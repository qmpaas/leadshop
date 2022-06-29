<?php
/**
 * 统计
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

class StatisticalController extends BasicsModules implements Map
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

        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'userstatistical': //用户统计
                return $this->userStatistical();
                break;
            case 'storeCount': //店铺统计
                return $this->storeCount();
                break;
            case 'nearTwoDay': //最近两天统计
                return $this->nearTwoDay();
                break;
            case 'exportGoods': //商品销售统计导出
                return $this->exportGoods();
                break;
            case 'exportUsers': //用户消费统计导出
                return $this->exportUsers();
                break;
            default:
                Error('未定义操作');
                break;
        }

    }

    public function userStatistical()
    {
        return $this->runModule('users', 'index', "statistical");
    }

    public function storeCount()
    {
        $AppID       = Yii::$app->params['AppID'];
        $merchant_id = 1;
        $where       = ['AppID' => $AppID];

        $unpaid        = M('order', 'Order')::find()->where(['and', $where, ['status' => 100, 'is_deleted' => 0]])->count();
        $unsent        = M('order', 'Order')::find()->where(['and', $where, ['status' => 201, 'is_deleted' => 0]])->count();
        $order_after   = M('order', 'OrderAfter')::find()->where(['and', $where, ['<', 'status', 200]])->count();
        $user_number   = M('users', 'User')::find()->where($where)->count();
        $income_amount = M('order', 'Order')::find()->where(['and', $where, ['>', 'status', 200]])->sum('pay_amount');
        $out_amount    = M('order', 'OrderAfter')::find()->where(['and', $where, ['status' => 200], ['<>', 'type', 2]])->sum('actual_refund');

        $data = [
            'unpaid'        => $unpaid,
            'unsent'        => $unsent,
            'order_after'   => $order_after,
            'user_number'   => $user_number,
            'income_amount' => qm_round(($income_amount - $out_amount), 2),
        ];

        return $data;
    }

    public function nearTwoDay()
    {
        $yesterday = strtotime('yesterday');
        $today     = strtotime(date("Y-m-d"));

        $AppID = Yii::$app->params['AppID'];
        $where = ['and', ['AppID' => $AppID], ['>=', 'created_time', $yesterday]];

        $visit_data = M('statistical', 'VisitLog')::find()->where($where)->select('UID,created_time')->asArray()->all();
        $order_data = M('order', 'Order')::find()->where(['and', $where, ['>', 'status', 200]])->select('pay_amount,UID,created_time')->asArray()->all();

        $visit_list       = [];
        $pay_amount_list  = [];
        $num_order_list   = [];
        $num_buyer_list   = [];
        $today_visit_list = [];

        for ($i = 0; $i < 48; $i++) {
            $visit          = [];
            $pay_amount     = 0;
            $num_order      = 0;
            $num_buyer      = [];
            $start_time     = $yesterday + $i * 3600;
            $end_time       = $yesterday + ($i + 1) * 3600;
            $new_visit_data = [];
            foreach ($visit_data as $v1) {
                if ($v1['created_time'] >= $start_time && $v1['created_time'] < $end_time) {
                    array_push($visit, $v1['UID']);
                    if ($v1['created_time'] >= $today) {
                        array_push($today_visit_list, $v1['UID']);
                    }
                } else {
                    array_push($new_visit_data, $v1);
                }
            }
            $visit_data = $new_visit_data;

            $new_order_data = [];
            foreach ($order_data as $v2) {
                if ($v2['created_time'] >= $start_time && $v2['created_time'] < $end_time) {
                    $pay_amount += $v2['pay_amount'];
                    $num_order++;
                    array_push($num_buyer, $v2['UID']);
                } else {
                    array_push($new_order_data, $v2);
                }
            }
            $order_data = $new_order_data;

            $visit_list[$i]      = count(array_unique($visit));
            $pay_amount_list[$i] = qm_round($pay_amount, 2);
            $num_order_list[$i]  = $num_order;
            $num_buyer_list[$i]  = count(array_unique($num_buyer));
        }

        $yesterday_list = [
            'visit_list'      => array_slice($visit_list, 0, 24),
            'pay_amount_list' => array_slice($pay_amount_list, 0, 24),
            'num_order_list'  => array_slice($num_order_list, 0, 24),
            'num_buyer_list'  => array_slice($num_buyer_list, 0, 24),
        ];

        $today_list = [
            'visit_list'      => array_slice($visit_list, 24, 24),
            'pay_amount_list' => array_slice($pay_amount_list, 24, 24),
            'num_order_list'  => array_slice($num_order_list, 24, 24),
            'num_buyer_list'  => array_slice($num_buyer_list, 24, 24),
        ];

        $today_visit      = count(array_unique($today_visit_list));
        $today_pay_amount = array_sum($today_list['pay_amount_list']);
        $today_num_order  = array_sum($today_list['num_order_list']);
        $today_num_buyer  = array_sum($today_list['num_buyer_list']);
        return [
            'today_visit'      => $today_visit,
            'today_pay_amount' => qm_round($today_pay_amount, 2),
            'today_num_order'  => $today_num_order,
            'today_num_buyer'  => $today_num_buyer,
            'yesterday_list'   => $yesterday_list,
            'today_list'       => $today_list,
        ];

    }

    public function exportGoods()
    {
        $sort        = Yii::$app->request->get('sort', '');
        $sort        = to_array($sort);
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['is_recycle' => 0, 'merchant_id' => $merchant_id, 'AppID' => $AppID];
        $orderBy     = [];
        foreach ($sort as $key => $value) {
            if (!sql_check($key)) {
                $orderBy[$key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
            }
        }
        $data = M('goods', 'Goods')::find()
            ->where($where)
            ->orderBy($orderBy)
            ->limit(100)
            ->select('id,name,sales,sales_amount')
            ->asArray()
            ->all();

        $tHeader = ['排名', 'ID', '名称', '销量', '销售额'];

        $list = [];
        foreach ($data as $key => $value) {
            $arr = [$key + 1, $value['id'], $value['name'], $value['sales'], (float) $value['sales_amount']];
            array_push($list, $arr);
        }
        return ['tHeader' => $tHeader, 'list' => $list];

    }

    public function exportUsers()
    {
        $sort        = Yii::$app->request->get('sort', '');
        $sort        = to_array($sort);
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['user.is_deleted' => 0, 'user.AppID' => $AppID];
        $orderBy     = [];
        foreach ($sort as $key => $value) {
            if (!sql_check($key)) {
                $orderBy['statistical.' . $key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
            }
        }
        $data = M('users', 'User')::find()
            ->alias('user')
            ->joinWith([
                'statistical as statistical',
            ])
            ->where($where)
            ->orderBy($orderBy)
            ->select('user.id,user.nickname,statistical.buy_number,statistical.buy_amount')
            ->distinct()
            ->asArray()
            ->all();

        $tHeader = ['排名', 'ID', '用户', '支付金额', '支付件数'];

        $list = [];
        foreach ($data as $key => $value) {
            $buy_amount = $value['statistical'] ? $value['statistical']['buy_amount'] : 0.00;
            $buy_number = $value['statistical'] ? $value['statistical']['buy_number'] : 0;
            $arr        = [$key + 1, $value['id'], $value['nickname'], (float) $buy_amount, $buy_number];
            array_push($list, $arr);
        }
        return ['tHeader' => $tHeader, 'list' => $list];

    }

}
