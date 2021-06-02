<?php
/**
 * 用户标签获取记录
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace users\models;

use framework\common\CommonModels;

class LabelLog extends CommonModels
{
    const id           = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const UID          = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const label_id     = ['bigint' => 10, 'notNull', 'comment' => '标签ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['UID' , 'label_id'], 'required', 'message' => '{attribute}不能为空'],
            [['UID' , 'label_id'], 'integer', 'message' => '{attribute}必须是整数'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_label_log}}';
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

    /**
     * 定义场景字段
     * @return [type] [description]
     */
    public function scenarios()
    {
        $scenarios           = parent::scenarios();
        $scenarios['create'] = ['UID', 'label_id'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UID'      => '用户ID',
            'label_id' => '标签ID',
        ];
    }

    public function getLabel(){
        return $this->hasOne(Label::className(), ['id' => 'label_id']);
    }
}
