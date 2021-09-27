<?php

namespace plugins\evaluate\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%evaluate_repository}}".
 *
 * @property int $id
 * @property string $name 评论库名称
 * @property int $created_time
 * @property int $updated_time
 * @property int $deleted_time
 * @property int $is_deleted
 * @property Evaluate $evaluate
 */
class EvaluateRepository extends CommonModels
{
    const id = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const name = ['varchar' => 20, 'comment' => '评论库名称'];
    const created_time = ['int' => 10, 'default' => 0, 'comment' => '创建时间'];
    const updated_time = ['int' => 10, 'default' => 0, 'comment' => '修改时间'];
    const deleted_time = ['int' => 10, 'default' => 0, 'comment' => '删除时间'];
    const is_deleted = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%evaluate_repository}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['name'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '评论库名称',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }

    public function getEvaluate()
    {
        return $this->hasMany(Evaluate::className(), ['repository_id' => 'id']);
    }
}
