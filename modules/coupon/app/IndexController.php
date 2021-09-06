<?php

namespace coupon\app;

use coupon\models\Coupon;
use coupon\models\UserCoupon;
use framework\common\BasicController;
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

        $behavior = \Yii::$app->request->get('behavior', 'user');
        switch ($behavior) {
            case 'user':
                return $this->userList();
                break;
            case 'coupon':
                return $this->couponList();
                break;
            case 'tabcount':
                return $this->userCouponNum();
            default:
                Error('未知操作');
                break;
        }
    }

    /**
     * 用户优惠券列表
     */
    public function userList()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $where   = ['u.UID' => \Yii::$app->user->id, 'u.is_deleted' => 0];
        $orderBy = ['u.created_time' => SORT_DESC];

        $status = \Yii::$app->request->get('status', 0);
        if ($status) {
            $where = ['and', $where, [
                'or',
                ['!=', 'u.status', 0],
                ['<', 'u.end_time', time()],
            ]];
        } else {
            $where = ['and', $where, ['u.status' => 0], ['>', 'u.end_time', time()]];
        }

        //筛选商品可用的用户优惠券
        $goods_id = \Yii::$app->request->get('goods_id', false);
        if ($goods_id) {
            $goods_id   = array_unique(explode(',', $goods_id));
            $goods_list = M('goods', 'Goods')::find()->where(['id' => $goods_id])->select('id,group')->asArray()->all();
            if ($goods_list) {
                $where          = ['and', $where, ['<=', 'u.begin_time', time()]];
                $goods_like     = ['and'];
                $goods_not_like = ['and'];
                $group_like     = ['and'];
                $group_not_like = ['and'];
                foreach ($goods_list as $goods_info) {
                    array_push($goods_like, ['like', 'c.appoint_data', '-' . $goods_info['id'] . '-']);
                    array_push($goods_not_like, ['not like', 'c.appoint_data', '-' . $goods_info['id'] . '-']);

                    $group      = explode('-', trim($goods_info['group'], '-'));
                    $g_like     = ['or'];
                    $g_not_like = ['and'];
                    foreach ($group as $group_id) {
                        array_push($g_like, ['like', 'c.appoint_data', '-' . $group_id . '-']);
                        array_push($g_not_like, ['not like', 'c.appoint_data', '-' . $group_id . '-']);
                    }
                    array_push($group_like, $g_like);
                    array_push($group_not_like, $g_not_like);
                }
                $c_type1 = ['c.appoint_type' => 1];
                $c_type2 = ['and', ['c.appoint_type' => 2], $goods_like];
                $c_type3 = ['and', ['c.appoint_type' => 4], $goods_not_like];
                $c_type4 = ['and', ['c.appoint_type' => 3], $group_like];
                $c_type5 = ['and', ['c.appoint_type' => 5], $group_not_like];
                $where   = ['and', $where, ['or', $c_type1, $c_type2, $c_type3, $c_type4, $c_type5]];
                $orderBy = ['c.sub_price' => SORT_DESC, 'c.min_price' => SORT_ASC, 'u.created_time' => SORT_DESC];
            } else {
                Error('请选择商品');
            }
        }

        $order_sn = \Yii::$app->request->get('order_sn', false);
        if ($order_sn) {
            $where = ['and', $where, ['u.origin_order_sn' => $order_sn]];
        }

        $query = UserCoupon::find()
            ->alias('u')
            ->where($where)
            ->joinWith([
                'coupon as c' => function ($q) {
                    $q->select('id,name,enable_share,appoint_type,min_price,sub_price,expire_type,expire_day,over_num');
                },
            ])
            ->select('u.id,u.begin_time,u.end_time,u.status,u.coupon_id,u.created_time,u.use_data')
            ->orderBy($orderBy)
            ->asArray();

        $type = \Yii::$app->request->get('type', false);
        if ($type) {
            $data = $query->all();
        } else {
            $data = new ActiveDataProvider(
                [
                    'query'      => $query,
                    'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
                ]
            );

            $list = $data->getModels();
            if ($list) {
                /**@var UserCoupon $item*/
                foreach ($list as &$item) {
                    if ($item['status'] == 0) {
                        if ($item['end_time'] < time()) {
                            $item['status'] = 3;
                        }
                    } else {
                        $content                = json_decode($item['use_data'], true);
                        $item['coupon']['name'] = $content['name'] ?? $item['coupon']['name'];
                    }
                    if ($item['coupon']['over_num'] <= 0) {
                        $item['coupon']['enable_share'] = false;
                    }
                }
                unset($item);
            }
            $data->setModels($list);
        }
        return $data;
    }

    /**
     * 优惠券列表
     */
    public function couponList()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $AppID    = \Yii::$app->params['AppID'];
        $where    = ['and', ['AppID' => $AppID, 'is_deleted' => 0, 'status' => 1], ['>', 'over_num', 0], ['or', ['expire_type' => 1], ['and', ['expire_type' => 2], ['>', 'end_time', time()]]]];
        $orderBy  = ['created_time' => SORT_DESC];

        //筛选商品可用优惠券
        $goods_id = \Yii::$app->request->get('goods_id', 0);
        if ($goods_id) {
            $goods_info = M('goods', 'Goods')::findOne($goods_id);
            if ($goods_info) {
                $c_type1    = ['appoint_type' => 1];
                $c_type2    = ['and', ['appoint_type' => 2], ['like', 'appoint_data', '-' . $goods_id . '-']];
                $c_type3    = ['and', ['appoint_type' => 4], ['not like', 'appoint_data', '-' . $goods_id . '-']];
                $group      = explode('-', trim($goods_info->group, '-'));
                $g_like     = ['or'];
                $g_not_like = ['and'];
                foreach ($group as $group_id) {
                    array_push($g_like, ['like', 'appoint_data', '-' . $group_id . '-']);
                    array_push($g_not_like, ['not like', 'appoint_data', '-' . $group_id . '-']);
                }
                $c_type4 = ['and', ['appoint_type' => 3], $g_like];
                $c_type5 = ['and', ['appoint_type' => 5], $g_not_like];
                $where   = ['and', $where, ['or', $c_type1, $c_type2, $c_type3, $c_type4, $c_type5]];
            } else {
                Error('商品不存在');
            }
            $orderBy = ['min_price' => SORT_ASC, 'sub_price' => SORT_DESC, 'created_time' => SORT_DESC];
        }

        //优惠券本身筛选
        $coupon_id = \Yii::$app->request->get('coupon_id', false);
        if ($coupon_id) {
            $coupon_id = explode(',', $coupon_id);
            $where     = ['and', $where, ['id' => $coupon_id]];
        }

        $query = Coupon::find()->where($where)->select('id,name,enable_share,appoint_type,min_price,sub_price,expire_type,expire_day,over_num,give_limit')->orderBy($orderBy)->asArray();

        $type = \Yii::$app->request->get('type', false);
        if ($type) {
            $list = $query->all();
        } else {
            $data = new ActiveDataProvider(
                [
                    'query'      => $query,
                    'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
                ]
            );
            $list = $data->getModels();
        }

        $myCoupon = $this->userOwned();
        if ($list) {
            /**@var UserCoupon $item*/
            foreach ($list as &$item) {
                $item['can_obtain'] = true;
                if (isset($myCoupon[$item['id']]) && $myCoupon[$item['id']] >= $item['give_limit'] && $item['give_limit'] != null) {
                    if ($myCoupon >= $item['give_limit']) {
                        $item['can_obtain'] = false;
                    }
                }
            }
        }
        if ($type) {
            return $list;
        } else {
            $data->setModels($list);
            return $data;
        }

    }

    /**
     * 获取当前用户领过的所有优惠券的数量,用于判断是否可再领取
     */
    public function userOwned()
    {
        $data = [];
        if (\Yii::$app->user->id) {
            $list = UserCoupon::find()->where(['UID' => \Yii::$app->user->id, 'origin' => 1, 'is_deleted' => 0])->select('count(1) num,coupon_id')->groupBy('coupon_id')->asArray()->all();
            foreach ($list as $v) {
                $data[$v['coupon_id']] = $v['num'];
            }
        }
        return $data;
    }

    /**
     * 领取优惠券
     * @return bool
     */
    public function actionCreate()
    {
        $id = \Yii::$app->request->post('id');
        /**@var Coupon $coupon*/
        $coupon = Coupon::find()->where(['id' => $id, 'is_deleted' => 0])->one();
        if (!$coupon) {
            Error('优惠券已失效');
        }
        if ($coupon->status == 0 || ($coupon->expire_type == 2 && $coupon->end_time < time())) {
            Error('不好意思,优惠券已失效');
        }
        $userCoupon = UserCoupon::find()->where(['coupon_id' => $coupon->id, 'is_deleted' => 0])->count();
        if ($coupon->total_num - $userCoupon <= 0) {
            Error('您来晚了,优惠券已被领完');
        }
        $myCoupon = UserCoupon::find()->where(['coupon_id' => $coupon->id, 'origin' => 1, 'UID' => \Yii::$app->user->id, 'is_deleted' => 0])->count();
        if (($myCoupon >= $coupon->give_limit) && ($coupon->give_limit != null)) {
            Error('无剩余领取次数');
        }
        try {
            Coupon::obtain($coupon, [\Yii::$app->user], 1, 1);
        } catch (Exception $e) {
            Error('系统出错');
        }
        return true;
    }

    public function actionView()
    {
        $behavior = \Yii::$app->request->get('behavior', 'user');
        switch ($behavior) {
            case "user":
                return $this->userCoupon();
                break;
            case "coupon":
                return $this->coupon();
                break;
        }

    }

    /**
     * 用户优惠券详情
     * @return mixed
     */
    private function userCoupon()
    {
        $id = \Yii::$app->request->get('id');
        /**@var UserCoupon $userCoupon*/
        $userCoupon = UserCoupon::find()->where(['id' => $id, 'UID' => \Yii::$app->user->id])->with(['coupon'])->one();
        if (!$userCoupon) {
            Error('优惠券不存在');
        }
        $newItem['coupon_id']         = $userCoupon->coupon_id;
        $newItem['enable_share']      = $userCoupon->coupon->enable_share;
        $newItem['begin_time']        = $userCoupon->begin_time;
        $newItem['end_time']          = $userCoupon->end_time;
        $newItem['min_price']         = $userCoupon->coupon->min_price;
        $newItem['sub_price']         = $userCoupon->coupon->sub_price;
        $newItem['status']            = $userCoupon->status;
        $newItem['appoint_type']      = $userCoupon->coupon->appoint_type;
        $newItem['appoint_data_list'] = $userCoupon->coupon->getAppointDataList()['appoint_data_list'];
        if ($userCoupon->status == 0) {
            if ($userCoupon->end_time < time()) {
                $newItem['status'] = 3;
            }
            $newItem['name']    = $userCoupon->coupon->name;
            $newItem['content'] = $userCoupon->coupon->content;
        } else {
            $content            = json_decode($userCoupon->use_data, true);
            $newItem['name']    = $content['name'] ?? $userCoupon->coupon->name;
            $newItem['content'] = $content['content'] ?? $userCoupon->coupon->content;
        }
        return $newItem;
    }

    /**
     * 优惠券详情
     * @return mixed
     */
    private function coupon()
    {
        $id = \Yii::$app->request->get('id');
        /**@var Coupon $coupon*/
        $coupon = Coupon::find()->where(['id' => $id])->one();
        if (!$coupon) {
            Error('优惠券不存在');
        }
        $newItem['id']                = $coupon->id;
        $newItem['name']              = $coupon->name;
        $newItem['can_obtain']        = true;
        $newItem['expire_type']       = $coupon->expire_type;
        $newItem['expire_day']        = $coupon->expire_day;
        $newItem['begin_time']        = $coupon->begin_time;
        $newItem['end_time']          = $coupon->end_time;
        $newItem['min_price']         = $coupon->min_price;
        $newItem['appoint_type']      = $coupon->appoint_type;
        $newItem['appoint_data']      = $coupon->getAppointDataList()['appoint_data'];
        $newItem['appoint_data_list'] = $coupon->getAppointDataList()['appoint_data_list'];
        $newItem['sub_price']         = $coupon->sub_price;
        $newItem['content']           = $coupon->content;
        $newItem['enable_share']      = $coupon->enable_share;
        $newItem['status']            = $coupon->status;
        if ($coupon->expire_type == 2 && $coupon->end_time < time()) {
            $newItem['status'] = 3;
        }
        if (\Yii::$app->user->id) {
            $myCoupon = UserCoupon::find()->where(['coupon_id' => $coupon->id, 'UID' => \Yii::$app->user->id, 'is_deleted' => 0, 'origin' => 1])->count();
            if (($myCoupon >= $coupon->give_limit) && ($coupon->give_limit != null)) {
                $newItem['can_obtain'] = false;
            }
        }
        return $newItem;
    }

    private function userCouponNum()
    {
        $canUseCount = UserCoupon::find()->where([
            'AND',
            ['UID' => \Yii::$app->user->id],
            ['>', 'end_time', time()],
            ['status' => 0],
            ['is_deleted' => 0],
        ])->count();
        return ['can_use_coupon_num' => $canUseCount];
    }
}
