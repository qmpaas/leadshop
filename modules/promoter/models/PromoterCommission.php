<?php
/**
 * 分销佣金记录
 */
namespace promoter\models;

use framework\common\CommonModels;

class PromoterCommission extends CommonModels
{
    const id             = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const beneficiary    = ['bigint' => 20, 'notNull', 'index' => '受益人ID', 'comment' => '受益人ID'];
    const order_goods_id = ['bigint' => 20, 'notNull', 'index' => '分销订单ID', 'comment' => '分销订单ID'];
    const commission     = ['decimal' => '10,2', 'default' => 0, 'comment' => '佣金'];
    const sales_amount   = ['decimal' => '10,2', 'default' => 0, 'comment' => '销售金额'];
    const level          = ['tinyint' => 1, 'default' => 1, 'comment' => '佣金等级'];
    const created_time   = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time   = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time   = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted     = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promoter_commission}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

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

    public function getPromoterOrder()
    {
        return $this->hasOne('promoter\models\PromoterOrder', ['order_goods_id' => 'order_goods_id']);
    }

    public function getUser()
    {
        return $this->hasOne('users\models\User', ['id' => 'beneficiary'])->select('id,nickname');
    }

}
