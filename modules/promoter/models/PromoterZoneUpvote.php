<?php

namespace promoter\models;

use framework\common\CommonModels;
use users\models\User;

/**
 * This is the model class for table "{{%promoter_zone_upvote}}".
 *
 * @property int $id ID
 * @property int $UID 用户ID
 * @property int $zone_id 空间动态id
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_deleted 是否删除
 */
class PromoterZoneUpvote extends CommonModels
{
    const id = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const UID = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const zone_id = ['int' => 11, 'notNull', 'comment' => '空间动态id'];
    const AppID = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promoter_zone_upvote}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UID', 'zone_id', 'AppID', 'merchant_id'], 'required'],
            [['UID', 'zone_id', 'merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
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
            'UID' => 'Uid',
            'zone_id' => 'Zone ID',
            'AppID' => 'App ID',
            'merchant_id' => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'UID']);
    }
}
