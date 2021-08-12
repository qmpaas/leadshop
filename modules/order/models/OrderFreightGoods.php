<?php
/**
 * 订单物流模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace order\models;

use framework\common\CommonModels;

class OrderFreightGoods extends CommonModels
{
    const id               = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const freight_id       = ['bigint' => 20, 'notNull', 'index' => '包裹id', 'comment' => '物流包裹ID'];
    const order_goods_id   = ['bigint' => 20, 'notNull', 'index' => '订单商品id', 'comment' => '订单商品ID'];
    const bag_goods_number = ['int' => 10, 'notNull', 'comment' => '订单商品数量'];
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
        return '{{%order_freight_goods}}';
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
     * 包裹商品
     * @return [type] [description]
     */
    public function getGoods()
    {
        return $this->hasOne('order\models\OrderGoods', ['id' => 'order_goods_id'])->select('id,goods_name,goods_image,goods_number,show_goods_param');
    }

    /**
     * 包裹信息
     * @return [type] [description]
     */
    public function getFreight()
    {
        return $this->hasOne('order\models\OrderFreight', ['id' => 'freight_id'])->select('id,order_sn,type,code,logistics_company,freight_sn');
    }

}
