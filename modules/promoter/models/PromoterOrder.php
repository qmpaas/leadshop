<?php
/**
 * 分销订单
 */

namespace promoter\models;

use framework\common\CommonModels;

class PromoterOrder extends CommonModels
{
    const id                = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const UID               = ['bigint' => 20, 'notNull', 'comment' => '买家ID'];
    const order_sn          = ['varchar' => 50, 'notNull', 'comment' => '订单号'];
    const order_goods_id    = ['bigint' => 20, 'notNull', 'index' => '订单商品ID', 'comment' => '订单商品ID'];
    const goods_number      = ['int' => 10, 'notNull', 'comment' => '总共商品数量'];
    const commission_number = ['int' => 10, 'notNull', 'comment' => '分佣商品数量'];
    const total_amount      = ['decimal' => '10,2', 'notNull', 'comment' => '分佣金额'];
    const profits_amount    = ['decimal' => '10,2', 'notNull', 'comment' => '分佣利润'];
    const status            = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '-1已失效 0待结算 1已结算'];
    const count_rules       = ['tinyint' => 1, 'default' => 1, 'comment' => '计算规则  1商品实付金额  2商品利润'];
    const AppID             = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id       = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time      = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time      = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time      = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted        = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promoter_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UID', 'order_sn', 'order_goods_id', 'goods_number', 'commission_number', 'total_amount', 'profits_amount', 'count_rules', 'AppID', 'merchant_id'], 'required', 'message' => '{attribute}不能为空'],
            [['UID', 'order_goods_id', 'goods_number', 'commission_number', 'merchant_id', 'count_rules', 'status'], 'integer', 'message' => '{attribute}必须是整数'],
            [['total_amount', 'profits_amount'], 'number', 'message' => '{attribute}必须是数字'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [

        ];
    }

    public function getOrderGoods()
    {
        return $this->hasOne('order\models\OrderGoods', ['id' => 'order_goods_id']);
    }

    public function getCommission(){
        return $this->hasMany('promoter\models\PromoterCommission', ['order_goods_id' => 'order_goods_id']);
    }

    /**
     * 买家信息
     * @return [type] [description]
     */
    public function getBuyer()
    {
        return $this->hasOne('order\models\OrderBuyer', ['order_sn' => 'order_sn'])->select('order_sn,note, name, mobile, province, city, district, address');
    }

    public function getUser()
    {
        return $this->hasOne('users\models\User', ['id' => 'UID'])->select('id,nickname,mobile,avatar,realname');
    }

    /**
     * 买家信息
     * @return [type] [description]
     */
    public function getOrder()
    {
        return $this->hasOne('order\models\Order', ['order_sn' => 'order_sn'])->select('id,order_sn,pay_type,status,pay_amount,source');
    }

}
