<?php

namespace promoter\models;

use framework\common\CommonModels;

class PromoterGoods extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const goods_id     = ['bigint' => 20, 'notNull', 'index' => '商品ID', 'comment' => '商品ID'];
    const sales        = ['int' => 10, 'default' => 0, 'comment' => '销量'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promoter_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_id', 'status', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['UID', 'created_time', 'status'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'goods_id'     => '商品id',
            'status'       => '状态',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted'   => 'Is Deleted',
        ];
    }
}
