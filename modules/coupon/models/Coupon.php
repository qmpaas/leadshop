<?php

namespace coupon\models;

use framework\common\CommonModels;
use goods\models\Goods;
use goods\models\GoodsGroup;
use users\models\User;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%coupon}}".
 *
 * @property int $id ID
 * @property string $name 优惠券名称
 * @property int $type 优惠券类型：1=满减，2=折扣
 * @property float $discount 折扣 type=2时
 * @property int $total_num 发放总量
 * @property int $over_num 剩余量
 * @property int $expire_type 用券类型 1=领取后N天过期，2=指定有效期
 * @property int $expire_day 有效天数，expire_type=1时
 * @property int $begin_time 用券开始时间
 * @property int $end_time 用券结束时间
 * @property float $min_price 门槛金额
 * @property float $sub_price 优惠金额
 * @property int $appoint_type 适用商品 1:全场通用 2:指定商品可用 3:指定分类可用 4:指定商品不可用 5:指定分类不可用
 * @property string|null $appoint_data 指定数据
 * @property int $give_limit 每人限领 0无限制
 * @property int $register_limit 新客领取
 * @property int $enable_share 分享设置 1开启 0关闭
 * @property int $expire_remind 到期提醒
 * @property int $enable_refund 退款设置 1开 0关
 * @property int $status 0下架 1上架
 * @property string|null $content 使用说明
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_recycle 是否在回收站
 * @property int|null $is_deleted 是否删除
 * @property array $appoint_data_list 指定数据
 */
