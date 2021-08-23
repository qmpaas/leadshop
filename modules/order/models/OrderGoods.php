<?php
/**
 * 订单商品模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace order\models;

use framework\common\CommonModels;

class OrderGoods extends CommonModels
{
    const id               = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const order_sn         = ['varchar' => 50, 'notNull', 'comment' => '订单号'];
    const goods_id         = ['bigint' => 20, 'notNull', 'comment' => '商品ID'];
    const goods_sn         = ['varchar' => 50, 'comment' => '商品编号'];
    const goods_name       = ['varchar' => 100, 'notNull', 'comment' => '商品名称'];
    const goods_image      = ['varchar' => 255, 'notNull', 'comment' => '商品图片'];
    const show_goods_param = ['varchar' => 255, 'notNull', 'comment' => '商品展示规格'];
    const goods_param      = ['varchar' => 255, 'notNull', 'comment' => '商品规格'];
    const goods_price      = ['decimal' => '10,2', 'notNull', 'comment' => '商品价格'];
    const goods_score      = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '商品积分'];
    const goods_cost_price = ['decimal' => '10,2', 'comment' => '商品成本价格'];
    const goods_weight     = ['decimal' => '10,2', 'comment' => '商品重量'];
    const goods_number     = ['int' => 10, 'notNull', 'comment' => '商品数量'];
    const total_amount     = ['decimal' => '10,2', 'notNull', 'comment' => '总计金额'];
    const score_amount     = ['bigint' => 10, 'default' => 0, 'comment' => '总计积分'];
    const pay_amount       = ['decimal' => '10,2', 'notNull', 'comment' => '实付金额'];
    const coupon_reduced   = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '优惠券优惠金额'];
    const after_sales      = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '0正常 1进行售后'];
    const is_evaluate      = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '0未评价 1已评价'];
    const created_time     = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time     = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time     = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted       = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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

        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_goods}}';
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [

        ];
    }

    /**
     * 订单信息
     * @return [type] [description]
     */
    public function getOrder()
    {
        return $this->hasOne('order\models\Order', ['order_sn' => 'order_sn']);
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

    public function getBag()
    {
        return $this->hasMany('order\models\OrderFreightGoods', ['order_goods_id' => 'id'])->select('freight_id,order_goods_id,bag_goods_number');
    }

    public function getAfter()
    {
        return $this->hasMany('order\models\OrderAfter', ['order_sn' => 'order_sn'])->select('order_sn,order_goods_id,type,status,return_number');
    }

    /**
     * 物流信息
     * @return [type] [description]
     */
    public function getGoods()
    {
        $Goods = 'goods\models\Goods';
        return $this->hasOne($Goods::className(), ['id' => 'goods_id'])->select('id,group')->from(['g' => $Goods::tableName()]);
    }

    /**
     * 物流信息
     * @return [type] [description]
     */
    public function getTaskgoods()
    {
        $TaskGoods = 'plugins\task\models\TaskGoods';
        return $this->hasOne($TaskGoods::className(), ['goods_id' => 'goods_id'])->from(['g' => $TaskGoods::tableName()]);
    }

}
