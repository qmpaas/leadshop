<?php

namespace plugins\evaluate\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%evaluate}}".
 *
 * @property int $id
 * @property int $repository_id 评论库id
 * @property int $star 星级
 * @property string $content 评论内容
 * @property string|null $images 评论图片
 * @property int $created_time
 * @property int $updated_time
 * @property int $deleted_time
 * @property int $is_deleted
 */
class Evaluate extends CommonModels
{
    const id = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const repository_id = ['bigint' => 20, 'unique', 'comment' => '评论库id'];
    const star = ['tinyint' => 1, 'notNull', 'comment' => '星级'];
    const content = ['text' => 0, 'notNull', 'comment' => '评论内容'];
    const images = ['text' => 0, 'comment' => '评论图片'];
    const created_time = ['int' => 10, 'default' => 0, 'comment' => '创建时间'];
    const updated_time = ['int' => 10, 'default' => 0, 'comment' => '修改时间'];
    const deleted_time = ['int' => 10, 'default' => 0, 'comment' => '删除时间'];
    const is_deleted = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%evaluate}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['repository_id', 'star', 'content'], 'required'],
            [['repository_id', 'star', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['content', 'images'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'repository_id' => '评论库id',
            'star' => '评价等级',
            'content' => '评价内容',
            'images' => '图片',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }
}
