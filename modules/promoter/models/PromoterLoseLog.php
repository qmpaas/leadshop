<?php

namespace promoter\models;

use framework\common\CommonModels;

class PromoterLoseLog extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const parent_id    = ['bigint' => 20, 'notNull', 'index' => '父级ID', 'comment' => '父级ID'];
    const UID          = ['bigint' => 20, 'notNull', 'index' => '用户ID', 'comment' => '用户ID'];
    const type         = ['tinyint' => 1, 'default' => 1, 'comment' => '失去下级原因  1解除  2清退  3保护期'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%promoter_lose_log}}';
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
}