class Coupon extends CommonModels
{
    const id               = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const name             = ['varchar' => 100, 'notNull', 'comment' => '优惠券名称'];
    const type             = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '优惠券类型：1=满减，2=折扣'];
    const discount         = ['decimal' => '10,2', 'notNull', 'default' => 10, 'comment' => '折扣 type=2时'];
    const total_num        = ['bigint' => 10, 'notNull', 'comment' => '发放总量'];
    const over_num         = ['bigint' => 10, 'notNull', 'comment' => '剩余量'];
    const expire_type      = ['tinyint' => 1, 'notNull', 'comment' => '用券类型 1=领取后N天过期，2=指定有效期'];
    const expire_day       = ['bigint' => 10, 'notNull', 'default' => 1, 'comment' => '有效天数，expire_type=1时'];
    const begin_time       = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '用券开始时间'];
    const end_time         = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '用券结束时间'];
    const min_price        = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '门槛金额'];
    const sub_price        = ['decimal' => '10,2', 'notNull', 'comment' => '优惠金额'];
    const appoint_type     = ['tinyint' => 1, 'notNull', 'comment' => '适用商品 1:全场通用 2:指定商品可用 3:指定分类可用 4:指定商品不可用 5:指定分类不可用'];
    const appoint_data     = ['text' => 0, 'comment' => '指定数据'];
    const give_limit       = ['tinyint' => 1, 'comment' => '每人限领 0无限制'];
    const register_limit   = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '新客领取'];
    const enable_share     = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '分享设置 1开启 0关闭'];
    const expire_remind    = ['int' => 11, 'comment' => '到期提醒'];
    const enable_refund    = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '退款设置 1开 0关'];
    const status           = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '上下架状态  0下架 1上架'];
    const content          = ['text' => 0, 'comment' => '使用说明'];
    const AppID            = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id      = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time     = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time     = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time     = ['bigint' => 10, 'comment' => '删除时间'];
    const is_recycle       = ['tinyint' => 1, 'default' => 0, 'comment' => '是否在回收站'];
    const is_deleted       = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%coupon}}';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['update'] = ['name', 'total_num', 'appoint_type', 'appoint_data',
            'give_limit', 'register_limit', 'enable_share', 'expire_remind', 'enable_refund', 'content', 'status'];
        $scenarios['status'] = ['status'];
        $scenarios['delete'] = ['is_deleted', 'deleted_time'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'total_num', 'expire_type', 'sub_price', 'appoint_type', 'AppID', 'merchant_id'], 'required'],
            [['type', 'total_num', 'expire_type', 'expire_day', 'begin_time', 'end_time', 'appoint_type', 'give_limit', 'enable_share', 'expire_remind', 'enable_refund', 'status', 'merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_recycle', 'is_deleted'], 'integer'],
            [['discount', 'min_price', 'sub_price'], 'number'],
            [['content'], 'string'],
            [['appoint_data'], 'safe'],
            [['name'], 'string', 'max' => 8],
            [['AppID'], 'string', 'max' => 50],
            [['total_num'], 'integer', 'min' => 0, 'max' => 10000000],
            [['expire_day'], 'integer', 'min' => 1, 'max' => 2000],
            [['sub_price'], 'number', 'min' => 0.01, 'max' => 9999999],
            [['give_limit', 'register_limit'], 'integer', 'min' => 0, 'max' => 100],
            [['register_limit'], 'default', 'value' => 0],
            [['total_num'], function ($attribute, $params) {
                if (!$this->isNewRecord && $this->getOldAttribute($attribute) > $this->$attribute) {
                    Error('发放总量只可增加不可减少');
                }
            }],
            [['expire_type'], function ($attribute, $params) {
                if ($this->$attribute == 1 && (!isset($this->expire_day) || empty($this->expire_day))) {
                    Error('请设置有效天数');
                } elseif ($this->$attribute == 2 && (!isset($this->begin_time) || empty($this->begin_time)
                        || !isset($this->end_time) || empty($this->end_time))) {
                    Error('请设置有效日期');
                }
            }],
            [['appoint_type'], function ($attribute, $params) {
                if ($this->$attribute != 1 && (!isset($this->appoint_data) || empty($this->appoint_data))) {
                    Error('请设置指定适用商品');
                }
                if (is_array($this->appoint_data)) {
                    if (count($this->appoint_data) > 500 && ($this->appoint_type == 2 || $this->appoint_type == 4)) {
                        Error('指定商品限制500件');
                    }
                    if (count($this->appoint_data) > 30 && ($this->appoint_type == 3 || $this->appoint_type == 5)) {
                        Error('指定分类限制30个');
                    }
                    $this->appoint_data = '-' . implode('-', $this->appoint_data) . '-';
                }
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '优惠券名称',
            'type' => '优惠券类型',
            'discount' => 'Discount',
            'total_num' => '发放总量',
            'expire_type' => '用券类型',
            'expire_day' => '有效天数',
            'begin_time' => '开始时间',
            'end_time' => '结束时间',
            'min_price' => '门槛金额',
            'sub_price' => '优惠金额',
            'appoint_type' => '适用商品',
            'appoint_data' => 'Appoint Data',
            'give_limit' => '限领',
            'register_limit' => '新客领取',
            'enable_share' => '分享开关',
            'expire_remind' => '到期提醒',
            'enable_refund' => '退款设置',
            'content' => '使用说明',
            'AppID' => 'App ID',
            'merchant_id' => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_recycle' => 'Is Recycle',
            'is_deleted' => 'Is Deleted',
        ];
    }

    /**
     * 赠送优惠券
     * @param Coupon $coupon
     * @param array $userList
     * @param $origin
     * @param $num
     * @param int $checkType 1发放量不足时发完为止  2发放量不足时停止发放
     * @return array
     * @throws \yii\db\Exception
     */
    public static function obtain(Coupon $coupon, array $userList, $origin, $num, $checkType = 1)
    {
        $userCouponList = [];
        $userCouponListCopy = [];
        $t = \Yii::$app->db->beginTransaction();
        $useCouponCount = UserCoupon::find()->where(['coupon_id' => $coupon->id, 'is_deleted' => 0])->count();
        if ($checkType == 1) {
            $coupon->over_num = $coupon->total_num - $useCouponCount - $num;
            if ($coupon->over_num < 0) {
                Error('您来晚了,优惠券已被领完');
            }
        } elseif ($checkType == 2) {
            $surplus = $coupon->total_num - $useCouponCount;
            if ($surplus <= 0) {
                return $userCouponList;
            } elseif ($num > $surplus) {
                $num = $surplus;
            }
        }
        /**@var User $user*/
        foreach ($userList as $user) {
            for ($i = 0; $i < $num; $i++) {
                $userCoupon = [];
                $userCoupon['AppID'] = \Yii::$app->params['AppID'];
                $userCoupon['merchant_id'] = 1;
                $userCoupon['coupon_id'] = $coupon->id;
                $userCoupon['UID'] = $user->id;
                $userCoupon['origin'] = $origin;
                if ($coupon->expire_type == 1) {
                    $time = time();
                    $userCoupon['begin_time'] = $time;
                    $userCoupon['end_time'] = $time + $coupon->expire_day * 86400;
                } else {
                    $userCoupon['begin_time'] = $coupon->begin_time;
                    $userCoupon['end_time'] = $coupon->end_time;
                }
                $userCoupon['created_time'] = time();
                $userCouponList[] = $userCoupon;
                $userCoupon['coupon'] = ArrayHelper::toArray($coupon);
                $userCouponListCopy[] = $userCoupon;
            }
        }
        if (count($userCouponList) > 0) {
            $count = \Yii::$app->db->createCommand()->batchInsert(
                UserCoupon::tableName(),
                ['AppID', 'merchant_id', 'coupon_id', 'UID', 'origin', 'begin_time', 'end_time', 'created_time'],
                $userCouponList
            )->execute();
        }
        if (!$coupon->save()) {
            Error($coupon->getErrorMsg());
        }
        $t->commit();
        return $userCouponListCopy;
    }

    /**
     * 获取优惠券指定数据
     * @return mixed
     */
    public function getAppointDataList()
    {
        $ids = explode('-', trim($this->appoint_data, '-'));
        if ($this->expire_type == 1) {
            $this->begin_time = null;
            $this->end_time = null;
        } elseif ($this->expire_type == 2) {
            $this->expire_day = null;
        }
        switch ($this->appoint_type) {
            //指定商品可用
            case 2:
            case 4:
                $goods = Goods::find()->select(['id', 'name', 'price', 'slideshow'])->where(['id' => $ids, 'is_deleted' => 0])->asArray()->all();
                foreach ($goods as &$item) {
                    $item['slideshow'] = to_array($item['slideshow']);
                }
                unset($item);
                $newCoupon['appoint_data'] = $ids;
                $newCoupon['appoint_data_list'] = str2url($goods);
                break;
            //指定分类可用
            case 3:
            case 5:
                $newCoupon['appoint_data'] = $ids;
                $newCoupon['appoint_data_list'] = GoodsGroup::find()->select(['id', 'name'])->where(['id' => $ids, 'is_deleted' => 0])->all();
                break;
            //指定商品不可用
            //指定分类不可用
            default:
                $newCoupon['appoint_data'] = [];
                $newCoupon['appoint_data_list'] = [];
                break;
        }
        return $newCoupon;
    }

    /**
     * 实时统计优惠券剩余发放量
     * @return bool|int|string|null
     */
    public function getCouponOverNum()
    {
        $num = UserCoupon::find()->where(['coupon_id' => $this->id])->count();
        return $this->total_num - $num;
    }
}
