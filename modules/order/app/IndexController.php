<?php
/**
 * 订单控制器
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace order\app;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class IndexController extends BasicController
{

    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $this->module->trigger('check_order');
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
            case 'tabcount':
                return $this->tabcount();
                break;
            default:
                return $this->list();
                break;
        }
    }

    /**
     * 订单列表
     * @return [type] [description]
     */
    function list() {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        //订单分组
        $UID   = Yii::$app->user->identity->id;
        $where = ['order.UID' => $UID, 'buyer.is_deleted' => 0];
        //处理获取商品类型

        $tab_key = Yii::$app->request->get('tab_key', 'all');
        switch ($tab_key) {
            case 'unpaid': //待付款
                $where = ['and', $where, ['order.status' => 100]];
                break;
            case 'unsent': //待发货
                $where = ['and', $where, ['order.status' => 201]];
                break;
            case 'unreceived': //待收货
                $where = ['and', $where, ['order.status' => 202]];
                break;
            case 'noevaluate': //待评价
                $where = ['and', $where, ['order.status' => 203, 'order.is_evaluate' => 0]];
                break;
        }

        // //时间区间
        // $time_start = $keyword['time_start'] ?? false;
        // if ($time_start > 0) {
        //     $where = ['and', $where, ['>=', 'order.created_time', $time_start]];
        // }
        // $time_end = $keyword['time_end'] ?? false;
        // if ($time_end > 0) {
        //     $where = ['and', $where, ['<=', 'order.created_time', $time_end]];
        // }

        // //商品名称
        // $goods_name = $keyword['goods_name'] ?? false;
        // if ($goods_name) {
        //     $where = ['and', $where, ['like', 'goods.goods_name', $goods_name]];
        // }

        $orderBy = ['order.created_time' => SORT_DESC];

        $data = new ActiveDataProvider(
            [
                'query'      => M()::find()
                    ->alias('order')
                    ->joinWith([
                        'buyer as buyer',
                        'goods as goods',
                        'user as user',
                    ])
                    ->where($where)
                    ->groupBy(['order.id'])
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as &$value) {
            foreach ($value['goods'] as &$goods) {
                if ($goods['after']) {
                    foreach ($goods['after'] as $v) {
                        if ($v['order_goods_id'] === 0) {
                            $goods['after'] = $v;
                            break;
                        } else {
                            if ($v['order_goods_id'] === $goods['id']) {
                                $goods['after'] = $v;
                                break;
                            }
                        }
                    }
                }
            }
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

    public function tabcount()
    {

        $UID   = Yii::$app->user->identity->id;
        $where = ['order.UID' => $UID, 'buyer.is_deleted' => 0];

        $data_list = ['unpaid' => 0, 'unsent' => 0, 'unreceived' => 0, 'noevaluate' => 0];
        foreach ($data_list as $key => &$value) {
            $w = null;
            switch ($key) {
                case 'unpaid': //待付款
                    $w = ['order.status' => 100];
                    break;
                case 'unsent': //待发货
                    $w = ['order.status' => 201];
                    break;
                case 'unreceived': //待收货
                    $w = ['order.status' => 202];
                    break;
                case 'noevaluate': //待评价
                    $w = ['order.status' => 203, 'order.is_evaluate' => 0];
                    break;
            }
            if ($w) {
                $w = ['and', $where, $w];
            } else {
                $w = $where;
            }

            $value = M()::find()->alias('order')
                ->joinWith([
                    'buyer as buyer',
                ])->where($w)->count();
        }

        $data_list['orderafter'] = M('order', 'OrderAfter')::find()->where(['UID' => $UID, 'is_deleted' => 0, 'status' => [100, 102, 111, 121, 122, 131, 132, 133]])->count();

        return $data_list;
    }

    /**
     * 订单详情
     * @return [type] [description]
     */
    public function actionView()
    {
        $id    = Yii::$app->request->get('id', false);
        $UID   = Yii::$app->user->identity->id;
        $where = ['order.id' => $id, 'order.UID' => $UID, 'buyer.is_deleted' => 0];

        $result = M()::find()
            ->alias('order')
            ->where($where)
            ->joinWith([
                'buyer as buyer',
                'goods as goods',
                'freight as freight' => function ($q) {
                    $q->with('goods');
                },
            ])
            ->asArray()
            ->one();

        if (empty($result)) {
            Error('订单不存在');
        }

        $result['check_after'] = false;
        foreach ($result['goods'] as &$goods) {
            if ($goods['after']) {
                $result['check_after'] = true;
                foreach ($goods['after'] as $v) {
                    if ($v['order_goods_id'] === 0) {
                        $goods['after'] = $v;
                        break;
                    } else {
                        if ($v['order_goods_id'] === $goods['id']) {
                            $goods['after'] = $v;
                            break;
                        }
                    }
                }
            }

        }
        if (count($result['freight']) === 1 && empty($result['freight'][0]['goods'])) {
            $order_goods = M('order', 'OrderGoods')::find()->where(['order_sn' => $result['order_sn']])->select('id,goods_name,goods_number,goods_image')->asArray()->all();
            $new_goods   = [];
            foreach ($order_goods as $o_g) {
                $new_o_g = [
                    'order_goods_id'   => $o_g['id'],
                    'bag_goods_number' => $o_g['goods_number'],
                    'goods'            => $o_g,
                ];
                array_push($new_goods, $new_o_g);
            }
            $result['freight'][0]['goods'] = $new_goods;
        }
        if (!empty($result['freight'])) {
            foreach ($result['freight'] as &$o_f) {
                $o_f['bag_goods_total'] = 0;
                foreach ($o_f['goods'] as $o_f_g) {
                    $o_f['bag_goods_total'] += $o_f_g['bag_goods_number'];
                }
            }
        }
        $result['goods_amount']  = $result['goods_amount'] + $result['goods_reduced'] + $result['coupon_reduced'];
        $result['store_reduced'] = $result['goods_reduced'] + $result['freight_reduced'];

        return str2url($result);
    }

    /**
     * 创建订单
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $post      = Yii::$app->request->post();
        $calculate = Yii::$app->request->get('calculate', false);

        $list = $this->build(); //预创建订单

        //先是指向预请求
        if ($calculate == 'calculate') {
            //订单预提交页面信息
            $return_data = $list[1];
            $number      = 0;
            foreach ($return_data['goods_data'] as &$value) {
                $number += $value['goods_number'];
            }
            $return_data['goods_number_amount'] = round($number, 2);
            return str2url($return_data);
        }

        $UID    = Yii::$app->user->identity->id;
        $AppID  = Yii::$app->params['AppID'];
        $source = Yii::$app->params['AppType'];

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        $order_list  = [];
        //拆单时统一支付金额
        $pay_total_amount = 0;
        //新增积分任务统计总支付积分
        $score_total_amount = 0;

        foreach ($list as $order_data) {
            $pay_total_amount += $order_data['pay_amount'];
            //统计积分总支出
            $score_total_amount += $order_data['total_score'] ?? 0;

            $setting_data = M('setting', 'Setting')::find()->where(['keyword' => 'setting_collection', 'merchant_id' => $order_data['merchant_id'], 'AppID' => $AppID])->select('content')->asArray()->one();
            if ($setting_data) {
                $setting_data['content'] = to_array($setting_data['content']);
                if (isset($setting_data['content']['trade_setting'])) {
                    $trade_setting = $setting_data['content']['trade_setting'];
                    if ($trade_setting['cancel_status']) {
                        $order_data['cancel_time'] = (float) $trade_setting['cancel_time'] * 60 * 60 + time();
                    }
                }
                if ($setting_data['content']['basic_setting']['run_status'] != 1) {
                    Error('店铺打烊中');
                }
            }
            $order_sn               = get_sn('osn');
            $order_data['order_sn'] = $order_sn;
            $order_data['UID']      = $UID;
            $order_data['AppID']    = $AppID;
            $order_data['type']     = $post['type'] ?? '';
            $order_data['status']   = 100;
            $order_data['source']   = $source ?? '';

            /**
             * 添加积分统计
             */
            //@增加积分商品订单字段
            $order_data['score_amount'] = $score_total_amount;

            $model = M('order', 'Order', true);
            $model->setScenario('create');
            $model->setAttributes($order_data);
            if ($model->validate()) {
                if ($model->save()) {
                    if (!N('consignee_info')) {
                        $transaction->rollBack(); //事务回滚
                        Error('收货人信息为空');
                    }
                    //买家信息插入
                    $buyer_data = [
                        'order_sn' => $order_sn,
                        'note'     => $post['note'] ?? '',
                        'name'     => $post['consignee_info']['name'],
                        'mobile'   => $post['consignee_info']['mobile'],
                        'province' => $post['consignee_info']['province'],
                        'city'     => $post['consignee_info']['city'],
                        'district' => $post['consignee_info']['district'],
                        'address'  => $post['consignee_info']['address'],
                    ];
                    $buyer_model = M('order', 'OrderBuyer', true);
                    $buyer_model->setScenario('create');
                    $buyer_model->setAttributes($buyer_data);
                    if ($buyer_model->validate()) {
                        $buyer_res = $buyer_model->save();
                    } else {
                        $transaction->rollBack();
                        return $buyer_model;
                    }

                    //订单商品信息批量插入处理
                    $row = [];
                    $col = [];
                    foreach ($order_data['goods_data'] as $v) {
                        $v['order_sn']     = $order_sn;
                        $v['created_time'] = time();
                        array_push($row, array_values($v));
                        if (empty($col)) {
                            $col = array_keys($v);
                        }
                    }

                    $prefix     = Yii::$app->db->tablePrefix;
                    $table_name = $prefix . 'order_goods';
                    $goods_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();

                    if ($buyer_res && $goods_res) {
                        $user_coupon_id = Yii::$app->request->post('user_coupon_id', false);
                        if ($user_coupon_id) {
                            $user_coupon_model           = M('coupon', 'UserCoupon')::findOne($user_coupon_id);
                            $user_coupon_model->order_sn = $order_sn;
                            $user_coupon_model->status   = 1;
                            $user_coupon_model->use_time = time();
                            $user_coupon_model->use_data = to_json($user_coupon_model->coupon->toArray());
                            if (!$user_coupon_model->save()) {
                                $transaction->rollBack(); //事务回滚
                                Error('下单失败');
                            }
                        }
                        array_push($order_list, ['order_sn' => $order_sn, 'order_id' => $model->attributes['id']]);
                    } else {
                        $transaction->rollBack(); //事务回滚
                        Error('下单失败');
                    }
                } else {
                    $transaction->rollBack(); //事务回滚
                    Error('下单失败');
                }

            } else {
                $transaction->rollBack(); //事务回滚
                return $model;
                // Error('下单失败');
            }
        }

        if (count($order_list) > 1) {
            $pay_model = M('order', 'OrderPay', true);
            $pay_sn    = get_sn('psn');
            $pay_data  = [
                'pay_sn'       => $pay_sn,
                'AppID'        => $AppID,
                'order_list'   => to_json($order_list),
                'total_amount' => $pay_total_amount,
            ];
            $pay_model->setAttributes($pay_data);
            if (!$pay_model->save()) {
                $transaction->rollBack(); //事务回滚
                Error('下单失败');
            }

            $return_data['pay_sn'] = $pay_sn;
        } else {
            $return_data = $order_list[0];
        }

        $return_data['pay_total_amount'] = $pay_total_amount;

        if ($model->type == "task") {
            //判断积分够不够
            $TaskUserModel = '\plugins\task\models\TaskUser';
            $TaskUser      = $TaskUserModel::find()->where(["UID" => $UID])->one();

            if (!$TaskUser || $TaskUser->number < $pay_total_amount) {
                Error("当前积分余额：" . $TaskUser->number, 416);
            }
        }

        //执行下单事件
        $this->module->event->order_goods = $post['goods_data'];

        //执行下单减库存
        if ($this->is_task()) {
            //插件模式下事件问题还没解决暂时直接写
            foreach ($post['goods_data'] as $value) {
                $GoodsData     = 'goods\models\GoodsData';
                $taskGoodsData = 'plugins\task\models\TaskGoods';
                $GoodsData::updateAllCounters(['task_stock' => (0 - $value['goods_number'])], ['goods_id' => $value['goods_id'], 'param_value' => $value['goods_param']]);
                $taskGoodsData::updateAllCounters(['task_stock' => (0 - $value['goods_number'])], ['id' => $value['goods_id']]);
            }
        } else {
            //普通订单这里处理
            $this->module->trigger('add_order');
        }

        // //判断插件已经安装，则执行
        // if ($this->plugins("task", "status")) {
        //     //判断是否积分订单
        //     if ($score_total_amount > 0) {
        //         //执行下单操作减积分操作
        //         $this->plugins("task", ["order", [
        //             $score_total_amount,
        //             $UID,
        //             $return_data['order_sn'],
        //             "order",
        //         ]]);
        //     }

        //     //执行下单操作
        //     $this->plugins("task", ["score", [
        //         "goods",
        //         $pay_total_amount,
        //         $UID,

        //     ]]);
        //     //执行下单操作
        //     $this->plugins("task", ["score", [
        //         "order",
        //         $pay_total_amount,
        //     ]]);
        // }

        if ($pay_total_amount == 0) {
            $free_res = $this->freePay($return_data);
            if (!$free_res) {
                $transaction->rollBack(); //事务回滚
                Error('下单失败');
            }
        }

        $transaction->commit(); //事务执行
        return $return_data;
    }

    public function freePay($order_info)
    {
        $order_sn = $order_info['order_sn'] ?? null;
        $model    = M('order', 'Order')::find()->where(['order_sn' => $order_sn])->one();

        Yii::info('判断插件是否安装' . $this->plugins("task", "status"));
        Yii::info('读取积分支付信息' . $model->total_score);
        Yii::info('读取积分支付总价' . $model->total_amount);
        Yii::info('读取积分支付订单' . $order_sn);
        Yii::info('读取积分支付用户' . $model->UID);

        if ($model && $model->status < 201) {
            $model->status   = 201;
            $model->pay_type = '';
            $model->pay_time = time();
            if ($model->save()) {

                //判断插件已经安装，则执行
                if ($this->plugins("task", "status")) {
                    //判断是否积分订单
                    if ($model->total_score > 0) {
                        //执行下单操作减积分操作
                        $this->plugins("task", ["order", [
                            $model->total_score,
                            $model->UID,
                            $order_sn,
                            "order",
                        ]]);
                    }
                    //执行下单操作
                    $this->plugins("task", ["score", [
                        "goods",
                        0,
                        $model->UID,
                        $order_sn,

                    ]]);
                    //执行下单操作
                    $this->plugins("task", ["score", [
                        "order",
                        $model->total_amount,
                        $model->UID,
                        $order_sn,
                    ]]);
                }

                $this->module->event->pay_order_sn = $order_sn;
                $this->module->event->pay_uid      = $model->UID;
                $this->module->trigger('pay_order');
                $this->module->event->user_statistical = ['UID' => $model->UID, 'buy_number' => 1, 'buy_amount' => $model->pay_amount, 'last_buy_time' => time()];
                $this->module->trigger('user_statistical');
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 重写刪除
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $id    = Yii::$app->request->get('id', false);
        $check = M()::findOne($id);
        if (!in_array($check->status, [101, 102, 103, 204])) {
            Error('该订单不能删除');
        }
        $result = M('order', 'OrderBuyer')::updateAll(['is_deleted' => 1], ['order_sn' => $check->order_sn]);
        if ($result) {
            return $result;
        } else {
            Error('删除失败');
        }

    }

    /**
     * 编辑中转
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'cancel': //取消订单
                return $this->cancel();
                break;
            case 'received': //确认收货
                return $this->received();
                break;
            default:
                Error('未定义操作');
                break;
        }
    }

    /**
     * 用户取消订单
     * @return [type] [description]
     */
    public function cancel()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = M()::findOne($id);
        if (empty($model)) {
            Error('订单不存在');
        }
        if ($model->status !== 100) {
            Error('非法操作');
        }
        $model->status      = 101;
        $model->cancel_time = time();

        if ($model->save()) {
            //执行取消订单事件
            $order_goods = M('order', 'OrderGoods')::find()->where(['order_sn' => $model->order_sn])->select('goods_id,goods_param,goods_number')->asArray()->all();

            //插件模式下事件问题还没解决暂时直接写
            if ($model->type == 'task') {
                foreach ($order_goods as $value) {
                    $GoodsData     = 'goods\models\GoodsData';
                    $taskGoodsData = 'plugins\task\models\TaskGoods';
                    $GoodsData::updateAllCounters(['task_stock' => $value['goods_number']], ['goods_id' => $value['goods_id'], 'param_value' => $value['goods_param']]);
                    $taskGoodsData::updateAllCounters(['task_stock' => $value['goods_number']], ['id' => $value['goods_id']]);
                }
            } else {
                $this->module->event->cancel_order_goods = $order_goods;
                $this->module->event->cancel_order_sn    = $model->order_sn;
                $this->module->trigger('cancel_order');
            }
            return true;
        } else {
            Error('操作失败');
        }
    }

    /**
     * 确认收货
     * @return [type] [description]
     */
    public function received()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = M()::findOne($id);
        if (empty($model)) {
            Error('订单不存在');
        }
        if ($model->status !== 202) {
            Error('非法操作');
        }
        $setting_data = M('setting', 'Setting')::find()->where(['keyword' => 'setting_collection', 'merchant_id' => $model->merchant_id, 'AppID' => $model->AppID])->select('content')->asArray()->one();
        if ($setting_data) {
            $setting_data['content'] = to_array($setting_data['content']);
            if (isset($setting_data['content']['trade_setting'])) {
                $trade_setting = $setting_data['content']['trade_setting'];
                if ($trade_setting['after_time']) {
                    $model->finish_time = (float) $trade_setting['after_time'] * 24 * 60 * 60 + time();
                }
                if ($trade_setting['evaluate_time']) {
                    $model->evaluate_time = (float) $trade_setting['evaluate_time'] * 24 * 60 * 60 + time();
                }
            }
        }
        $model->received_time = time();
        $model->status        = 203;
        if ($model->save()) {
            return true;
        } else {
            Error('操作失败');
        }
    }

    /**
     * 订单预创建
     * @return [type] [description]
     */
    public function build($type = 'shop_order')
    {
        $consignee_info = Yii::$app->request->post('consignee_info', []);
        // if (empty($consignee_info)) {
        //     Error('收货人信息为空');
        // }

        $return_data = [];
        switch ($type) {
            //商城订单
            case 'shop_order':
                $result = [];
                if ($this->is_task()) {
                    $result = $this->buildGoodsTask();
                } else {
                    $result = $this->buildGoods();
                }
                foreach ($result as $merchant_id => $value) {
                    //判断此处处理价格计算
                    if ($this->is_task()) {
                        $return_data[$merchant_id] = $this->buildAmountTask($value, $consignee_info, $merchant_id);

                        // $UID = Yii::$app->user->identity->id;
                        // //判断积分够不够
                        // $TaskUserModel = '\plugins\task\models\TaskUser';
                        // $TaskUser      = $TaskUserModel::find()->where(["UID" => $UID])->one();

                        // //跳转积分处理
                        // if (!$TaskUser || $TaskUser->number < $return_data[$merchant_id]['total_score']) {
                        //     Error("当前积分余额：" . $TaskUser->number, 416);
                        // }

                    } else {
                        $return_data[$merchant_id] = $this->buildAmount($value, $consignee_info, $merchant_id);
                    }
                }

                break;

        }
        return $return_data;
    }

    /**
     * 用于判断是否为积分商品
     * @return boolean [description]
     */
    public function is_task()
    {
        $task = Yii::$app->request->get('task', false); //判断是否是预请求
        if ($task == 'false') {
            $task = false;
        }

        if ($task == false) {
            $type = Yii::$app->request->post('type', false);
            if ($type == 'task') {
                $task = true;
            }
        }
        if ($this->plugins("task", "status")) {
            return $task;
        } else {
            return false;
        }
    }

    /**
     * 商品库存检测及处理
     * @return [type] [description]
     */
    public function buildGoods()
    {
        $calculate  = Yii::$app->request->get('calculate', false); //判断是否是预请求
        $UID        = Yii::$app->user->identity->id;
        $goods_data = Yii::$app->request->post('goods_data', []);

        if (empty($goods_data)) {
            Error('商品为空');
        }

        $goods_id = array_unique(array_column($goods_data, 'goods_id'));
        //获取所购买商品的列表
        $goods_list     = M('goods', 'Goods')::find()->where(['id' => $goods_id])->with(['param', 'freight', 'package'])->asArray()->all();
        $return_data    = [];
        $failure_reason = ''; //param规格不存在  is_sale下架  delete商品不存在  stocks库存不足  limit限购  min低于起购数
        $failure_number = null;

        $goods_data_number_count = [];
        foreach ($goods_data as $goods) {
            if (isset($goods_data_number_count[$goods['goods_id']])) {
                $goods_data_number_count[$goods['goods_id']] += $goods['goods_number'];
            } else {
                $goods_data_number_count[$goods['goods_id']] = $goods['goods_number'];
            }
        }
        foreach ($goods_list as $value) {

            //判断是否有删除或者下架
            if ($value['is_recycle'] === 1 || $value['is_sale'] === 0) {
                if ($calculate == 'calculate') {
                    $failure_reason = $value['is_recycle'] === 1 ? 'delete' : 'is_sale';
                } else {
                    Error($value['name'] . '不存在或已下架');
                }

            }

            $value['freight']['freight_rules'] = $value['freight'] ? to_array($value['freight']['freight_rules']) : null;
            $value['package']['free_area']     = $value['package'] ? to_array($value['package']['free_area']) : null;
            $param_data                        = to_array($value['param']['param_data']);
            $slideshow                         = to_array($value['slideshow']); //轮播图
            $first_param_info                  = array_column($param_data[0]['value'], null, 'value'); //第一个规格信息

            foreach ($goods_data as $v) {
                //商品数据去除多余部分
                $v = [
                    'goods_id'     => $v['goods_id'],
                    'goods_sn'     => $v['goods_sn'],
                    'goods_param'  => $v['goods_param'],
                    'goods_number' => $v['goods_number'],
                ];

                //购买商品和商品列表一一匹配
                if ($v['goods_id'] == $value['id']) {
                    //商品规格信息
                    $goods_info = array_column($value['param']['goods_data'], null, 'param_value');
                    if (!isset($goods_info[$v['goods_param']])) {
                        if ($calculate == 'calculate') {
                            $failure_reason = 'param';
                        } else {
                            Error($value['name'] . '不存在' . $v['goods_param']);
                        }
                    }
                    //库存判断
                    if ($goods_info[$v['goods_param']]['stocks'] < $v['goods_number']) {
                        if ($calculate == 'calculate') {
                            $failure_reason = 'stocks';
                        } else {
                            Error($value['name'] . '库存不足');
                        }

                    }
                    //起购数量判断
                    if ($value['min_number'] > $goods_data_number_count[$v['goods_id']]) {
                        if ($calculate == 'calculate') {
                            $failure_reason = 'min';
                        } else {
                            Error($value['name'] . $value['min_number'] . '份起购');
                        }

                    }
                    //限购判断
                    if ($value['limit_buy_status'] === 1) {
                        switch ($value['limit_buy_type']) {
                            case 'day':
                                $limit_time     = strtotime(date('Y-m-d'));
                                $limit_buy_type = '本日';
                                break;
                            case 'week':
                                $limit_time     = mktime(0, 0, 0, date('m'), date('d') - date('w'), date('Y'));
                                $limit_buy_type = '本周';
                                break;
                            case 'month':
                                $limit_time     = strtotime(date('Y-m'));
                                $limit_buy_type = '本月';
                                break;
                            case 'all':
                                $limit_time     = 0;
                                $limit_buy_type = '';
                                break;

                            default:
                                Error('限购时间类型错误');
                                break;
                        }

                        $UID          = Yii::$app->user->identity->id;
                        $goods_number = M('order', 'OrderGoods')::find()
                            ->alias('goods')
                            ->joinWith([
                                'order as order',
                            ])
                            ->where(['and', ['>', 'order.status', 200], ['>=', 'order.created_time', $limit_time], ['order.UID' => $UID, 'goods.goods_id' => $value['id']]])
                            ->SUM('goods.goods_number');
                        //算上当前购买量后的购买总数
                        if (($goods_number + $goods_data_number_count[$v['goods_id']]) > $value['limit_buy_value']) {
                            if ($calculate == 'calculate') {
                                $failure_reason = 'limit';
                                $failure_number = $value['limit_buy_value'];
                            } else {
                                $can_buy_num = $value['limit_buy_value'] - $goods_number;
                                if ($can_buy_num <= 0) {
                                    Error('您' . $limit_buy_type . '购买' . $value['name'] . '已达上限');
                                } else {
                                    Error('您' . $limit_buy_type . '还可以购买' . $value['name'] . ' ' . $can_buy_num . ' 份');
                                }
                            }

                        }
                    }

                    $first_param = explode('_', $v['goods_param'])[0]; //第一个规格
                    $goods_image = $param_data[0]['image_status'] && $first_param_info[$first_param]['image'] ? $first_param_info[$first_param]['image'] : $slideshow[0]; //存在规格图片则使用,不存在使用第一张轮播图

                    $show_goods_param = '';
                    $goods_param      = explode('_', $v['goods_param']);
                    foreach ($param_data as $key => $param_info) {
                        if ($param_info['name']) {
                            $show_goods_param .= $param_info['name'] . '：' . $goods_param[$key] . ' ';
                        } else {
                            $show_goods_param .= $goods_param[$key] . ' ';
                        }
                    }

                    $v['goods_name']       = $value['name'];
                    $v['show_goods_param'] = $show_goods_param;
                    $v['goods_price']      = $goods_info[$v['goods_param']]['price'];
                    $v['goods_cost_price'] = $goods_info[$v['goods_param']]['cost_price'] ? $goods_info[$v['goods_param']]['cost_price'] : 0;
                    $v['goods_weight']     = $goods_info[$v['goods_param']]['weight'] ? $goods_info[$v['goods_param']]['weight'] : 0;
                    $v['goods_image']      = $goods_image;
                    $v['freight']          = $value['freight'];
                    $v['package']          = $value['package'];
                    $v['ft_type']          = $value['ft_type'];
                    $v['ft_price']         = $value['ft_price'];
                    if ($calculate == 'calculate') {
                        $v['failure_reason'] = $failure_reason;
                        $v['failure_number'] = $failure_number;
                    }

                    if (array_key_exists($value['merchant_id'], $return_data)) {
                        array_push($return_data[$value['merchant_id']], $v);
                    } else {
                        $return_data[$value['merchant_id']] = [$v];
                    }
                }
            }
        }

        return $return_data;
    }

    /**
     * 商品库存检测及处理
     * @return [type] [description]
     */
    public function buildGoodsTask()
    {
        $calculate  = Yii::$app->request->get('calculate', false); //判断是否是预请求
        $UID        = Yii::$app->user->identity->id;
        $goods_data = Yii::$app->request->post('goods_data', []);

        if (empty($goods_data)) {
            Error('商品为空');
        }

        $goods_id = array_unique(array_column($goods_data, 'goods_id'));

        //判断是否是任务中心下单
        $goods_list = M('goods', 'Goods')::find()->where(['id' => $goods_id])->with(['param', 'freight', 'package', 'task'])->asArray()->all();

        $return_data    = [];
        $failure_reason = ''; //param规格不存在  is_sale下架  delete商品不存在  stocks库存不足  limit限购  min低于起购数
        $failure_number = null;

        $goods_data_number_count = [];
        foreach ($goods_data as $goods) {
            if (isset($goods_data_number_count[$goods['goods_id']])) {
                $goods_data_number_count[$goods['goods_id']] += $goods['goods_number'];
            } else {
                $goods_data_number_count[$goods['goods_id']] = $goods['goods_number'];
            }
        }

        foreach ($goods_list as $value) {

            //判断是否有删除或者下架
            if ($value['task']['goods_is_sale'] === 0) {
                if ($calculate == 'calculate') {
                    $failure_reason = $value['task']['goods_is_sale'] === 0 ? 'delete' : 'is_sale';
                } else {
                    Error($value['name'] . '不存在或已下架');
                }
            }

            $value['freight']['freight_rules'] = $value['freight'] ? to_array($value['freight']['freight_rules']) : null;
            $value['package']['free_area']     = $value['package'] ? to_array($value['package']['free_area']) : null;
            $param_data                        = to_array($value['param']['param_data']);
            $slideshow                         = to_array($value['slideshow']); //轮播图
            $first_param_info                  = array_column($param_data[0]['value'], null, 'value'); //第一个规格信息

            foreach ($goods_data as $v) {
                //商品数据去除多余部分
                $v = [
                    'goods_id'     => $v['goods_id'],
                    'goods_sn'     => $v['goods_sn'],
                    'goods_param'  => $v['goods_param'],
                    'goods_number' => $v['goods_number'],
                ];

                //购买商品和商品列表一一匹配
                if ($v['goods_id'] == $value['id']) {
                    //商品规格信息
                    $goods_info = array_column($value['param']['goods_data'], null, 'param_value');

                    if (!isset($goods_info[$v['goods_param']])) {
                        if ($calculate == 'calculate') {
                            $failure_reason = 'param';
                        } else {
                            Error($value['name'] . '不存在' . $v['goods_param']);
                        }
                    }

                    /**
                     * 用于判断处理积分商品
                     */
                    if ($goods_info[$v['goods_param']]['task_stock'] < $v['goods_number']) {
                        if ($calculate == 'calculate') {
                            $failure_reason = 'stocks';
                        } else {
                            Error("积分商品" . $value['name'] . '库存不足');
                        }
                    }

                    /**
                     * 判断兑换限制是否存在
                     */
                    if ($goods_info[$v['goods_param']]['task_limit']) {
                        if ($goods_info[$v['goods_param']]['task_limit'] > 0) {
                            //限制兑换-1 处理当前
                            if ($goods_info[$v['goods_param']]['task_limit'] < $v['goods_number']) {
                                if ($calculate == 'calculate') {
                                    $failure_reason = 'limit';
                                } else {
                                    Error("购买数量超过兑换限制数件");
                                }
                            }
                        }

                        //限购兑换-2 处理用户已购
                        $UID          = Yii::$app->user->identity->id;
                        $goods_number = M('order', 'OrderGoods')::find()
                            ->alias('goods')
                            ->joinWith([
                                'order as order',
                            ])
                            ->where(['and', ['>', 'order.status', 200], [
                                'order.UID'         => $UID,
                                'order.type'        => 'task',
                                'goods.goods_id'    => $value['id'],
                                'goods.goods_param' => $v['goods_param'],
                            ]])
                            ->SUM('goods.goods_number');

                        //算上当前购买量后的购买总数
                        if (($goods_number + $v['goods_number']) > $goods_info[$v['goods_param']]['task_limit']) {
                            if ($calculate == 'calculate') {
                                $failure_reason = 'limit';
                                $failure_number = $goods_info[$v['goods_param']]['task_limit'];
                            } else {
                                $can_buy_num = $goods_info[$v['goods_param']]['task_limit'] - $goods_number;
                                if ($can_buy_num <= 0) {
                                    Error('您兑换' . $value['name'] . '已达上限');
                                } else {
                                    Error('您还可以购买' . $value['name'] . ' ' . $can_buy_num . ' 份');
                                }
                            }
                        }
                    }

                    /**
                     * 判断是否为0
                     */
                    if ($goods_info[$v['goods_param']]['task_limit'] === 0) {
                        if ($calculate == 'calculate') {
                            $failure_reason = 'limit';
                        } else {
                            Error("购买数量超过兑换限制数件");
                        }
                    }

                    //判断积分够不够
                    $TaskUserModel = '\plugins\task\models\TaskUser';
                    $TaskUser      = $TaskUserModel::find()->where(["UID" => $UID])->one();

                    $total_score = $goods_info[$v['goods_param']]['task_number'] * $v['goods_number'];
                    Yii::info('出发余额计算方式:' . $total_score);
                    Yii::info('出发用户余额:' . $TaskUser->number);
                    //跳转积分处理
                    if (!$TaskUser || $TaskUser->number < $total_score) {
                        if ($calculate == 'calculate') {
                            $failure_reason = to_json([
                                'score' => $TaskUser->number,
                                'msg'   => "当前积分余额：" . $TaskUser->number,
                            ]);
                        } else {
                            Error("当前积分余额：" . $TaskUser->number, 416);
                        }
                    }

                    $first_param = explode('_', $v['goods_param'])[0]; //第一个规格
                    $goods_image = $param_data[0]['image_status'] && $first_param_info[$first_param]['image'] ? $first_param_info[$first_param]['image'] : $slideshow[0]; //存在规格图片则使用,不存在使用第一张轮播图

                    $show_goods_param = '';
                    $goods_param      = explode('_', $v['goods_param']);
                    foreach ($param_data as $key => $param_info) {
                        if ($param_info['name']) {
                            $show_goods_param .= $param_info['name'] . '：' . $goods_param[$key] . ' ';
                        } else {
                            $show_goods_param .= $goods_param[$key] . ' ';
                        }
                    }

                    $v['goods_name']       = $value['name'];
                    $v['show_goods_param'] = $show_goods_param;
                    $v['goods_score']      = $goods_info[$v['goods_param']]['task_number'] ?? 0;
                    $v['goods_price']      = $goods_info[$v['goods_param']]['task_price'];
                    $v['goods_cost_price'] = $goods_info[$v['goods_param']]['cost_price'] ? $goods_info[$v['goods_param']]['cost_price'] : 0;
                    $v['goods_weight']     = $goods_info[$v['goods_param']]['weight'] ? $goods_info[$v['goods_param']]['weight'] : 0;
                    $v['goods_image']      = $goods_image;
                    $v['freight']          = $value['freight'];
                    $v['package']          = $value['package'];
                    $v['ft_type']          = $value['ft_type'];
                    $v['ft_price']         = $value['ft_price'];
                    if ($calculate == 'calculate') {
                        $v['failure_reason'] = $failure_reason;
                        $v['failure_number'] = $failure_number;
                    }

                    if (array_key_exists($value['merchant_id'], $return_data)) {
                        array_push($return_data[$value['merchant_id']], $v);
                    } else {
                        $return_data[$value['merchant_id']] = [$v];
                    }
                }
            }
        }

        return $return_data;
    }

    /**
     * 金额计算
     * @return [type] [description]
     */
    public function buildAmount($goods, $consignee_info, $merchant_id)
    {
        $freight_data   = $this->buildFreightPrice($goods, $consignee_info);
        $goods_amount   = $freight_data['goods_amount']; //商品总金额
        $freight_amount = $freight_data['freight_amount']; //总运费

        foreach ($goods as $key => &$value) {
            $goods_price = $value['goods_number'] * $value['goods_price'];

            unset($value['freight']);
            unset($value['package']);
            unset($value['ft_type']);
            unset($value['ft_price']);
            $value['total_amount']   = $goods_price;
            $value['pay_amount']     = $goods_price;
            $value['coupon_reduced'] = 0;

        }

        $total_amount = $goods_amount + $freight_amount;
        $return_data  = [
            'total_amount'   => $total_amount,
            'goods_amount'   => $goods_amount,
            'pay_amount'     => $total_amount,
            'freight_amount' => $freight_amount,
            'coupon_reduced' => 0,
            'merchant_id'    => $merchant_id,
            'goods_data'     => $goods,
        ];

        $return_data = $this->buildReducePrice($return_data);

        return $return_data;

    }

    /**
     * 金额计算
     * @return [type] [description]
     */
    public function buildAmountTask($goods, $consignee_info, $merchant_id)
    {
        $freight_data   = $this->buildFreightPrice($goods, $consignee_info);
        $goods_amount   = $freight_data['goods_amount']; //商品总金额
        $freight_amount = $freight_data['freight_amount']; //总运费
        //累计计算积分
        $total_score = 0;
        foreach ($goods as $key => &$value) {
            $goods_price = $value['goods_number'] * $value['goods_price'];
            $goods_score = $value['goods_number'] * $value['goods_score'];
            unset($value['freight']);
            unset($value['package']);
            unset($value['ft_type']);
            unset($value['ft_price']);
            $value['total_amount']   = $goods_price;
            $value['pay_amount']     = $goods_price;
            $value['score_amount']   = $goods_score;
            $value['coupon_reduced'] = 0;
            //处理积分统计
            $total_score += $goods_score;
        }

        $total_amount = $goods_amount + $freight_amount;
        $return_data  = [
            'total_score'    => $total_score,
            'total_amount'   => $total_amount,
            'goods_amount'   => $goods_amount,
            'pay_amount'     => $total_amount,
            'freight_amount' => $freight_amount,
            'coupon_reduced' => 0,
            'merchant_id'    => $merchant_id,
            'goods_data'     => $goods,
        ];

        $return_data = $this->buildReducePrice($return_data);

        return $return_data;

    }

    /**
     * 运费处理
     */
    public function buildFreightPrice($goods, $consignee_info)
    {
        $calculate = Yii::$app->request->get('calculate', false); //判断是否是预请求

        $number_amount  = 0; //商品总数
        $goods_amount   = 0; //商品总金额
        $freight_amount = 0; //总运费

        $new_goods = [];
        foreach ($goods as $v) {
            $goods_amount += $v['goods_price'] * $v['goods_number'];
            $number_amount += $v['goods_number'];
            if (isset($new_goods[$v['goods_id']])) {
                $new_goods[$v['goods_id']]['goods_number'] += $v['goods_number'];
            } else {
                $new_goods[$v['goods_id']] = $v;
            }
        }

        $goods = array_merge($new_goods);

        //按商品数量高到底排序,优化运费计算
        for ($i = 0; $i < count($goods); $i++) {
            for ($j = $i + 1; $j < count($goods); $j++) {
                if ($goods[$i]['goods_number'] < $goods[$j]['goods_number']) {
                    $tem       = $goods[$j];
                    $goods[$j] = $goods[$i];
                    $goods[$i] = $tem;
                }
            }
        }

        $first_price_key = 0; //选择的首件所在的商品键
        $first_price     = -1; //选择的首件价格
        foreach ($goods as $k => &$v) {
            $v['freight_rules'] = []; //拿到对应的运费计算规则
            if (!empty($consignee_info) && is_array($v['freight']['freight_rules'])) {
                foreach ($v['freight']['freight_rules'] as $freight_rules) {
                    $province = array_column($freight_rules['area'], null, 'name');
                    if (array_key_exists($consignee_info['province'], $province)) {
                        $city = $province[$consignee_info['province']]['list'];
                        $city = array_column($city, null, 'name');
                        if (array_key_exists($consignee_info['city'], $city)) {
                            $district = $city[$consignee_info['city']]['list'];
                            $district = array_column($district, null, 'name');
                            if (array_key_exists($consignee_info['district'], $district)) {
                                if ($first_price < $freight_rules['first']['price']) {
                                    //取用首件价格高的
                                    $first_price     = $freight_rules['first']['price'];
                                    $first_price_key = $k;
                                } elseif ($first_price == $freight_rules['first']['price']) {
                                    //首件价格相等,计算首件平均价
                                    $first_freight_rules = $goods[$first_price_key]['freight_rules'];
                                    if (($first_freight_rules['first']['price'] / $first_freight_rules['first']['number']) < ($freight_rules['first']['price'] / $freight_rules['first']['number'])) {
                                        //取用首件平均价高的
                                        $first_price     = $freight_rules['first']['price'];
                                        $first_price_key = $k;

                                    } elseif (($first_freight_rules['first']['price'] / $first_freight_rules['first']['number']) == ($freight_rules['first']['price'] / $freight_rules['first']['number'])) {
                                        //首件平均价相等,判断续件价格
                                        if ($first_freight_rules['continue']['price'] < $freight_rules['continue']['price']) {
                                            //取用续件价格高的
                                            $first_price     = $freight_rules['first']['price'];
                                            $first_price_key = $k;
                                        } elseif ($first_freight_rules['continue']['price'] == $freight_rules['continue']['price']) {
                                            //续件价格相同,计算续件均价
                                            if (($first_freight_rules['continue']['price'] / $first_freight_rules['continue']['number']) < ($freight_rules['continue']['price'] / $freight_rules['continue']['number'])) {
                                                //取用续件平均价格高的
                                                $first_price     = $freight_rules['first']['price'];
                                                $first_price_key = $k;
                                            }
                                        }
                                    }
                                }
                                $v['freight_rules'] = $freight_rules;
                            }
                        }
                    }
                }
            }
        }

        foreach ($goods as $key => $value) {
            $goods_number = $value['goods_number'];
            $goods_weight = $value['goods_number'] * $value['goods_weight'];
            $goods_price  = $value['goods_number'] * $value['goods_price'];

            if ($calculate == 'calculate' && empty($consignee_info)) {
                $UID            = Yii::$app->user->identity->id;
                $consignee_info = M("users", 'Address')::find()->where(['UID' => $UID, 'status' => 1])->asArray()->one();
            }

            $freight = 0;
            if (!empty($consignee_info)) {
                //计算初始运费
                if ($value['ft_type'] === 1) {
                    //固定邮费
                    $freight = $value['ft_price'];
                } else {

                    $freight_rules = $value['freight_rules'];
                    if (!empty($freight_rules)) {
                        if ($value['freight']['type'] == 1) {
                            //按件计算
                            $f_number = $goods_number;
                        } else {
                            //按重计算
                            $f_number = $goods_weight;
                        }

                        if ($first_price_key == $key) {

                            $freight += $freight_rules['first']['price']; //首件首重费用

                            $continue = $f_number - $freight_rules['first']['number']; //判断是否超出首件数量或首重重量
                        } else {
                            $continue = $f_number;
                        }
                        if ($continue > 0 && $freight_rules['continue']['number'] > 0) {
                            $freight += ceil($continue / $freight_rules['continue']['number']) * $freight_rules['continue']['price'];
                        }
                    }

                }

                //包邮计算
                if (is_array($value['package']['free_area'])) {
                    foreach ($value['package']['free_area'] as $free_area) {
                        $province = array_column($free_area['area'], null, 'name');
                        if (array_key_exists($consignee_info['province'], $province)) {
                            $city = $province[$consignee_info['province']]['list'];
                            $city = array_column($city, null, 'name');
                            if (array_key_exists($consignee_info['city'], $city)) {
                                $district = $city[$consignee_info['city']]['list'];
                                $district = array_column($district, null, 'name');
                                if (array_key_exists($consignee_info['district'], $district)) {
                                    switch ($value['package']['type']) {
                                        case 1:
                                            //订单满额
                                            $p_number = $goods_amount;
                                            break;
                                        case 2:
                                            //订单满件
                                            $p_number = $number_amount;
                                            break;
                                        case 3:
                                            //商品满额
                                            $p_number = $goods_price;
                                            break;
                                        case 4:
                                            //商品满件
                                            $p_number = $goods_number;
                                            break;
                                    }
                                    if ($p_number >= $free_area['number']) {
                                        $freight = 0;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $freight_amount += $freight;

        }

        return [
            'goods_amount'   => $goods_amount,
            'freight_amount' => $freight_amount,
        ];
    }

    /**
     * 价格减免
     */
    public function buildReducePrice($data)
    {
        $calculate = Yii::$app->request->get('calculate', false); //判断是否是预请求
        $goods_id  = array_unique(array_column($data['goods_data'], 'goods_id'));
        //获取所购买商品的列表
        $goods_list = M('goods', 'Goods')::find()->where(['id' => $goods_id])->select('id,group')->asArray()->all();
        foreach ($goods_list as &$goods) {
            $goods['group'] = array_unique(explode('-', trim($goods['group'], '-')));
        }

        //执行优惠券计算
        $user_coupon_id = Yii::$app->request->post('user_coupon_id', false);
        if ($user_coupon_id) {
            $u_c_info = M('coupon', 'UserCoupon')::find()->where(['id' => $user_coupon_id])->with(['coupon' => function ($q) {
                $q->select('type,discount,min_price,sub_price,appoint_type,appoint_data');
            }])->asArray()->one();

            if (empty($u_c_info)) {
                Error('优惠券找不到');
            }

            if ($u_c_info['status'] === 1) {
                Error('优惠券已使用');
            } elseif ($u_c_info['status'] === 2) {
                Error('优惠券已失效');
            }

            $time = time();
            if ($time < $u_c_info['begin_time']) {
                Error('优惠券还不能使用');
            } elseif ($time > $u_c_info['end_time']) {
                Error('优惠券已过期');
            }

            if ((float) $u_c_info['coupon']['min_price'] > 0 && $data['goods_amount'] < (float) $u_c_info['coupon']['min_price']) {
                Error('优惠券不可用');
            }

            $appoint_data = explode('-', trim($u_c_info['coupon']['appoint_data'], '-'));
            switch ($u_c_info['coupon']['appoint_type']) {
                case 2:
                    $diff = array_diff($goods_id, $appoint_data);
                    //存在差集则说明有商品不属于指定包含商品
                    if (!empty($diff)) {
                        Error('优惠券不可用');
                    }
                    break;
                case 4:
                    $intersect = array_intersect($goods_id, $appoint_data);
                    //有交集则说明有商品属于不包含商品
                    if (!empty($intersect)) {
                        Error('优惠券不可用');
                    }
                    break;
                case 3:
                    foreach ($goods_list as $goods) {
                        $intersect = array_intersect($goods['group'], $appoint_data);
                        //没有交集则说明此商品分类不属于包含分类
                        if (empty($intersect)) {
                            Error('优惠券不可用');
                        }
                    }
                    break;
                case 5:
                    foreach ($goods_list as $goods) {
                        $intersect = array_intersect($goods['group'], $appoint_data);
                        //有交集则说明此商品分类存在属于不包含分类
                        if (!empty($intersect)) {
                            Error('优惠券不可用');
                        }
                    }
                    break;

                default:

                    break;
            }

            $data['coupon_reduced'] = $data['goods_amount'] < $u_c_info['coupon']['sub_price'] ? $data['goods_amount'] : $u_c_info['coupon']['sub_price'];
            $goods_amount           = $data['goods_amount'] - $data['coupon_reduced'];
            $discount               = $data['goods_amount'] > 0 ? $goods_amount / $data['goods_amount'] : 0;
            $data['pay_amount']     = $data['pay_amount'] - $data['coupon_reduced'];

            //预请求,显示原来价格
            if ($calculate != 'calculate') {
                $data['goods_amount'] = $goods_amount;
            }

            foreach ($data['goods_data'] as &$value) {
                $goods_pay_amount        = round($value['pay_amount'] * $discount, 2);
                $value['coupon_reduced'] = $value['pay_amount'] - $goods_pay_amount;
                $value['pay_amount']     = $goods_pay_amount;
            }
        }

        return $data;

    }

}
