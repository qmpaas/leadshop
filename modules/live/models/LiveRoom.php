<?php

namespace live\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%live_room}}".
 *
 * @property int $id ID
 * @property int $room_id 商品名称
 * @property string $anchor_wechat 主播微信号
 * @property string $sub_wechat 副手微信号
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_deleted 是否删除
 */
class LiveRoom extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const room_id      = ['bigint' => 11, 'notNull', 'comment' => '商品名称'];
    const anchor_wechat= ['varchar' => 128, 'notNull', 'comment' => '主播微信号'];
    const sub_wechat   = ['varchar' => 128, 'notNull', 'default' => '', 'comment' => '副手微信号'];
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
        return '{{%live_room}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['room_id', 'anchor_wechat', 'AppID'], 'required'],
            [['room_id', 'merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['anchor_wechat', 'sub_wechat'], 'string', 'max' => 128],
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
            'room_id' => 'Room ID',
            'anchor_wechat' => 'Anchor Wechat',
            'sub_wechat' => 'Sub Wechat',
            'AppID' => 'App ID',
            'merchant_id' => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }
}