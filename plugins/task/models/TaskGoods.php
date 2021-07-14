<?php
/**
 * 商品详情模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task\models;

use framework\common\CommonModels;
use goods\models\Goods;

class TaskGoods extends CommonModels
{
    const id            = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const goods_id      = ['bigint' => 20, 'comment' => '商品ID'];
    const task_stock    = ['bigint' => 10, 'comment' => '兑换库存'];
    const task_number   = ['bigint' => 10, 'comment' => '兑换积分'];
    const task_price    = ['decimal' => '10,2', 'comment' => '兑换价格'];
    const task_limit    = ['bigint' => 5, 'comment' => '兑换限制'];
    const task_status   = ['tinyint' => 1, 'default' => 0, 'comment' => '兑换状态'];
    const goods_is_sale = ['tinyint' => 1, 'default' => 0, 'comment' => '是否上架：0 下架 1 上架'];
    const is_recycle    = ['tinyint' => 1, 'default' => 0, 'comment' => '是否在回收站'];

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
        return '{{%task_goods}}';
    }

    /**
     * 获取商品信息数据
     * @return [type] [description]
     */
    public function getGoods()
    {
        /**
         * 第一个参数为要关联的字表模型类名称，
         * 第二个参数指定 通过子表的 customer_id 去关联主表的 id 字段
         * ->from(['g' => Goods::tableName()]) 设置附表别名
         */
        return $this->hasOne(Goods::className(), ['id' => 'goods_id'])->from(['g' => Goods::tableName()]);
    }

}
