<?php
/**
 * 设置管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace fitment\models;

use framework\common\CommonModels;

class Page extends CommonModels
{

    const id           = ['bigkey' => 10, 'unsigned', 'unique', 'comment' => 'ID'];
    const title        = ['varchar' => 50, 'notNull', 'comment' => '微页面标题'];
    const name         = ['varchar' => 50, 'notNull', 'comment' => '微页面名称'];
    const background   = ['varchar' => 50, 'notNull', 'default' => '#F7F7F7', 'comment' => '背景色'];
    const goods_number = ['smallint' => 3, 'notNull', 'comment' => '商品数量'];
    const visit_number = ['int' => 10, 'notNull', 'default' => 0, 'comment' => '访问次数'];
    const content      = ['text' => 0, 'notNull', 'comment' => '页面配置'];
    const status       = ['tinyint' => 1, 'default' => 0, 'comment' => '1默认页面 0非默认'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
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
            [['title', 'name', 'content', 'AppID'], 'required', 'message' => '{attribute}不能为空'],
            [['goods_number', 'status', 'visit_number'], 'integer', 'message' => '{attribute}必须是整数'],
            [['background'], 'string', 'message' => '{attribute}必须是字符串'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fitment_page}}';
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
        $scenarios['create'] = ['title', 'name', 'content', 'AppID', 'goods_number', 'background'];
        $scenarios['update'] = ['title', 'name', 'content', 'goods_number', 'visit_number', 'background'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            "title"        => "微页面标题",
            "name"         => "微页面名称",
            "content"      => "微页面内容",
            "goods_number" => "商品数量",
            "AppID"        => "应用ID",
            "visit_number" => "访问量",
            "background"   => "背景色",
        ];
    }
}
