<?php
/**
 * 商品分类模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace goods\models;

use framework\common\CommonModels;

class GoodsData extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const goods_id     = ['bigint' => 20, 'notNull', 'comment' => '商品ID'];
    const param_value  = ['varchar' => 255, 'notNull', 'comment' => '规格参数'];
    const price        = ['decimal' => '10,2', 'notNull', 'comment' => '价格'];
    const cost_price   = ['decimal' => '10,2', 'default' => 0, 'comment' => '成本价'];
    const stocks       = ['int' => 10, 'notNull', 'default' => 0, 'comment' => '库存'];
    const weight       = ['decimal' => '10,2', 'default' => 0, 'comment' => '重量'];
    const task_stock   = ['int' => 10, 'notNull', 'default' => 0, 'comment' => '兑换库存'];
    const task_number  = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '兑换积分'];
    const task_price   = ['decimal' => '10,2', 'comment' => '兑换价格'];
    const task_limit   = ['bigint' => 5, 'comment' => '兑换限制'];
    const task_status  = ['tinyint' => 1, 'default' => 0, 'comment' => '是否上架：0 下架 1 上架'];
    const goods_sn     = ['varchar' => 50, 'comment' => '商品编号'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];
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
        return '{{%goods_data}}';
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

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [

        ];
    }

}
