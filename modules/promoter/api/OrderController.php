<?php

namespace promoter\api;

use app\components\ComPromoter;
use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

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

        return 222;
    }

    /**
     * 分销订单
     */
    public function actionSearch()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        //订单分组
        $keyword = Yii::$app->request->post('keyword', []);

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];

        $where = ['p.merchant_id' => $merchant_id, 'p.AppID' => $AppID, 'p.is_deleted' => 0];

        //佣金状态
        $status = $keyword['status'] ?? false;
        if ($status > 0 || $status === 0) {
            $where = ['and', $where, ['p.status' => $status]];
        }

        //订单来源
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['o.source' => $source]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'o.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'o.created_time', $time_end]];
        }

        $search_key = $keyword['search_key'] ?? false;
        $search     = $keyword['search'] ?? '';

        //订单编号
        if ($search_key == 'order_sn' && $search) {
            $where = ['and', $where, ['like', 'p.order_sn', $search]];
        }

        //买家昵称
        if ($search_key == 'buyer_nickname' && $search) {
            $where = ['and', $where, ['like', 'u.nickname', $search]];
        }

        //买家手机
        if ($search_key == 'buyer_mobile' && $search) {
            $where = ['and', $where, ['like', 'u.mobile', $search]];
        }

        //收货人名称
        if ($search_key == 'consignee_name' && $search) {
            $where = ['and', $where, ['like', 'b.name', $search]];
        }

        //收货人电话
        if ($search_key == 'consignee_mobile' && $search) {
            $where = ['and', $where, ['like', 'b.mobile', $search]];
        }
        //商品名称
        if ($search_key == 'goods_name' && $search) {
            $where = ['and', $where, ['like', 'g.goods_name', $search]];
        }
        //商品货号
        if ($search_key == 'goods_sn' && $search) {
            $where = ['and', $where, ['like', 'g.goods_sn', $search]];
        }

        $orderBy = ['p.created_time' => SORT_DESC];

        $data = new ActiveDataProvider(
            [
                'query'      => M('promoter', 'PromoterOrder')::find()
                    ->alias('p')
                    ->joinWith([
                        'buyer as b',
                        'orderGoods as g',
                        'order as o',
                        'user as u',
                        'commission' => function ($q) {
                            $q->with('user');
                        },
                    ])
                    ->where($where)
                    ->groupBy(['p.id'])
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as &$value) {
            $new_com = [];
            foreach ($value['commission'] as $v) {
                switch ($v['level']) {
                    case 1:
                        if (isset($new_com['first'])) {
                            $new_com['first'] = ['commission' => qm_round($new_com['first']['commission'] + $v['commission']), 'user' => $new_com['first']['user'] . '、' . $v['user']['nickname']];
                        } else {
                            $new_com['first'] = ['commission' => $v['commission'], 'user' => $v['user']['nickname']];
                        }
                        break;
                    case 2:
                        $new_com['second'] = ['commission' => $v['commission'], 'user' => $v['user']['nickname']];
                        break;
                    case 3:
                        $new_com['third'] = ['commission' => $v['commission'], 'user' => $v['user']['nickname']];
                        break;

                }
            }

            $value['commission'] = $new_com;
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

    /**
     * 订单退款时,修改分销订单
     */
    public static function editPromoterOrder($event)
    {
        $refunded       = $event->refunded;
        $order_sn       = $refunded['order_sn'];
        $order_goods_id = $refunded['order_goods_id'];
        $return_number  = $refunded['return_number'];
        $model          = M('promoter', 'PromoterOrder')::findOne(['order_sn' => $order_sn, 'order_goods_id' => $order_goods_id]);
        if ($model && $model->status >= 0) {
            $commission_number        = $model->commission_number;
            $model->commission_number = $model->commission_number - $return_number;
            if ($model->commission_number == 0) {
                $model->status = -1;
            }
            $scale          = $model->commission_number / $commission_number;
            $commssion_list = ArrayHelper::toArray($model->commission);

            $model->total_amount   = qm_round($scale * $model->total_amount, 2, 'floor');
            $model->profits_amount = qm_round($scale * $model->profits_amount, 2, 'floor');

            $col = [];
            $row = [];
            foreach ($commssion_list as &$v) {
                $v['commission']   = qm_round($scale * $v['commission'], 2, 'floor');
                $v['sales_amount'] = qm_round($scale * $v['sales_amount'], 2, 'floor');
                array_push($row, array_values($v));
                if (empty($col)) {
                    $col = array_keys($v);
                }
            }

            $t = Yii::$app->db->beginTransaction();

            $res = $model->save();

            $del_res = M('promoter', 'PromoterCommission')::deleteAll(['order_goods_id' => $order_goods_id]);

            $prefix     = Yii::$app->db->tablePrefix;
            $table_name = $prefix . 'promoter_commission';
            $batch_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();

            if ($res && $del_res && $batch_res) {
                $ComPromoter = new ComPromoter();
                $ComPromoter->setLevel(array_unique(array_column($commssion_list, 'UID')), 3);
                $t->commit();
            } else {
                $t->rollBack();
            }

        }

    }

}
