<?php
/**
 * 订单模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace order\models;

use framework\common\CommonModels;

class Order extends CommonModels
{
    const id              = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const order_sn        = ['varchar' => 50, 'notNull', 'comment' => '订单号'];
    const UID             = ['bigint' => 20, 'notNull', 'comment' => '买家ID'];
    const total_amount    = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '总计价格'];
    const pay_amount      = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '支付价格'];
    const score_amount    = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '积分支付'];
    const total_score     = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '积分统计'];
    const type            = ['varchar' => 255, 'default' => '', 'comment' => '订单类型 base 基础订单 task 任务订单'];
    const goods_amount    = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '商品金额'];
    const goods_reduced   = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '商品减少'];
    const freight_amount  = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '物流金额'];
    const freight_reduced = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '物流减少'];
    const coupon_reduced  = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '优惠券优惠金额'];
    const status          = ['smallint' => 3, 'notNull', 'default' => 100, 'comment' => '100待付款  101用户取消 102超时取消 103商户取消  201已付款(待发货)  202已发货(待收货)  203已收货 204已完成'];
    const after_sales     = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '0正常  1售后中'];
    const source          = ['varchar' => 50, 'notNull', 'comment' => '来源'];
    const pay_type        = ['varchar' => 20, 'comment' => 'wechat微信  alipay支付宝'];
    const pay_number      = ['varchar' => 255, 'comment' => '支付交易号'];
    const pay_time        = ['bigint' => 10, 'comment' => '支付时间'];
    const cancel_time     = ['bigint' => 10, 'comment' => '关闭时间'];
    const send_time       = ['bigint' => 10, 'comment' => '发货时间'];
    const received_time   = ['bigint' => 10, 'comment' => '收货时间'];
    const finish_time     = ['bigint' => 10, 'comment' => '结束时间'];
    const evaluate_time   = ['bigint' => 10, 'comment' => '评价时间'];
    const note            = ['varchar' => 255, 'comment' => '商家备注'];
    const AppID           = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id     = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const is_evaluate     = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '0未评价 1已评价'];
    const created_time    = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time    = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time    = ['bigint' => 10, 'comment' => '删除时间'];
    const is_recycle      = ['tinyint' => 1, 'default' => 0, 'comment' => '是否在回收站'];
    const is_deleted      = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['order_sn', 'UID', 'total_amount', 'pay_amount', 'goods_amount', 'merchant_id', 'AppID', 'source'], 'required', 'message' => '{attribute}不能为空'],
            [['UID', 'merchant_id'], 'integer', 'message' => '{attribute}必须是整数'],
            [['total_amount', 'pay_amount', 'goods_amount', 'freight_amount', 'cancel_time', 'coupon_reduced'], 'number', 'message' => '{attribute}必须是数字'],

        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
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
        $scenarios['create'] = ['order_sn', 'status', 'type', 'UID', 'total_amount', 'task_amount', 'pay_amount', 'goods_amount', 'coupon_reduced', 'freight_amount', 'merchant_id', 'cancel_time', 'source', 'AppID', 'score_amount', 'total_score'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_sn'     => '订单编号',
            'UID'          => '用户ID',
            'total_amount' => '总计价格',
            'pay_amount'   => '实付价格',
            'goods_amount' => '商品价格',
            'merchant_id'  => '商户ID',
            'source'       => '来源',
        ];
    }

    public function getUser()
    {
        $User = 'users\models\User';
        return $this->hasOne($User::className(), ['id' => 'UID'])->from(['u' => $User::tableName()]);
    }

    public function getOauth()
    {
        $Oauth = 'users\models\Oauth';
        return $this->hasOne($Oauth::className(), ['UID' => 'UID'])->from(['t' => $Oauth::tableName()]);
    }

    /**
     * 买家信息
     * @return [type] [description]
     */
    public function getBuyer()
    {
        return $this->hasOne('order\models\OrderBuyer', ['order_sn' => 'order_sn'])->select('order_sn,note,is_deleted, name, mobile, province, city, district, address');
    }
    /**
     * 物流信息
     * @return [type] [description]
     */
    public function getFreight()
    {
        return $this->hasOne('order\models\OrderFreight', ['order_sn' => 'order_sn'])->select('order_sn,type,code,logistics_company,freight_sn,created_time');
    }
    /**
     * 商品信息
     * @return [type] [description]
     */
    public function getGoods()
    {
        $Oauth = 'order\models\OrderGoods';
        return $this->hasMany($Oauth::className(), ['order_sn' => 'order_sn'])
            ->select('id,order_sn,freight_sn,goods_id,goods_name,goods_sn,goods_image,goods_param,show_goods_param,goods_price,goods_weight,goods_number,total_amount,pay_amount,after_sales,goods_score,score_amount')
            ->with('after')
            ->from(['u' => $Oauth::tableName()]);
    }

}
