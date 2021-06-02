<?php

namespace coupon\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%user_coupon}}".
 *
 * @property int $id ID
 * @property int $coupon_id 优惠券ID
 * @property int $UID 用户ID
 * @property string|null $order_sn 订单号
 * @property string|null $origin_order_sn 来源订单号
 * @property int $origin 来源  1:自己领取 2:商家发放 3:下单赠送
 * @property string|null $use_data 已使用的优惠券数据
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_recycle 是否在回收站
 * @property int|null $is_deleted 是否删除
 * @property int|null $goods_id 订单商品id,用于退款后失效
 * @property int|null $status 状态  0未使用  1已使用 2已失效
 * @property int|null $begin_time 有效期开始时间
 * @property int|null $end_time 有效期结束时间
 * @property int $is_remind 是否已到期提醒
 * @property Coupon $coupon 卡券对象
 */
class UserCoupon extends CommonModels
{

    const id              = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const coupon_id       = ['bigint' => 20, 'notNull', 'comment' => '优惠券ID'];
    const UID             = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const order_sn        = ['varchar' => 50, 'comment' => '使用订单号'];
    const origin_order_sn = ['varchar' => 50, 'comment' => '来源订单号'];
    const goods_id        = ['bigint' => 20, 'comment' => '订单商品id,用于退款后失效'];
    const origin          = ['tinyint' => 1, 'notNull', 'comment' => '来源  1:自己领取 2:商家发放 3:下单赠送'];
    const status          = ['tinyint' => 1, 'default' => 0, 'comment' => '状态  0未使用  1已使用 2已失效'];
    const use_data        = ['text' => 0, 'comment' => '已使用的优惠券数据'];
    const begin_time      = ['bigint' => 10, 'comment' => '有效期开始时间'];
    const end_time        = ['bigint' => 10, 'comment' => '有效期结束时间'];
    const use_time        = ['bigint' => 10, 'comment' => '使用时间'];
    const is_remind       = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '到期提醒 0否 1是'];
    const AppID           = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id     = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time    = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time    = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time    = ['bigint' => 10, 'comment' => '删除时间'];
    const is_recycle      = ['tinyint' => 1, 'default' => 0, 'comment' => '是否在回收站'];
    const is_deleted      = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_coupon}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coupon_id', 'origin', 'AppID', 'merchant_id', 'UID'], 'required'],
            [['coupon_id', 'origin', 'merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_recycle', 'is_deleted', 'UID', 'goods_id', 'status', 'begin_time', 'end_time', 'is_remind'], 'integer'],
            [['use_data'], 'string'],
            [['order_sn', 'AppID'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'coupon_id'    => '优惠券id',
            'UID'          => '用户id',
            'order_sn'     => '订单号',
            'goods_id'     => '订单商品ID',
            'origin'       => '来源',
            'status'       => '状态',
            'use_data'     => '使用数据',
            'AppID'        => 'App ID',
            'merchant_id'  => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_recycle'   => 'Is Recycle',
            'is_deleted'   => 'Is Deleted',
            'begin_time'   => 'Begin Time',
            'end_time'     => 'End Time',
        ];
    }

    public function getCoupon()
    {
        return $this->hasOne('coupon\models\Coupon', ['id' => 'coupon_id']);
    }
}
