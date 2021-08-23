<?php
/**
 * 订单控制器
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace order\api;

use app\components\subscribe\OrderSendMessage;
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
        $id       = Yii::$app->request->get('id', false);
        $order_sn = Yii::$app->request->get('order_sn', false);
        $where    = ['is_recycle' => 0];
        if ($id) {
            $where = ['and', $where, ['id' => $id]];
        }
        if ($order_sn) {
            $where = ['and', $where, ['order_sn' => $order_sn]];
        }

        $result = M()::find()
            ->where($where)
            ->with([
                'buyer',
                'goods',
                'user',
                'freight' => function ($q) {
                    $q->with('goods');
                },
            ])
            ->asArray()
            ->one();

        if (empty($result)) {
            Error('订单不存在');
        }
        foreach ($result['goods'] as &$goods) {
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
        $result                 = str2url($result);
        $result['goods_amount'] = $result['goods_amount'] + $result['coupon_reduced'];
        return $result;
    }

    public function actionTabcount()
    {
        //商品分组
        $keyword = Yii::$app->request->post('keyword', []);

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['order.merchant_id' => $merchant_id, 'order.AppID' => $AppID];

        //订单来源筛选
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['order.source' => $source]];
        }

        //支付方式
        $pay_type = $keyword['pay_type'] ?? false;
        if ($pay_type) {
            $where = ['and', $where, ['order.pay_type' => $pay_type]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'order.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'order.created_time', $time_end]];
        }

        $search_key = $keyword['search_key'] ?? false;
        $search     = $keyword['search'] ?? '';

        //订单编号
        if ($search_key == 'order_sn' && $search) {
            $where = ['and', $where, ['like', 'order.order_sn', $search]];
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

        //商品名称
        if ($search_key == 'goods_name' && $search) {
            $where = ['and', $where, ['like', 'goods.goods_name', $search]];
        }
        //商品货号
        if ($search_key == 'goods_sn' && $search) {
            $where = ['and', $where, ['like', 'goods.goods_sn', $search]];
        }

        $data_list = ['all' => [], 'unpaid' => [], 'unsent' => [], 'unreceived' => [], 'received' => [], 'finished' => [], 'closed' => [], 'recycle' => []];

        foreach ($data_list as $key => &$value) {
            switch ($key) {
                case 'unpaid': //待付款
                    $w = ['order.status' => 100, 'order.is_recycle' => 0];
                    break;
                case 'unsent': //待发货
                    $w = ['order.status' => 201, 'order.is_recycle' => 0];
                    break;
                case 'unreceived': //待收货
                    $w = ['order.status' => 202, 'order.is_recycle' => 0];
                    break;
                case 'received': //已收货
                    $w = ['order.status' => 203, 'order.is_recycle' => 0];
                    break;
                case 'finished': //已完成
                    $w = ['order.status' => 204, 'order.is_recycle' => 0];
                    break;
                case 'closed': //已关闭
                    $w = ['order.status' => [101, 102, 103], 'order.is_recycle' => 0];
                    break;
                case 'recycle': //回收站
                    $w = ['order.is_recycle' => 1, 'order.is_deleted' => 0];
                    break;
                default: //默认获取全部
                    $w = ['order.is_recycle' => 0];
                    break;
            }

            $w     = ['and', $where, $w];
            $value = M()::find()
                ->alias('order')
                ->joinWith([
                    'buyer as buyer',
                    'goods as goods',
                    'user as user',
                ])
                ->where($w)
                ->groupBy(['order.id'])
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
            case 'unpaid': //待付款
                $where = ['order.status' => 100, 'order.is_recycle' => 0];
                break;
            case 'unsent': //待发货
                $where = ['order.status' => 201, 'order.is_recycle' => 0];
                break;
            case 'unreceived': //待收货
                $where = ['order.status' => 202, 'order.is_recycle' => 0];
                break;
            case 'received': //已收货
                $where = ['order.status' => 203, 'order.is_recycle' => 0];
                break;
            case 'finished': //已完成
                $where = ['order.status' => 204, 'order.is_recycle' => 0];
                break;
            case 'closed': //已关闭
                $where = ['order.status' => [101, 102, 103], 'order.is_recycle' => 0];
                break;
            case 'recycle': //回收站
                $where = ['order.is_recycle' => 1, 'order.is_deleted' => 0];
                break;

            default: //默认获取全部
                $where = ['order.is_recycle' => 0];
                break;
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['and', $where, ['order.merchant_id' => $merchant_id, 'order.AppID' => $AppID]];

        //订单来源筛选
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['order.source' => $source]];
        }

        //订单类型
        $type = $keyword['type'] ?? '';
        if ($type) {
            $where = ['and', $where, ['order.type' => $type]];
        }

        //支付方式
        $pay_type = $keyword['pay_type'] ?? false;
        if ($pay_type) {
            $where = ['and', $where, ['order.pay_type' => $pay_type]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'order.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'order.created_time', $time_end]];
        }

        $search_key = $keyword['search_key'] ?? false;
        $search     = $keyword['search'] ?? '';

        //订单编号
        if ($search_key == 'order_sn' && $search) {
            $where = ['and', $where, ['like', 'order.order_sn', $search]];
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

        //商品名称
        if ($search_key == 'goods_name' && $search) {
            $where = ['and', $where, ['like', 'goods.goods_name', $search]];
        }
        //商品货号
        if ($search_key == 'goods_sn' && $search) {
            $where = ['and', $where, ['like', 'goods.goods_sn', $search]];
        }

        //处理排序
        $sort    = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['order.created_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                $orderBy['order.' . $key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
            }
        }

        $data = new ActiveDataProvider(
            [
                'query'      => M()::find()
                    ->alias('order')
                    ->joinWith([
                        'buyer as buyer',
                        'goods as goods',
                        'user as user',
                        'freight as freight',
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

    /**
     * 订单详情
     * @return [type] [description]
     */
    public function actionView()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'order_goods':
                return $this->orderGoodsInfo();
                break;
            case 'order_bag':
                return $this->orderBag();
                break;
            default:
                return $this->orderInfo();
                break;
        }
    }

    private function orderGoodsInfo()
    {
        $id    = Yii::$app->request->get('id', false);
        $where = ['is_recycle' => 0, 'id' => $id];

        $result = M()::find()
            ->where($where)
            ->with([
                'buyer',
                'goods' => function ($q) {
                    $q->with('bag');
                },
            ])
            ->select('id,order_sn,status')
            ->asArray()
            ->one();

        if (empty($result)) {
            Error('订单不存在');
        }

        $bag_name_id = M('order', 'OrderFreight')::find()->where(['order_sn' => $result['order_sn']])->orderBy(['id' => SORT_ASC])->select('id')->all();
        $bag_name_id = array_column($bag_name_id, 'id');
        foreach ($result['goods'] as &$goods) {
            $goods['send_number'] = 0;
            if (!empty($goods['bag'])) {
                foreach ($goods['bag'] as &$v) {
                    $v['bag_name_num'] = array_search($v['freight_id'], $bag_name_id) + 1;
                    $goods['send_number'] += $v['bag_goods_number'];
                }
            }
            $goods['wait_number'] = $goods['goods_number'] - $goods['send_number'];

        }

        $result = str2url($result);
        return $result;
    }

    private function orderBag()
    {
        $id    = Yii::$app->request->get('id', false);
        $where = ['is_recycle' => 0, 'id' => $id];

        $result = M()::find()
            ->where($where)
            ->with([
                'buyer',
                'freight' => function ($q) {
                    $q->with('goods');
                },
            ])
            ->select('id,order_sn,status')
            ->asArray()
            ->one();

        if (empty($result)) {
            Error('订单不存在');
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

        $result = str2url($result);
        return $result;
    }

    private function orderInfo()
    {
        $id    = Yii::$app->request->get('id', false);
        $where = ['is_recycle' => 0, 'id' => $id];

        $result = M()::find()
            ->where($where)
            ->with([
                'buyer',
                'goods',
                'user',
                'freight' => function ($q) {
                    $q->with('goods');
                },
            ])
            ->asArray()
            ->one();

        if (empty($result)) {
            Error('订单不存在');
        }
        foreach ($result['goods'] as &$goods) {
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
        $result                 = str2url($result);
        $result['goods_amount'] = $result['goods_amount'] + $result['coupon_reduced'];
        return $result;
    }

    /**
     * 重写刪除
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = M()::findOne($id);
        if ($model) {

            //只有关闭和完成的订单可以删除
            if (!in_array($model->status, [101, 102, 103, 204])) {
                Error('该订单不能删除');
            }
            $model->is_recycle = 1;
            if ($model->save()) {
                return $model->is_recycle;
            } else {
                Error('删除失败');
            }
        } else {
            Error('删除失败');
        }

    }

    /**
     * 回收站恢复
     * @return [type] [description]
     */
    public function actionRestore()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = M()::findOne($id);
        if ($model) {
            $model->is_recycle = 0;
            if ($model->save()) {
                return true;
            } else {
                Error('恢复失败');
            }
        } else {
            Error('恢复失败');
        }

    }

    /**
     * 商家彻底删除
     * @return [type] [description]
     */
    public function actionRemove()
    {
        $id = Yii::$app->request->get('id', false);

        $data = [
            'is_deleted'   => 1,
            'deleted_time' => time(),
        ];

        $result = M()::updateAll($data, ['is_recycle' => 1, 'id' => $id]);

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
            case 'note': //商家备注
                return $this->note();
                break;
            case 'cancel': //取消订单
                return $this->cancel();
                break;
            case 'send': //确认发货
                return $this->send();
                break;
            case 'editfreight': //修改物流
                return $this->editfreight();
                break;
            case 'received': //确认收货
                return $this->received();
                break;
            case 'amount': //修改支付金额
                return $this->amount();
                break;
            case 'consignee': //修改收货人
                return $this->consignee();
                break;
            default:
                Error('未定义操作');
                break;
        }
    }

    /**
     * 商户备注
     * @return [type] [description]
     */
    public function note()
    {
        $id   = Yii::$app->request->get('id', false);
        $note = Yii::$app->request->post('note', false);
        if (!$note) {
            Error('备注内容不能为空');
        }
        $model = M()::findOne($id);
        if (empty($model)) {
            Error('订单不存在');
        }

        $model->note = $note;

        if ($model->save()) {
            return true;
        } else {
            Error('操作失败');
        }
    }

    /**
     * 商户取消订单
     * @return [type] [description]
     */
    public function cancel()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = M()::findOne($id);
        if (empty($model)) {
            Error('订单不存在');
        }

        //只有未付款的订单可以取消
        if ($model->status !== 100) {
            Error('非法操作');
        }
        $model->status      = 103;
        $model->cancel_time = time();

        if ($model->save()) {
            //执行取消订单事件
            $order_goods                             = M('order', 'OrderGoods')::find()->where(['order_sn' => $model->order_sn])->select('goods_id,goods_param,goods_number')->asArray()->all();
            $this->module->event->cancel_order_goods = $order_goods;
            $this->module->event->cancel_order_sn    = $model->order_sn;
            $this->module->trigger('cancel_order');
            return ['status' => $model->status];
        } else {
            Error('操作失败');
        }
    }

    /**
     * 发货
     * @return [type] [description]
     */
    public function send()
    {
        $id   = Yii::$app->request->get('id', false);
        $post = Yii::$app->request->post();

        if (empty($post['goods_list'])) {
            Error('请选择商品');
        }

        $model = M()::findOne($id);
        if (empty($model)) {
            Error('订单不存在');
        }

        //只有付款商品才能发货
        if ($model->status !== 201) {
            Error('非法操作');
        }
        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务

        $post['order_sn'] = $model->order_sn;
        $freight_model    = M('order', 'OrderFreight', true);
        $freight_model->setAttributes($post);
        if ($freight_model->validate()) {
            if ($freight_model->save()) {

                $order_goods         = M('order', 'OrderGoods')::find()->where(['order_sn' => $model->order_sn])->select('id,goods_number')->asArray()->all();
                $order_freight_goods = M('order', 'OrderFreightGoods')::find()->where(['order_goods_id' => array_column($order_goods, 'id')])->select('order_goods_id,bag_goods_number')->asArray()->all();

                $all_goods_number      = 0; //商品总数
                $all_bag_goods_number  = 0; //包裹中总数
                $all_post_goods_number = 0; //此次发货数
                foreach ($order_goods as &$o_g) {
                    $all_goods_number += $o_g['goods_number'];
                    $o_g['send_number'] = 0;
                }
                $order_goods = array_column($order_goods, null, 'id');

                foreach ($order_freight_goods as $o_f_g) {
                    $all_bag_goods_number += $o_f_g['bag_goods_number'];
                    $order_goods[$o_f_g['order_goods_id']]['send_number'] += $o_f_g['bag_goods_number'];
                }

                $row        = [];
                $col        = ['freight_id', 'order_goods_id', 'bag_goods_number', 'created_time'];
                $time       = time();
                $freight_id = $freight_model->id;
                foreach ($post['goods_list'] as $g) {
                    if (!$g['number']) {
                        $transaction->rollBack(); //事务回滚
                        Error('请输入商品数量');
                    }
                    $all_post_goods_number += $g['number'];
                    $g_id = $g['order_goods_id'];
                    if (($order_goods[$g_id]['send_number'] + $g['number']) > $order_goods[$g_id]['goods_number']) {
                        $transaction->rollBack(); //事务回滚
                        Error('发货数量超额');
                    }
                    array_push($row, [$freight_id, $g_id, $g['number'], $time]);
                }

                $prefix     = Yii::$app->db->tablePrefix;
                $table_name = $prefix . 'order_freight_goods';
                $o_f_g_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
                if ($o_f_g_res == count($row)) {

                    //全部发完则订单成为已发货状态
                    if ($all_goods_number <= ($all_bag_goods_number + $all_post_goods_number)) {
                        $setting_data = M('setting', 'Setting')::find()->where(['keyword' => 'setting_collection', 'merchant_id' => $model->merchant_id, 'AppID' => $model->AppID])->select('content')->asArray()->one();
                        if ($setting_data) {
                            $setting_data['content'] = to_array($setting_data['content']);
                            if (isset($setting_data['content']['trade_setting'])) {
                                $trade_setting = $setting_data['content']['trade_setting'];
                                if ($trade_setting['received_time']) {
                                    $model->received_time = (float) $trade_setting['received_time'] * 24 * 60 * 60 + time();
                                }
                            }
                        }
                        $model->send_time = $time;
                        $model->status    = 202;
                        $res              = $model->save();
                        if ($res) {
                            $this->module->event->sms = [
                                'type'   => 'order_send',
                                'mobile' => [$model->user->mobile],
                                'params' => [
                                    'code' => substr($model->order_sn, -4),
                                ],
                            ];
                            $this->module->trigger('send_sms');

                            if ($freight_model->type == 1 || $freight_model->type == 3) {
                                $message = new OrderSendMessage([
                                    'expressName' => $freight_model->logistics_company,
                                    'expressNo'   => $freight_model->freight_sn,
                                    'address'     => $model->buyer->address,
                                    'orderNo'     => $model->order_sn,
                                ]);
                            } else {
                                $message = new OrderSendMessage([
                                    'expressName' => '无物流',
                                    'expressNo'   => '--',
                                    'address'     => $model->buyer->address,
                                    'orderNo'     => $model->order_sn,
                                ]);
                            }
                            \Yii::$app->subscribe
                                ->setUser($model->UID)
                                ->setPage('pages/order/detail?id=' . $model->id)
                                ->send($message);
                        } else {
                            $transaction->rollBack(); //事务回滚
                            Error('发货失败');
                        }

                    }
                    $transaction->commit(); //事务执行
                    return ['status' => $model->status];
                } else {
                    $transaction->rollBack(); //事务回滚
                    Error('发货失败');
                }
            }
        } else {
            return $freight_model;
        }

    }

    /**
     * 修改订单物流信息
     * @return [type] [description]
     */
    public function editfreight()
    {
        $id    = Yii::$app->request->post('id', false);
        $post  = Yii::$app->request->post();
        $model = M('order', 'OrderFreight')::findOne($id);
        if (empty($model)) {
            Error('物流不存在');
        }

        $order_model = M()::find()->where(['order_sn' => $model->order_sn])->one();

        //只有在已发货状态下可以修改物流
        if ($order_model->status !== 201 && $order_model->status !== 202) {
            Error('非法操作');
        }

        $model->setAttributes($post);
        if ($model->validate()) {
            if ($model->save()) {
                return true;
            } else {
                Error('操作失败');
            }
        } else {
            return $model;
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
        //只有在发货后可以收货
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
            return ['status' => $model->status];
        } else {
            Error('操作失败');
        }
    }

    /**
     * 修改订单金额
     * @return [type] [description]
     */
    public function amount()
    {
        $id   = Yii::$app->request->get('id', false);
        $post = Yii::$app->request->post();

        $model = M()::findOne($id);
        if (empty($model)) {
            Error('订单不存在');
        }
        if ($model->status !== 100) {
            Error('非法操作');
        }

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        if (isset($post['goods_amount']) && $post['goods_amount'] >= 0) {

            $goods_amount = $model->goods_reduced + $model->goods_amount + $model->coupon_reduced;

            $order_goods_list = M('order', 'OrderGoods')::find()->where(['order_sn' => $model->order_sn])->select('id,total_amount')->asArray()->all();

            foreach ($order_goods_list as $v) {
                $pay_amount = $goods_amount <= 0 ? 0 : round($v['total_amount'] * ($post['goods_amount'] / $goods_amount), 2);
                M('order', 'OrderGoods')::updateAll(['pay_amount' => $pay_amount], ['id' => $v['id']]);
            }
            $model->goods_reduced = $goods_amount - $post['goods_amount'] - $model->coupon_reduced;
            $model->goods_amount  = $post['goods_amount'];
        } else {
            Error('商品价格不符合要求');
        }

        if (isset($post['freight_amount']) && $post['freight_amount'] >= 0) {
            $model->freight_reduced = $model->freight_reduced + $model->freight_amount - $post['freight_amount'];
            $model->freight_amount  = $post['freight_amount'];
        } else {
            Error('运费不符合要求');
        }

        $model->pay_amount = $model->goods_amount + $model->freight_amount;

        if ($model->save()) {
            $transaction->commit();
            return ['pay_amount' => $model->pay_amount, 'freight_amount' => $model->freight_amount, 'goods_amount' => $model->goods_amount];
        } else {
            $transaction->rollBack();
            Error('操作失败');
        }
    }

    /**
     * 修改物流
     * @return [type] [description]
     */
    public function consignee()
    {
        $order_sn       = Yii::$app->request->post('order_sn', false);
        $consignee_info = Yii::$app->request->post('consignee_info', []);
        $model          = M('order', 'OrderBuyer')::find()->where(['order_sn' => $order_sn])->one();
        if (empty($model)) {
            Error('订单不存在');
        }
        $model->setScenario('update');
        $model->setAttributes($consignee_info);
        if ($model->validate()) {
            if ($model->save()) {
                return true;
            } else {
                Error('操作失败');
            }
        }
        return $model;
    }

    //还没有加列队,临时凑合着用
    public static function checkOrder()
    {
        $AppID         = Yii::$app->params['AppID'];
        $time          = time();
        $finish_time   = null;
        $evaluate_time = null;
        $setting_data  = M('setting', 'Setting')::find()->where(['keyword' => 'setting_collection', 'AppID' => $AppID])->select('content')->asArray()->one();
        if ($setting_data) {
            $setting_data['content'] = to_array($setting_data['content']);
            if (isset($setting_data['content']['trade_setting'])) {
                $trade_setting = $setting_data['content']['trade_setting'];
                if ($trade_setting['after_time']) {
                    $finish_time = (float) $trade_setting['after_time'] * 24 * 60 * 60 + $time;
                }
                if ($trade_setting['evaluate_time']) {
                    $evaluate_time = (float) $trade_setting['evaluate_time'] * 24 * 60 * 60 + $time;
                }
                if ($trade_setting['cancel_status']) {
                    $cancel_list = M('order', 'Order')::find()->where(['and', ['AppID' => $AppID, 'status' => 100], ['<=', 'cancel_time', $time]])->select('order_sn')->asArray()->all();
                    if (!empty($cancel_list)) {
                        $cancel_list = array_column($cancel_list, 'order_sn');
                        M('order', 'Order')::updateAll(['status' => 102], ['order_sn' => $cancel_list]);
                        $order_goods = M('order', 'OrderGoods')::find()->where(['order_sn' => $cancel_list])->select('goods_id,goods_param,goods_number')->asArray()->all();
                        foreach ($order_goods as $value) {
                            M('goods', 'GoodsData')::updateAllCounters(['stocks' => $value['goods_number']], ['goods_id' => $value['goods_id'], 'param_value' => $value['goods_param']]);
                            M('goods', 'Goods')::updateAllCounters(['stocks' => $value['goods_number']], ['id' => $value['goods_id']]);
                        }
                        M('coupon', 'UserCoupon')::updateAll(['use_data' => null, 'use_time' => null, 'status' => 0, 'order_sn' => null], ['order_sn' => $cancel_list]);
                    }
                }
            }
        }
        $received_list = M('order', 'Order')::find()->where(['and', ['AppID' => $AppID, 'status' => 202], ['<=', 'received_time', $time]])->select('order_sn')->asArray()->all();
        if (!empty($received_list)) {
            $received_list = array_column($received_list, 'order_sn');
            M('order', 'Order')::updateAll(['status' => 203, 'finish_time' => $finish_time, 'evaluate_time' => $evaluate_time], ['order_sn' => $received_list]);
        }

        $finish_list = M('order', 'Order')::find()->where(['and', ['AppID' => $AppID, 'status' => 203, 'after_sales' => 0], ['<=', 'finish_time', $time]])->select('order_sn')->asArray()->all();
        if (!empty($finish_list)) {
            $finish_list = array_column($finish_list, 'order_sn');
            M('order', 'Order')::updateAll(['status' => 204], ['order_sn' => $finish_list]);
        }
    }

}
