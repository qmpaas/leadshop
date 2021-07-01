<?php

namespace live\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%live_goods}}".
 *
 * @property int $id ID
 * @property string $name 商品名称
 * @property string $cover 商品封面
 * @property int $price_type 价格类型
 * @property float $price 价格
 * @property float $price2 价格2
 * @property string $link 小程序路径
 * @property int $status 状态
 * @property string $audit_id 审核单号
 * @property int $goods_id 微信直播商品id
 * @property int $gid leadshop商品id
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_deleted 是否删除
 */
class LiveGoods extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const name         = ['varchar' => 1024, 'notNull', 'default' => '', 'comment' => '商品名称'];
    const cover        = ['varchar' => 4096, 'notNull', 'default' => '', 'comment' => '商品封面'];
    const price_type   = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '价格类型'];
    const price        = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '价格'];
    const price2       = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '价格2'];
    const link         = ['varchar' => 256, 'notNull', 'default' => '', 'comment' => '小程序路径'];
    const audit_id     = ['varchar' => 255, 'notNull', 'default' => '', 'comment' => '审核单号'];
    const goods_id     = ['int' => 11, 'notNull', 'default' => 0, 'comment' => '微信直播商品id'];
    const gid          = ['int' => 11, 'notNull', 'default' => 0, 'comment' => 'leadshop商品id'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id  = ['bigint' => 10, 'notNull', 'default' => 1, 'comment' => '商户ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%live_goods}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price_type', 'goods_id', 'gid', 'merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['price', 'price2'], 'number'],
            [['AppID'], 'required'],
            [['name'], 'string', 'max' => 1024],
            [['cover'], 'string', 'max' => 4096],
            [['link'], 'string', 'max' => 256],
            [['audit_id'], 'string', 'max' => 255],
            [['AppID'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'cover' => 'Cover',
            'price_type' => 'Price Type',
            'price' => 'Price',
            'price2' => 'Price2',
            'link' => 'Link',
            'status' => 'Status',
            'audit_id' => 'Audit ID',
            'AppID' => 'App ID',
            'merchant_id' => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }
}