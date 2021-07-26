<?php
/**
 * 订单售后模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace order\models;

use framework\common\CommonModels;

class OrderAfter extends CommonModels
{
    const id                    = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const order_sn              = ['varchar' => 50, 'notNull', 'comment' => '订单号'];
    const after_sn              = ['varchar' => 50, 'notNull', 'comment' => '售后单号'];
    const return_sn             = ['varchar' => 50, 'comment' => '退款单号'];
    const UID                   = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const order_goods_id        = ['bigint' => 20, 'notNull', 'comment' => '订单商品ID'];
    const type                  = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '0退款 1退货退款 2换货'];
    const source                = ['varchar' => 50, 'notNull', 'comment' => '来源'];
    const return_number         = ['int' => 10, 'notNull', 'default' => 1, 'comment' => '退货数量'];
    const return_amount         = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '退款金额'];
    const return_freight        = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '退款运费'];
    const return_score          = ['bigint' => 20, 'default' => 0, 'comment' => '退款积分'];
    const return_score_type     = ['tinyint' => 1, 'default' => 0, 'comment' => '0不退积分 1退积分'];
    const actual_refund         = ['decimal' => '10,2', 'comment' => '实际退款'];
    const actual_score          = ['bigint' => 20, 'comment' => '实际退还积分'];
    const return_reason         = ['varchar' => 255, 'comment' => '退货原因'];
    const order_type            = ['varchar' => 255, 'default' => 'base', 'comment' => '订单类型'];
    const images                = ['text' => 0, 'comment' => '说明图片'];
    const status                = ['smallint' => 3, 'notNull', 'default' => 100, 'comment' => '100待审核 101首次拒绝 102再次提交待审核  111审核通过待退款 121审核通过待买家发货 122买家发货待商家收货退款 131审核通过待买家发货 132买家发货待商家收货  133商家换货(买家待收)  200售后已完成  201两次拒绝之后完成'];
    const return_address        = ['text' => 0, 'comment' => '退货地址'];
    const user_note             = ['varchar' => 255, 'comment' => '用户备注'];
    const user_freight_info     = ['text' => 0, 'comment' => '用户物流信息JSON'];
    const refuse_reason         = ['varchar' => 255, 'comment' => '拒绝原因'];
    const second_refuse_reason  = ['varchar' => 255, 'comment' => '第二次拒绝原因'];
    const merchant_note         = ['varchar' => 255, 'comment' => '商家备注'];
    const merchant_freight_info = ['text' => 0, 'comment' => '商家物流信息JSON'];
    const process               = ['text' => 0, 'comment' => '售后流程'];
    const AppID                 = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id           = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const refuse_time           = ['bigint' => 10, 'comment' => '拒绝时间'];
    const audit_time            = ['bigint' => 10, 'comment' => '审核时间'];
    const return_time           = ['bigint' => 10, 'comment' => '退款时间'];
    const salesexchange_time    = ['bigint' => 10, 'comment' => '退货时间'];
    const exchange_time         = ['bigint' => 10, 'comment' => '换货时间'];
    const finish_time           = ['bigint' => 10, 'comment' => '完成时间'];
    const created_time          = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time          = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time          = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted            = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];
    /**
     * 实现数据验证
     * 需要数据写入，必须在rules添加对应规则
     * 在控制中执行[模型]->attributes = $postData;
     * 否则会导致验证不生效，并且写入数据为空
     * @return [type] [description]
     */
    public function rules()
    {
        return [
            //创建订单
            [['order_sn', 'after_sn', 'UID', 'order_goods_id', 'type', 'return_number', 'merchant_id', 'AppID', 'source'], 'required', 'message' => '{attribute}不能为空'],
            [['UID', 'merchant_id', 'type', 'order_goods_id', 'return_number', 'status'], 'integer', 'message' => '{attribute}必须是整数'],
            [['return_amount', 'return_freight'], 'number', 'message' => '{attribute}必须是数字'],
            [['order_sn', 'user_note', 'images', 'process', 'return_reason'], 'string', 'message' => '{attribute}必须是字符串'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_after}}';
    }

    /**
     * 增加额外属性
     * @return [type] [description]
     */
    public function attributes()
    {
        $attributes = parent::attributes();
        return $attributes;
    }

    /**
     * 场景处理
     * @return [type] [description]
     */
    public function scenarios()
    {
        $scenarios           = parent::scenarios();
        $scenarios['create'] = ['order_sn', 'after_sn', 'UID', 'order_goods_id', 'return_amount', 'return_freight', 'return_number', 'merchant_id', 'type', 'user_note', 'images', 'status', 'source', 'AppID', 'return_reason', 'process', 'return_score', 'order_type', 'return_score_type'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_sn'          => '订单编号',
            'after_sn'          => '售后编号',
            'UID'               => '用户ID',
            'order_goods_id'    => '订单商品ID',
            'type'              => '售后类型',
            'return_number'     => '售后数量',
            'return_amount'     => '售后金额',
            'return_freight'    => '运费金额',
            'return_reason'     => '退货原因',
            'return_score_type' => '售后订单类型',
            'user_note'         => '用户备注',
            'source'            => '来源',
            'process'           => '流程',
        ];
    }

    /**
     * 商品信息
     * @return [type] [description]
     */
    public function getGoods()
    {
        return $this->hasMany('order\models\OrderGoods', ['order_sn' => 'order_sn'])->select('id,order_sn,freight_sn,goods_name,goods_id,goods_sn,goods_image,goods_param,show_goods_param,goods_price,goods_weight,goods_number,total_amount,pay_amount,after_sales,goods_score,score_amount');
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
        return $this->hasOne('users\models\User', ['id' => 'UID'])->select('id,nickname,mobile,avatar');
    }

    /**
     * 买家信息
     * @return [type] [description]
     */
    public function getOrder()
    {
        return $this->hasOne('order\models\Order', ['order_sn' => 'order_sn'])->select('id,order_sn,pay_type,status,pay_amount,goods_amount,freight_amount')->with('freight');
    }

}
