<?php
/**
 * 设置管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace fitment\models;

use framework\common\CommonModels;

class Template extends CommonModels
{
    const id           = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const name         = ['varchar' => 50, 'notNull', 'comment' => '模板名称'];
    const background   = ['varchar' => 50, 'notNull', 'default' => '#F7F7F7', 'comment' => '背景色'];
    const image        = ['varchar' => 255, 'notNull', 'comment' => '封面'];
    const content      = ['text' => 0, 'notNull', 'comment' => '页面配置'];
    const writer       = ['varchar' => 50, 'notNull', 'comment' => '作者'];
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
            [['name', 'image', 'content', 'writer'], 'required', 'message' => '{attribute}不能为空'],
            [['background'], 'string', 'message' => '{attribute}必须是字符串'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fitment_template}}';
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

    public function scenarios()
    {
        $scenarios           = parent::scenarios();
        $scenarios['create'] = ['name', 'image', 'content', 'writer', 'background'];
        $scenarios['update'] = ['name', 'image', 'content', 'background'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            "name"       => "模板名称",
            "content"    => "模板内容",
            "image"      => "封面图",
            "writer"     => "作者",
            "background" => "背景色",
        ];
    }
}
