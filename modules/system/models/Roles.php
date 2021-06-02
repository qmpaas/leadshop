<?php
/**
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace system\models;

use framework\common\CommonModels;

class Roles extends CommonModels
{
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
            ['name', 'string'],
            ['describe', 'string'],
            ['visits', 'string'],
            ['created_time', 'integer'],
            ['updated_time', 'integer'],
            ['deleted_time', 'integer'],
            ['is_deleted', 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%roles}}';
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'  => '模块名称',
            'title' => '模块标题',
        ];
    }

}
