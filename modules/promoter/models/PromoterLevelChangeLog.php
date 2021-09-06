<?php

namespace promoter\models;

use framework\common\CommonModels;

class PromoterLevelChangeLog extends CommonModels
{
    const id             = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const UID            = ['bigint' => 20, 'notNull', 'index' => '用户ID', 'comment' => '用户ID'];
    const old_level      = ['tinyint' => 2, 'notNull', 'comment' => '之前等级'];
    const old_level_name = ['varchar' => 255, 'notNull', 'comment' => '之前等级'];
    const new_level      = ['tinyint' => 2, 'notNull', 'comment' => '现在等级'];
    const new_level_name = ['varchar' => 255, 'notNull', 'comment' => '现在等级'];
    const type           = ['tinyint' => 1, 'default' => 1, 'comment' => '1升级  2降级'];
    const look_status    = ['tinyint' => 1, 'default' => 0, 'comment' => '查看状态'];
    const push_status    = ['tinyint' => 1, 'default' => 0, 'comment' => '推送状态'];
    const created_time   = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time   = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time   = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted     = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promoter_level_change_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    public function getUser()
    {
        return $this->hasOne('users\models\User', ['id' => 'UID']);
    }
}
