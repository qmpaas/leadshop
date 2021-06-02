<?php

namespace coupon\api;

use coupon\models\Coupon;
use coupon\models\UserCoupon;
use framework\common\BasicController;
use users\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

class IndexController extends BasicController
{
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        $behavior = \Yii::$app->request->get('behavior', '');
        switch ($behavior) {
            case 'user':
                return $this->userCoupon();
                break;
            default:
                return $this->list();
                Error('未知操作');
                break;
        }

    }

    public function userCoupon()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $get      = \Yii::$app->request->get();
        $UID      = $get['uid'] ?? 0;
        $where    = ['u.UID' => $UID, 'u.is_deleted' => 0];

        $status = $get['status'] ?? -1;
        switch ($status) {
            case 0:
            case 1:
            case 2:
                $where = ['and', $where, ['and', ['u.status' => $status], ['>', 'u.end_time', time()]]];
                break;
            case 3:
                //未使用已过期的才算已过期
                $where = ['and', $where, ['and', ['u.status' => 0], ['<=', 'u.end_time', time()]]];
                break;
            default:
                break;
        }

        $name = $get['name'] ?? false;
        if ($name) {
            $where = ['and', $where, ['like', 'c.name', $name]];
        }

        $query = UserCoupon::find()->alias('u')
            ->joinWith(['coupon as c' => function ($q) {
                $q->select('id,name,content,min_price,sub_price');
            }])
            ->where($where)
            ->select('u.id,u.coupon_id,u.status,u.created_time,u.use_time,u.end_time,u.order_sn')
            ->orderBy(['created_time' => SORT_DESC])
            ->asArray();

        $data = new ActiveDataProvider(
            [
                'query'      => $query,
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as &$v) {
            if ($v['end_time'] <= time() && $v['status'] === 0) {
                $v['status'] = 3;
            }
        }
        $data->setModels($list);

        return $data;
    }

    function list() {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $get      = \Yii::$app->request->get();
        $sortType = $get['sort'] ?? 0;
        switch ($sortType) {
            case 1:
                $sort = 'use_num asc';
                break;
            case 2:
                $sort = 'use_num desc';
                break;
            case 3:
                $sort = 'created_time asc';
                break;
            case 4:
            default:
                $sort = 'created_time desc';
                break;
        }
        $merchant_id = 1;
        $AppID       = \Yii::$app->params['AppID'];
        $where       = [
            'AppID'       => $AppID,
            'merchant_id' => $merchant_id,
            'is_deleted'  => 0,
        ];
        if (isset($get['name']) && $get['name']) {
            $where = ['and', $where, ['like', 'name', $get['name']]];
        }
        $type = $get['type'] ?? 'all';
        if ($type == 'can_use') {
            $where = ['and', $where, ['status' => 1]];
            $where = ['and', $where, ['>', 'over_num', 0]];
            $where = ['and', $where, [
                'or',
                ['expire_type' => 1],
                [
                    'AND',
                    ['expire_type' => 2],
                    ['>', 'end_time', time()],
                ],
            ]];
        }

        //优惠券本身筛选
        $coupon_id = $get['coupon_id'] ?? false;
        if ($coupon_id) {
            $coupon_id = explode(',', $coupon_id);
            $where     = ['and', $where, ['id' => $coupon_id]];
        }

        $begin = $get['begin_time'] ?? false;
        $end   = $get['end_time'] ?? false;
        if ($begin) {
            $where = ['and', $where, ['>=', 'created_time', $begin]];
        }
        if ($end) {
            $where = ['and', $where, ['<=', 'created_time', $end]];
        }

        $status = $get['status'] ?? -1;
        if (in_array($status, [0, 1])) {
            $where = ['AND', $where, [
                'or',
                [
                    'AND',
                    ['status' => $status],
                    ['expire_type' => 1],
                ],
                [
                    'AND',
                    ['status' => $status],
                    ['expire_type' => 2],
                    ['>', 'end_time', time()],
                ],
            ]];
        } elseif ($status == 2) {
            $where = ['AND', $where, [
                'or',
                [
                    'AND',
                    ['expire_type' => 2],
                    ['<', 'end_time', time()],
                ],
            ]];
        }

        $obtainCouponQuery = UserCoupon::find()
            ->alias('ouc')
            ->andWhere(['is_deleted' => 0])
            ->select('count(1) obtain_num, ouc.coupon_id')
            ->groupBy('ouc.coupon_id');
        $useCouponQuery = UserCoupon::find()
            ->alias('uuc')
            ->andWhere(['!=', 'order_sn', ''])
            ->andWhere(['is_deleted' => 0])
            ->select('count(1) use_num, uuc.coupon_id')
            ->groupBy('uuc.coupon_id');
        $data = new ActiveDataProvider(
            [
                'query'      => Coupon::find()->alias('c')
                    ->where($where)
                    ->leftJoin(['obc' => $obtainCouponQuery], 'obc.coupon_id = c.id')
                    ->leftJoin(['usc' => $useCouponQuery], 'usc.coupon_id = c.id')
                    ->select(['c.*'])
                    ->addSelect(["obtain_num" => "IF(obc.obtain_num, obc.obtain_num, 0)"])
                    ->addSelect(["use_num" => 'IF(usc.use_num, usc.use_num, 0)', 'over_num' => 'c.`total_num` - IF(obc.obtain_num, obc.obtain_num, 0)'])
                    ->orderBy($sort)
                    ->asArray(),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );

        $newList = [];
        $list    = $data->getModels();
        if ($list) {
            foreach ($list as $item) {
                $newItem['id']           = $item['id'];
                $newItem['name']         = $item['name'];
                $newItem['content']      = $item['content'];
                $newItem['expire_type']  = $item['expire_type'];
                $newItem['expire_day']   = $item['expire_day'];
                $newItem['min_price']    = $item['min_price'];
                $newItem['sub_price']    = $item['sub_price'];
                $newItem['begin_time']   = $item['begin_time'];
                $newItem['end_time']     = $item['end_time'];
                $newItem['status']       = $item['status'];
                $newItem['created_time'] = $item['created_time'];
                $newItem['obtain_num']   = $item['obtain_num'];
                $newItem['use_num']      = $item['use_num'];
                $newItem['over_num']     = $item['over_num'];
                $newItem['appoint_type'] = $item['appoint_type'];
                if ($item['expire_type'] == 2 && $item['end_time'] < time()) {
                    $newItem['status'] = 2;
                }
                $newList[] = $newItem;
            }
        }

        //将所有返回内容中的本地地址代替字符串替换为域名
        $newList = str2url($newList);
        $data->setModels($newList);
        return $data;
    }

    public function actionView()
    {
        $id     = \Yii::$app->request->get('id', 0);
        $coupon = Coupon::findOne(['id' => $id, 'is_deleted' => 0]);
        if (!$coupon) {
            Error('优惠券不存在');
        }
        $obtainCouponNum = UserCoupon::find()->andWhere(["coupon_id" => $coupon->id])
            ->andWhere(['is_deleted' => 0])->count();
        $newCoupon['id']            = $coupon['id'];
        $newCoupon['name']          = $coupon['name'];
        $newCoupon['total_num']     = $coupon['total_num'];
        $newCoupon['expire_type']   = $coupon['expire_type'];
        $newCoupon['expire_day']    = $coupon['expire_day'];
        $newCoupon['min_price']     = $coupon['min_price'];
        $newCoupon['sub_price']     = $coupon['sub_price'];
        $newCoupon['begin_time']    = $coupon['begin_time'];
        $newCoupon['end_time']      = $coupon['end_time'];
        $newCoupon['appoint_type']  = $coupon['appoint_type'];
        $newCoupon['give_limit']    = $coupon['give_limit'];
        $newCoupon['expire_remind'] = $coupon['expire_remind'];
        $newCoupon['enable_share']  = $coupon['enable_share'];
        $newCoupon['enable_refund'] = $coupon['enable_refund'];
        $newCoupon['over_num']      = $coupon['total_num'] - $obtainCouponNum;
        $newCoupon['content']       = $coupon['content'];
        if ($newCoupon['expire_type'] == 1) {
            $newCoupon['begin_time'] = null;
            $newCoupon['end_time']   = null;
        } elseif ($newCoupon['expire_type'] == 2) {
            $newCoupon['expire_day'] = null;
        }
        $newCoupon['appoint_data']      = $coupon->getAppointDataList()['appoint_data'];
        $newCoupon['appoint_data_list'] = $coupon->getAppointDataList()['appoint_data_list'];
        if ($coupon['expire_type'] == 2 && $coupon['end_time'] < time()) {
            $newCoupon['status'] = 2;
        } else {
            $newCoupon['status'] = $coupon['status'];
        }
        $newCoupon['created_time'] = $coupon['created_time'];
        return $newCoupon;
    }

    public function actionCreate()
    {
        $behavior = \Yii::$app->request->get('behavior', 'save');
        switch ($behavior) {
            case 'save':
                return $this->save();
                break;
            case 'send':
                return $this->grant();
                break;
            default:
                Error('无该操作');
                break;
        }
    }

    /**
     * 保存优惠券
     * @return bool
     */
    private function save()
    {
        $model               = new Coupon();
        $model->attributes   = \Yii::$app->request->post();
        $model->AppID        = \Yii::$app->params['AppID'];
        $model->merchant_id  = 1;
        $model->over_num     = $model->total_num;
        $model->appoint_data = \Yii::$app->request->post('appoint_data', []);
        if (!$model->save()) {
            Error($model->getErrorMsg());
        } else {
            return $model->id;
        }
    }

    private function grant()
    {
        $couponList = \Yii::$app->request->post('coupon_list');
        foreach ($couponList as $item) {
            if (!isset($item['id']) || !isset($item['num']) || empty($item['num'])) {
                Error('请选择优惠券和发放数量');
            }
        }
        $coupons = Coupon::find()
            ->where(['id' => array_column($couponList, 'id'), 'status' => 1, 'is_deleted' => 0])
            ->all();
        if (!$coupons) {
            Error('请选择优惠券');
        }
        $userList   = \Yii::$app->request->post('user_list', []);
        $labelList  = \Yii::$app->request->post('label_list', []);
        $users      = User::findAll(['id' => $userList]);
        $labelUsers = User::find()->alias('u')
            ->joinWith(['labellog' => function ($query) use ($labelList, $userList) {
                $query->alias('ll')->orWhere([
                    'AND',
                    ['ll.label_id' => $labelList],
                    ['not in', 'll.UID', $userList],
                ]);
            }])->all();
        $users = array_merge($users, $labelUsers);
        if (empty($users)) {
            Error('请选择用户或者用户标签');
        }
        $couponList = array_column($couponList, 'num', 'id');
        /**@var Coupon $coupon*/
        foreach ($coupons as $coupon) {
            $count = UserCoupon::find()->where(['coupon_id' => $coupon->id, 'is_deleted' => 0])->count();
            if (count($users) * $couponList[$coupon->id] > ($coupon->total_num - $count)) {
                Error($coupon->name . '剩余发放量不足---------共' . count($users) . '个用户,剩余发放总量' . ($coupon->total_num - $count) . '张,每人最多只能发放' . intval(($coupon->total_num - $count) / count($users)) . '张');
            }
        }
        $success = [];
        foreach ($coupons as $coupon) {
            try {
                $success[] = Coupon::obtain($coupon, $users, 2, $couponList[$coupon->id]);
            } catch (Exception $e) {
                \Yii::error('=====发放优惠券失败=====');
                \Yii::error($e);
                Error($e->getMessage());
            }
        }
        return $success;
    }

    public function actionUpdate()
    {
        $id       = \Yii::$app->request->get('id', false);
        $behavior = \Yii::$app->request->get('behavior', 'update');
        $coupon   = Coupon::findOne($id);
        if (!$coupon) {
            Error('优惠券不存在!');
        }
        if ($behavior == 'update') {
            $coupon->scenario      = 'update';
            $coupon->attributes    = \Yii::$app->request->post();
            $coupon->appoint_data  = \Yii::$app->request->post('appoint_data', []);
            $coupon->over_num      = $coupon->getCouponOverNum();
            $coupon->give_limit    = Yii::$app->request->post('give_limit', null);
            $coupon->expire_remind = Yii::$app->request->post('expire_remind', null);
            if (!$coupon->save()) {
                Error($coupon->getErrorMsg());
            } else {
                return true;
            }
        } elseif ($behavior == 'status') {
            $coupon->scenario = 'status';
            $coupon->status   = \Yii::$app->request->post('status');
            if (!$coupon->save()) {
                Error($coupon->getErrorMsg());
            } else {
                return true;
            }
        }
    }

    /**
     * 处理数据软删除
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $get      = \Yii::$app->request->get();
        $id       = intval($get['id']);
        $behavior = \Yii::$app->request->get('behavior', '');
        switch ($behavior) {
            case 'user':
                $model = UserCoupon::findOne($id);
                if ($model) {
                    $model->deleted_time = time();
                    $model->is_deleted   = 1;
                    if ($model->save()) {
                        return $model->is_deleted;
                    } else {
                        Error('删除失败，请检查is_deleted字段是否存在');
                    }
                } else {
                    Error('删除失败，数据不存在');
                }
                break;
            default:
                $model = Coupon::findOne($id);
                if ($model) {
                    $model->scenario     = 'delete';
                    $model->deleted_time = time();
                    $model->is_deleted   = 1;
                    if ($model->save()) {
                        return $model->is_deleted;
                    } else {
                        Error('删除失败，请检查is_deleted字段是否存在');
                    }
                } else {
                    Error('删除失败，数据不存在');
                }
                break;
        }
    }

    /**
     * 优惠券返还
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    public static function restoreUserCoupon($event)
    {
        M('coupon', 'UserCoupon')::updateAll(['use_data' => null, 'use_time' => null, 'status' => 0, 'order_sn' => null], ['order_sn' => $event->cancel_order_sn]);
    }

    /**
     * 付款自动发放优惠券
     */
    public static function sendUserCoupon($event)
    {
        $order_sn     = $event->pay_order_sn;
        $UID          = $event->pay_uid;
        $time         = time();
        $AppID        = Yii::$app->params['AppID'];
        $merchant_id  = 1;
        $goods        = M('order', 'OrderGoods')::find()->where(['order_sn' => $order_sn])->select('goods_id,goods_number')->asArray()->all();
        $goods_id     = array_unique(array_column($goods, 'goods_id'));
        $goods_number = [];
        //计算同一商品的总数
        foreach ($goods as $g) {
            if (isset($goods_number[$g['goods_id']])) {
                $goods_number[$g['goods_id']] += $g['goods_number'];
            } else {
                $goods_number[$g['goods_id']] = $g['goods_number'];
            }

        }
        $coupons     = M('goods', 'GoodsCoupon')::find()->where(['goods_id' => $goods_id])->select('goods_id,coupon_id,number')->asArray()->all();
        $coupons_id  = array_unique(array_column($coupons, 'coupon_id'));
        $coupon_list = M('coupon', 'Coupon')::find()->where(['and', ['id' => $coupons_id, 'status' => 1, 'is_deleted' => 0], ['or', ['>', 'end_time', $time], ['expire_type' => 1]]])->asArray()->all();
        $coupon_list = array_column($coupon_list, null, 'id');

        $col = ['coupon_id', 'UID', 'origin', 'begin_time', 'end_time', 'created_time', 'AppID', 'merchant_id', 'goods_id', 'origin_order_sn'];
        $row = [];
        foreach ($coupons as $v) {
            if (isset($coupon_list[$v['coupon_id']])) {
                $info = $coupon_list[$v['coupon_id']];
                //此某商品需赠送某张优惠券数量
                $coupon_number = $v['number'] * $goods_number[$v['goods_id']];
                //优惠券剩余不足时赠送所有剩余
                $number = $info['over_num'] >= $coupon_number ? $coupon_number : $info['over_num'];
                if ($info['expire_type'] == 1) {
                    $begin_time = $time;
                    $end_time   = $time + $info['expire_day'] * 86400;
                } else {
                    $begin_time = $info['begin_time'];
                    $end_time   = $info['end_time'];
                }
                for ($i = 0; $i < $number; $i++) {
                    array_push($row, [$v['coupon_id'], $UID, 3, $begin_time, $end_time, $time, $AppID, $merchant_id, $v['goods_id'], $order_sn]);
                }

                if ($number > 0) {
                    $model           = M('coupon', 'Coupon')::findOne($v['coupon_id']);
                    $model->over_num = $model->over_num - $number;
                    $model->save();
                }
            }
        }

        if (count($row) > 0) {
            $prefix     = Yii::$app->db->tablePrefix;
            $table_name = $prefix . 'user_coupon';
            $batch_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
        }

    }

    /**
     * 退款后使赠送优惠券失效
     */
    public static function invalidateUserCoupon($event)
    {
        $refunded     = $event->refunded;
        $goods_id     = M('order', 'OrderGoods')::findOne($refunded['order_goods_id'])->goods_id;
        $goods_number = M('order', 'OrderGoods')::find()->where(['order_sn' => $refunded['order_sn'], 'goods_id' => $goods_id])->sum('goods_number');

        $user_coupon = M('coupon', 'UserCoupon')::find()
            ->alias('uc')
            ->joinWith(['coupon as c' => function ($q) {
                $q->select('id,enable_refund');
            }])
            ->where(['and', ['uc.origin_order_sn' => $refunded['order_sn'], 'uc.goods_id' => $goods_id, 'c.enable_refund' => 1], ['>', 'uc.end_time', time()]])
            ->asArray()
            ->all();
        $user_coupon_number = [];
        foreach ($user_coupon as $v) {
            if (isset($user_coupon_number[$v['coupon_id']])) {
                $user_coupon_number[$v['coupon_id']]++;
            } else {
                $user_coupon_number[$v['coupon_id']] = 1;
            }

        }
        foreach ($user_coupon_number as &$number) {
            $number = ceil($number * ($refunded['return_number'] / $goods_number));
        }

        $invalidate_list = [];
        foreach ($user_coupon as $info) {
            if ($info['status'] === 0 && $user_coupon_number[$info['coupon_id']] > 0) {
                array_push($invalidate_list, $info['id']);
                $user_coupon_number[$info['coupon_id']]--;
            }

        }

        M('coupon', 'UserCoupon')::updateAll(['status' => 2], ['id' => $invalidate_list]);
    }
}
