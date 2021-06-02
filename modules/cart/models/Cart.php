<?php
/**
 * 购物车
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace cart\models;

use framework\common\CommonModels;

class Cart extends CommonModels
{
    const id               = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const goods_id         = ['bigint' => 20, 'notNull', 'comment' => '商品ID'];
    const UID              = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const goods_name       = ['varchar' => 255, 'notNull', 'comment' => '商品名称'];
    const goods_image      = ['varchar' => 255, 'notNull', 'comment' => '商品图片'];
    const goods_param      = ['varchar' => 255, 'notNull', 'comment' => '商品规格'];
    const show_goods_param = ['varchar' => 255, 'notNull', 'comment' => '商品规格'];
    const goods_number     = ['int' => 10, 'notNull', 'default' => 1, 'comment' => '商品数量'];
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
            [['goods_id', 'UID', 'goods_param', 'goods_number', 'goods_name', 'goods_image', 'show_goods_param'], 'required', 'message' => '{attribute}不能为空'],
            [['goods_id', 'goods_number'], 'integer', 'message' => '{attribute}必须为整数'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cart}}';
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
        $scenarios           = parent::scenarios();
        $scenarios['create'] = ['goods_id', 'UID', 'goods_param', 'goods_number', 'goods_name', 'goods_image', 'show_goods_param'];
        $scenarios['update'] = ['goods_param', 'goods_number', 'goods_name', 'goods_image', 'show_goods_param'];
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

    public function getGoodsinfo()
    {
        return $this->hasOne('goods\models\Goods', ['id' => 'goods_id'])->where(['is_deleted' => 0, 'is_recycle' => 0])->with('param')->select('id,is_sale,min_number,limit_buy_value,limit_buy_status');
    }

}
