<?php
/**
 * 售后订单控制器
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace order\api;

use app\components\subscribe\OrderRefundMessage;
use app\components\subscribe\OrderSaleVerifyMessage;
use app\components\subscribe\TaskSendMessage;
use framework\common\BasicController;
use order\models\OrderAfter;
use sms\app\IndexController as smsController;
use Yii;
use yii\data\ActiveDataProvider;

class AfterController extends BasicController
{
    public $modelClass = 'order\models\OrderAfter';

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
        return '占位方法';
    }

    public function actionTabcount()
    {
        //商品分组
        $keyword = Yii::$app->request->post('keyword', []);

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['after.merchant_id' => $merchant_id, 'after.AppID' => $AppID, 'after.is_deleted' => 0];

        //申请类型
        $type = $keyword['type'] ?? false;
        if ($type > 0 || $type === 0) {
            $where = ['and', $where, ['after.type' => $type]];
        }

        //订单来源
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['after.source' => $source]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'after.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'after.created_time', $time_end]];
        }

        $search_key = $keyword['search_key'] ?? false;
        $search     = $keyword['search'] ?? '';

        //订单编号
        if ($search_key == 'order_sn' && $search) {
            $where = ['and', $where, ['like', 'after.order_sn', $search]];
        }

        //订单编号
        if ($search_key == 'after_sn' && $search) {
            $where = ['and', $where, ['like', 'after.after_sn', $search]];
        }

        //买家昵称
        if ($search_key == 'buyer_nickname' && $search) {
            $where = ['and', $where, ['like', 'user.nickname', $search]];
        }

        //买家手机
        if ($search_key == 'buyer_mobile' && $search) {
            $where = ['and', $where, ['like', 'user.mobile', $search]];
        }

        //收货人名称
        if ($search_key == 'consignee_name' && $search) {
            $where = ['and', $where, ['like', 'buyer.name', $search]];
        }

        //收货人电话
        if ($search_key == 'consignee_mobile' && $search) {
            $where = ['and', $where, ['like', 'buyer.mobile', $search]];
        }
        $o_g_ids = [];
        //商品名称
        if ($search_key == 'goods_name' && $search) {
            $ids = M('order', 'OrderGoods')::find()
                ->alias('g')
                ->leftJoin(['o' => M('order', 'Order')::tablename()], 'o.order_sn = g.order_sn')
                ->where(['and', ['like', 'g.goods_name', $search], ['o.AppID' => $AppID, 'o.merchant_id' => $merchant_id]])->select('id')->asArray()->all();
            $ids     = array_column($ids, 'id');
            $o_g_ids = array_merge($o_g_ids, $ids);

        }
        //商品货号
        if ($search_key == 'goods_sn' && $search) {
            $ids = M('order', 'OrderGoods')::find()
                ->alias('g')
                ->leftJoin(['o' => M('order', 'Order')::tablename()], 'o.order_sn = g.order_sn')
                ->where(['and', ['like', 'g.goods_sn', $search], ['o.AppID' => $AppID, 'o.merchant_id' => $merchant_id]])->select('id')->asArray()->all();
            $ids     = array_column($ids, 'id');
            $o_g_ids = array_merge($o_g_ids, $ids);
        }

        if (!empty($o_g_ids)) {
            $o_g_ids = array_unique($o_g_ids);
            $where   = ['and', $where, ['goods.order_goods_id' => $o_g_ids]];
        }

        $data_list = ['all' => [], 'waitaudit' => [], 'bybuyer' => [], 'bymerchant' => [], 'finished' => [], 'closed' => [], 'recycle' => []];

        foreach ($data_list as $key => &$value) {
            switch ($key) {
                case 'waitaudit': //待审核
                    $w = ['after.status' => [100, 102]];
                    break;
                case 'bybuyer': //用户操作
                    $w = ['after.status' => [121, 131, 133]];
                    break;
                case 'bymerchant': //商家操作
                    $w = ['after.status' => [111, 122, 132]];
                    break;
                case 'finished': //已完成
                    $w = ['after.status' => [200, 201]];
                    break;
                case 'closed': //已关闭
                    $w = ['after.status' => 101];
                    break;

                default: //默认获取全部
                    $w = [];
                    break;
            }

            if (empty($w)) {
                $w = $where;
            } else {
                $w = ['and', $where, $w];
            }
            $value = OrderAfter::find()
                ->alias('after')
                ->joinWith([
                    'buyer as buyer',
                    'goods as goods',
                    'user as user',
                ])
                ->where($w)
                ->groupBy(['after.id'])
                ->count();
        }

        return $data_list;
    }

    /**
     * 后台订单列表
     * @return [type] [description]
     */
    public function actionSearch()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        //订单分组
        $keyword = Yii::$app->request->post('keyword', []);

        //处理获取商品类型
        $tab_key = $keyword['tab_key'] ?? 'all';
        switch ($tab_key) {
            case 'waitaudit': //待审核
                $where = ['after.status' => [100, 102]];
                break;
            case 'bybuyer': //用户操作
                $where = ['after.status' => [121, 131, 133]];
                break;
            case 'bymerchant': //商家操作
                $where = ['after.status' => [111, 122, 132]];
                break;
            case 'finished': //已完成
                $where = ['after.status' => [200, 201]];
                break;
            case 'closed': //已关闭
                $where = ['after.status' => 101];
                break;

            default: //默认获取全部
                $where = [];
                break;
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        if (empty($where)) {
            $where = ['after.merchant_id' => $merchant_id, 'after.AppID' => $AppID, 'after.is_deleted' => 0];
        } else {

            $where = ['and', $where, ['after.merchant_id' => $merchant_id, 'after.AppID' => $AppID, 'after.is_deleted' => 0]];
        }

        //申请类型
        $type = $keyword['type'] ?? false;
        if ($type > 0 || $type === 0) {
            $where = ['and', $where, ['after.type' => $type]];
        }

        //订单来源
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['after.source' => $source]];
        }

        //订单类型
        $order_type = $keyword['order_type'] ?? '';
        if ($order_type) {
            $where = ['and', $where, ['after.order_type' => $order_type]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'after.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'after.created_time', $time_end]];
        }

        $search_key = $keyword['search_key'] ?? false;
        $search     = $keyword['search'] ?? '';

        //订单编号
        if ($search_key == 'order_sn' && $search) {
            $where = ['and', $where, ['like', 'after.order_sn', $search]];
        }

        //订单编号
        if ($search_key == 'after_sn' && $search) {
            $where = ['and', $where, ['like', 'after.after_sn', $search]];
        }

        //买家昵称
        if ($search_key == 'buyer_nickname' && $search) {
            $where = ['and', $where, ['like', 'user.nickname', $search]];
        }

        //买家手机
        if ($search_key == 'buyer_mobile' && $search) {
            $where = ['and', $where, ['like', 'user.mobile', $search]];
        }

        //收货人名称
        if ($search_key == 'consignee_name' && $search) {
            $where = ['and', $where, ['like', 'buyer.name', $search]];
        }

        //收货人电话
        if ($search_key == 'consignee_mobile' && $search) {
            $where = ['and', $where, ['like', 'buyer.mobile', $search]];
        }
        $o_g_ids = [];
        //商品名称
        if ($search_key == 'goods_name' && $search) {
            $ids = M('order', 'OrderGoods')::find()
                ->alias('g')
                ->leftJoin(['o' => M('order', 'Order')::tablename()], 'o.order_sn = g.order_sn')
                ->where(['and', ['like', 'g.goods_name', $search], ['o.AppID' => $AppID, 'o.merchant_id' => $merchant_id]])->select('id')->asArray()->all();
            $ids     = array_column($ids, 'id');
            $o_g_ids = array_merge($o_g_ids, $ids);

        }
        //商品货号
        if ($search_key == 'goods_sn' && $search) {
            $ids = M('order', 'OrderGoods')::find()
                ->alias('g')
                ->leftJoin(['o' => M('order', 'Order')::tablename()], 'o.order_sn = g.order_sn')
                ->where(['and', ['like', 'g.goods_sn', $search], ['o.AppID' => $AppID, 'o.merchant_id' => $merchant_id]])->select('id')->asArray()->all();
            $ids     = array_column($ids, 'id');
            $o_g_ids = array_merge($o_g_ids, $ids);
        }

        if (!empty($o_g_ids)) {
            $o_g_ids = array_unique($o_g_ids);
            $where   = ['and', $where, ['goods.order_goods_id' => $o_g_ids]];
        }

        //处理排序
        $sort    = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['after.created_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                $orderBy['after.' . $key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
            }
        }

        $data = new ActiveDataProvider(
            [
                'query'      => OrderAfter::find()
                    ->alias('after')
                    ->joinWith([
                        'buyer as buyer',
                        'goods as goods',
                        'order as order',
                        'user as user',
                    ])
                    ->where($where)
                    ->groupBy(['after.id'])
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            if ($value['order_goods_id']) {
                $new_goods      = null;
                $goods          = array_column($value['goods'], null, 'id');
                $new_goods      = isset($goods[$value['order_goods_id']]) ? [$goods[$value['order_goods_id']]] : null;
                $value['goods'] = $new_goods;
            }
            $value['images']                = to_array($value['images']);
            $value['merchant_freight_info'] = to_array($value['merchant_freight_info']);
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

    /**
     * 订单详情
     * @return [type] [description]
     */
    public function actionView()
    {
        $id       = Yii::$app->request->get('id', false);
        $behavior = Yii::$app->request->get('behavior', false);

        if ($behavior === 'order_goods') {
            $check = OrderAfter::findOne(['order_goods_id' => $id]);
            if (!$check) {
                $o_g_info = M('order', 'OrderGoods')::findOne($id);
                $where    = ['order_sn' => $o_g_info->order_sn];
            } else {
                $where = ['order_goods_id' => $id];
            }

        } else {
            $where = ['id' => $id];
        }

        $model = OrderAfter::findOne($where);

        $result = OrderAfter::find()
            ->where($where)
            ->with([
                'buyer',
                'goods',
                'order',
                'user',
            ])
            ->asArray()
            ->one();
        if ($result) {
            if ($result['order_goods_id']) {
                $goods           = array_column($result['goods'], null, 'id');
                $new_goods[]     = isset($goods[$result['order_goods_id']]) ? $goods[$result['order_goods_id']] : null;
                $result['goods'] = $new_goods;
            }
            $result['images']                = to_array($result['images']);
            $result['process']               = to_array($result['process']);
            $result['return_address']        = to_array($result['return_address']);
            $result['user_freight_info']     = to_array($result['user_freight_info']);
            $result['merchant_freight_info'] = to_array($result['merchant_freight_info']);
            $result                          = str2url($result);
            return $result;
        } else {
            Error('售后不存在');
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

        $model = null;
        switch ($behavior) {
            case 'refuse': //拒绝
                $model = $this->refuse();
                break;
            case 'pass': //通过
                $model = $this->pass();
                break;
            case 'refund': //退款
                $model = $this->refund();
                break;
            case 'salesreturn': //退货退款
                $model = $this->salesReturn();
                break;
            case 'salesexchange': //换货
                $model = $this->salesExchange();
                break;
            case 'exchangefreight': //换货物流
                $model = $this->exchangeFreight();
                break;
            default:
                Error('未定义操作');
                break;
        }
        if (!($model instanceof OrderAfter)) {
            Error('未定义操作');
        }
        $this->orderAfter($model);
        return $model;
    }

    /**
     * 拒绝申请
     * @return [type] [description]
     */
    public function refuse()
    {
        $id    = Yii::$app->request->get('id', false);
        $post  = Yii::$app->request->post();
        $model = OrderAfter::findOne($id);
        if (empty($model)) {
            Error('售后订单不存在');
        }
        $process = to_array($model->process);
        $time    = time();
        if ($model->status === 100) {
            //首次拒绝
            $model->status      = 101;
            $model->refuse_time = $time;
            array_unshift($process, ['label' => '卖家', 'content' => '拒绝售后 ' . date('Y-m-d H:i:s', $time)]);
            if (isset($post['refuse_reason'])) {
                $model->refuse_reason = $post['refuse_reason'];
            }
        } elseif ($model->status === 102) {
            //二次拒绝后转完成
            $model->status      = 201;
            $model->finish_time = $time;
            array_unshift($process, ['label' => '结束', 'content' => '已完成(已拒绝) ' . date('Y-m-d H:i:s', $time)]);
            if (isset($post['refuse_reason'])) {
                $model->second_refuse_reason = $post['refuse_reason'];
            }
        } else {
            Error('非法操作');
        }
        $model->process = to_json($process);

        if ($model->save()) {
            return $model;
        } else {
            Error('操作失败');
        }
    }

    /**
     * 通过申请
     * @return [type] [description]
     */
    public function pass()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = OrderAfter::findOne($id);
        if (empty($model)) {
            Error('售后订单不存在');
        }
        if ($model->status !== 100 && $model->status !== 102) {
            Error('非法操作');
        }
        if ($model->type === 1) {
            $return_address = Yii::$app->request->post('return_address', false);
            if (empty($return_address)) {
                Error('退货地址不能为空');
            }
            //退货退款
            $model->status         = 121;
            $model->return_address = to_json($return_address);
        } elseif ($model->type === 2) {
            $return_address = Yii::$app->request->post('return_address', false);
            if (empty($return_address)) {
                Error('退货地址不能为空');
            }
            //换货
            $model->status         = 131;
            $model->return_address = to_json($return_address);
        } else {
            //退款
            $model->status = 111;
            $order_sn = $model->order_sn;
            $order_info = M('order', 'Order')::find()->where(['order_sn' => $order_sn])->one();
            if ($order_info->status === 201 && $order_info->freight_amount > 0 && $model->order_goods_id) {
                $a_g_count = OrderAfter::find()->where(['and',['order_sn'=>$order_sn],['>','status',110],['<','status',201]])->sum('return_number');//退款商品数
                $a_g_count = $a_g_count ?: 0;
                $o_g_count = M('order', 'OrderGoods')::find()->where(['order_sn'=>$order_sn])->sum('goods_number');//订单商品数
                if (($a_g_count+$model->return_number) >= $o_g_count) {
                    $model->return_freight = $order_info->freight_amount;
                    $model->return_amount += $model->return_freight;
                }
            }
        }
        $model->audit_time = time();

        $process = to_array($model->process);
        array_unshift($process, ['label' => '卖家', 'content' => '审核通过 ' . date('Y-m-d H:i:s', time())]);
        $model->process = to_json($process);

        if ($model->save()) {
            return $model;
        } else {
            Error('操作失败');
        }
    }

    /**
     * 退款
     * @return [type] [description]
     */
    public function refund()
    {
        $id            = Yii::$app->request->get('id', false);
        $actual_refund = Yii::$app->request->post('actual_refund', false);
        $actual_score  = Yii::$app->request->post('actual_score', 0);
        $model         = OrderAfter::findOne($id);

        if (empty($model)) {
            Error('售后订单不存在');
        }
        if ($model->status !== 111) {
            Error('非法操作');
        }

        if (!$actual_refund || $actual_refund < 0 || $actual_refund > $model->return_amount) {
            Error('退款金额异常');
        }

        if ($actual_score) {
            if ($actual_score < 0 || $actual_score > $model->return_score) {
                Error('退还积分异常');
            }
        }

        $order_info   = M('order', 'Order')::find()->where(['order_sn' => $model->order_sn])->select('pay_amount,pay_number,source')->asArray()->one();
        $return_order = [
            'order_sn'   => $order_info['pay_number'],
            'pay_amount' => $order_info['pay_amount'],
            'source'     => $order_info['source'],
        ];
        $return_sn = get_sn('rsn');

        return Yii::$app->payment->refund($return_order, $return_sn, $actual_refund, function () use ($model, $actual_refund, $actual_score, $return_sn) {
            $time                     = time();
            $model->actual_refund     = $actual_refund;
            $model->actual_score      = $actual_score;
            $model->return_sn         = $return_sn;
            $model->status            = 200;
            $model->return_time       = $time;
            $model->finish_time       = $time;

            $process = to_array($model->process);
            array_unshift($process, ['label' => '卖家', 'content' => '退款' . date('Y-m-d H:i:s', $time)]);
            array_unshift($process, ['label' => '结束', 'content' => '已完成 ' . date('Y-m-d H:i:s', $time)]);
            $model->process = to_json($process);
            if ($model->save()) {
                $after_count = OrderAfter::find()->where(['and', ['order_sn' => $model->order_sn], ['>=', 'status', 200]])->count();
                $goods_count = M('order', 'OrderGoods')::find()->where(['order_sn' => $model->order_sn])->count();
                if ($model->order_goods_id === 0 || $after_count >= $goods_count) {
                    $order_model = M('order', 'Order')::find()->where(['order_sn' => $model->order_sn])->one();

                    $order_model->after_sales = 1;
                    $order_model->finish_time = $time;

                    if ($order_model->status < 203) {
                        $order_model->received_time = null;
                    }
                    $order_model->save();
                }

                $this->orderFinishCheck($model->order_sn);
                $this->module->event->refunded = ['order_sn' => $model->order_sn, 'order_goods_id' => $model->order_goods_id, 'return_number' => $model->return_number];
                $this->module->trigger('refunded');

                //处理任务中心积分退还问题
                $model->return_score_type = $this->plugins("task", "config.integral_return");
                $this->onReturnScore($model);

                return $model;
            } else {
                Error('操作失败');
            }
        });
    }

    /**
     * 执行积分退还
     * @param  string $model [description]
     * @return [type]        [description]
     */
    public function onReturnScore($model)
    {
        //判断是否安装
        $task_status = $this->plugins("task", "status");
        $task_return = $this->plugins("task", "config.integral_return");
        //用于判断积分可退
        if ($model->order_type == 'task' && $task_status && $task_return) {
            $ScoreModel = '\plugins\task\models\TaskScore';
            //预下单积分处理
            $ScoreClass             = (new $ScoreModel());
            $ScoreClass->task_id    = 0;
            $ScoreClass->UID        = $model->UID;
            $ScoreClass->start_time = time();
            $ScoreClass->status     = 1;
            $ScoreClass->order_sn   = $model->order_sn;
            $ScoreClass->type       = 'add';
            $ScoreClass->number     = $model->actual_score;
            $ScoreClass->remark     = "订单退款退积分";
            $ScoreClass->insert();
            //积分返回用户账户
            $balance = $this->onUserNumer($model->UID, $model->actual_score);

            //读取用户信息
            $UserData = \users\models\User::find()->where(["id" => $model->UID])->one();

            //处理执行消息订阅
            \Yii::$app->subscribe
                ->setUser($model->UID)
                ->setPage('plugins/task/index')
                ->send(new TaskSendMessage([
                    'number'  => $model->actual_score,
                    'balance' => $balance,
                    'remark'  => "订单退款退积分",
                    'time'    => date("Y年m月d日 H:m", time()),
                ]));

            //判断手机号是否存在
            if ($UserData && $UserData->mobile) {
                //处理短信模板
                $event      = array('sms' => []);
                $event      = json_decode(json_encode($event));
                $event->sms = array(
                    'type'   => 'score_changes',
                    'mobile' => [$UserData->mobile],
                    'params' => [
                        'name1' => '变动',
                        'name2' => $model->actual_score,
                        'name3' => $balance,
                    ],
                );
                //执行短信发送
                (new smsController($this->id, $this->module))->sendSms($event);
            }

        }
    }

    /**
     * 处理积分返回用户账号
     * @param  string $UID    [description]
     * @param  [type] $number [description]
     * @return [type]         [description]
     */
    public function onUserNumer($UID = '', $number)
    {
        //获取用户信息
        $UserModel = '\plugins\task\models\TaskUser';
        $UserClass = $UserModel::find()->where(["UID" => $UID])->one();
        //处理用户积分信息
        $UserClass->number += $number;
        $UserClass->total += $number;
        //执行积分数据写入
        $returned = $UserClass->save();
        return $UserClass->number;
    }

    /**
     * 退货退款
     * @return [type] [description]
     */
    public function salesReturn()
    {

        $id            = Yii::$app->request->get('id', false);
        $actual_refund = Yii::$app->request->post('actual_refund', false);
        $actual_score  = Yii::$app->request->post('actual_score', 0);
        $model         = OrderAfter::findOne($id);

        if (empty($model)) {
            Error('售后订单不存在');
        }
        if ($model->status !== 122) {
            Error('非法操作');
        }

        if (!$actual_refund || $actual_refund < 0 || $actual_refund > $model->return_amount) {
            Error('退款金额异常');
        }

        if ($actual_score) {
            if ($actual_score < 0 || $actual_score > $model->return_score) {
                Error('退还积分异常');
            }
        }

        $order_info   = M('order', 'Order')::find()->where(['order_sn' => $model->order_sn])->select('pay_amount,pay_number,source')->asArray()->one();
        $return_order = [
            'order_sn'   => $order_info['pay_number'],
            'pay_amount' => $order_info['pay_amount'],
            'source'     => $order_info['source'],
        ];
        $return_sn = get_sn('rsn');
        return Yii::$app->payment->refund($return_order, $return_sn, $actual_refund, function () use ($model, $actual_refund, $actual_score, $return_sn) {
            $time                     = time();
            $model->actual_refund     = $actual_refund;
            $model->actual_score      = $actual_score;
            $model->return_sn         = $return_sn;
            $model->status            = 200;
            $model->return_time       = $time;
            $model->finish_time       = $time;

            $process = to_array($model->process);
            array_unshift($process, ['label' => '卖家', 'content' => '确认收货并退款' . date('Y-m-d H:i:s', $time)]);
            array_unshift($process, ['label' => '结束', 'content' => '已完成 ' . date('Y-m-d H:i:s', $time)]);
            $model->process = to_json($process);
            if ($model->save()) {
                $after_count = OrderAfter::find()->where(['and', ['order_sn' => $model->order_sn], ['>=', 'status', 200]])->count();
                $goods_count = M('order', 'OrderGoods')::find()->where(['order_sn' => $model->order_sn])->count();
                if ($after_count >= $goods_count) {
                    $order_model = M('order', 'Order')::find()->where(['order_sn' => $model->order_sn])->one();

                    $order_model->after_sales = 1;
                    $order_model->finish_time = $time;

                    if ($order_model->status < 203) {
                        $order_model->received_time = null;
                    }
                    $order_model->save();
                }
                $this->orderFinishCheck($model->order_sn);

                $this->module->event->refunded = ['order_sn' => $model->order_sn, 'order_goods_id' => $model->order_goods_id, 'return_number' => $model->return_number];
                $this->module->trigger('refunded');

                //处理任务中心积分退还问题
                $model->return_score_type = $this->plugins("task", "config.integral_return");
                $this->onReturnScore($model);
                return $model;
            } else {
                Error('操作失败');
            }
        });
    }

    /**
     * 换货
     * @return [type] [description]
     */
    public function salesExchange()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = OrderAfter::findOne($id);
        if (empty($model)) {
            Error('售后订单不存在');
        }

        if ($model->status !== 132) {
            Error('非法操作');
        }
        $time                         = time();
        $merchant_freight_info        = Yii::$app->request->post('merchant_freight_info', []);
        $model->merchant_freight_info = to_json($merchant_freight_info);
        $model->status                = 200;
        $model->exchange_time         = $time;
        $model->finish_time           = $time;

        $process = to_array($model->process);
        array_unshift($process, ['label' => '卖家', 'content' => '确认收货并发货 ' . date('Y-m-d H:i:s', $time)]);
        array_unshift($process, ['label' => '结束', 'content' => '已完成 ' . date('Y-m-d H:i:s', $time)]);
        $model->process = to_json($process);

        if ($model->save()) {
            $after_count = OrderAfter::find()->where(['and', ['order_sn' => $model->order_sn], ['>=', 'status', 200]])->count();
            $goods_count = M('order', 'OrderGoods')::find()->where(['order_sn' => $model->order_sn])->count();
            if ($after_count >= $goods_count) {
                $order_model = M('order', 'Order')::find()->where(['order_sn' => $model->order_sn])->one();

                $order_model->after_sales = 1;
                $order_model->finish_time = $time;

                if ($order_model->status < 203) {
                    $order_model->received_time = null;
                }
                $order_model->save();
            }
            $this->orderFinishCheck($model->order_sn);
            return $model;
        } else {
            Error('操作失败');
        }
    }

    /**
     * 修改换货物流
     */
    public function exchangeFreight()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = OrderAfter::findOne($id);
        if (empty($model)) {
            Error('售后订单不存在');
        }
        $merchant_freight_info        = Yii::$app->request->post('merchant_freight_info', []);
        $model->merchant_freight_info = to_json($merchant_freight_info);
        if ($model->save()) {
            return $model;
        } else {
            Error('修改失败');
        }
    }

    public function orderFinishCheck($order_sn)
    {
        $order_after_goods_number = M('order', 'OrderAfter')::find()->where(['order_sn' => $order_sn, 'status' => 200])->sum('return_number');
        $order_goods_number       = M('order', 'OrderGoods')::find()->where(['order_sn' => $order_sn])->sum('goods_number');

        if ($order_goods_number === $order_after_goods_number) {
            M('order', 'Order')::updateAll(['status' => 204], ['order_sn' => $order_sn]);
        }
    }

    /**
     * 管理员端订单售后事件
     * @param $model
     */
    public function orderAfter($model)
    {
        Yii::error('触发订单售后事件');
        $status = $model->status;
        Yii::error($status);
        $subscribe = null;
        switch ($status) {
            //售后通过
            case '111':
            case '121':
            case '131':
                $this->module->event->sms = [
                    'type'   => 'order_verify',
                    'mobile' => [$model->user->mobile],
                    'params' => [
                        'status' => '通过',
                    ],
                ];
                $subscribeData = [
                    'result'      => '审核通过',
                    'orderNo'     => $model->after_sn,
                    'orderAmount' => $model->order->pay_amount,
                ];
                $subscribe = new OrderSaleVerifyMessage($subscribeData);
                break;
            //售后拒绝
            case '101':
            case '201':
                $this->module->event->sms = [
                    'type'   => 'order_verify',
                    'mobile' => [$model->user->mobile],
                    'params' => [
                        'status' => '拒绝',
                    ],
                ];
                $subscribeData = [
                    'result'      => '审核不通过',
                    'orderNo'     => $model->after_sn,
                    'orderAmount' => $model->order->pay_amount,
                ];
                $subscribe = new OrderSaleVerifyMessage($subscribeData);
                break;
            //售后成功
            case '200':
                //退款成功
                if ($model->type === 0 || $model->type === 1) {
                    $this->module->event->sms = [
                        'type'   => 'order_refund_success',
                        'mobile' => [$model->user->mobile],
                        'params' => [
                            'code' => substr($model->order_sn, -4),
                        ],
                    ];

                    $subscribeData = [
                        'refundAmount' => $model->return_amount,
                        'orderNo'      => $model->order->order_sn,
                        'goodsName'    => $model->goods[0]['goods_name'],
                        'applyTime'    => date('Y年m月d日 H:i', time()),
                    ];
                    $subscribe = new OrderRefundMessage($subscribeData);
                }
                break;
            default:
                break;
        }

        $this->module->trigger('send_sms');

        if ($subscribe) {
            \Yii::$app->subscribe->setUser($model->UID)->setPage('pages/order/after-sales-details?id=' . $model->id)->send($subscribe);
        }
    }
}
