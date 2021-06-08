<?php

namespace subscribe\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%subscribe_template}}".
 *
 * @property int $id ID
 * @property string $AppID 应用ID
 * @property string $tpl_name 订阅消息名称
 * @property string $tpl_id 订阅消息id
 * @property int $created_time 创建时间
 * @property int $updated_time 更新时间
 * @property int $deleted_time 删除时间
 * @property int $is_deleted 是否删除
 */
class SubscribeTemplate extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const tpl_name     = ['varchar' => 50, 'notNull', 'comment' => '订阅消息名称'];
    const tpl_id       = ['varchar' => 50, 'notNull', 'comment' => '订阅消息id'];
    const platform     = ['varchar' => 8, 'default' => 'weapp', 'comment' => 'weapp:微信小程序  wechat:微信公众号'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%subscribe_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['AppID', 'tpl_name', 'tpl_id'], 'required'],
            [['created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['AppID', 'tpl_name', 'tpl_id'], 'string', 'max' => 50],
            [['platform'], 'string', 'max' => 8],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'AppID' => 'App ID',
            'tpl_name' => 'Tpl Name',
            'tpl_id' => 'Tpl ID',
            'platform' => '平台',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }
}