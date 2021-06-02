<?php
/**
 * 运费模板模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace logistics\models;

use framework\common\CommonModels;

class FreightTemplate extends CommonModels
{
    const id            = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const name          = ['varchar' => 50, 'notNull', 'comment' => '模板名称'];
    const type          = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '计费方式 1按件数 2按重量'];
    const freight_rules = ['text' => 0, 'comment' => '运费规则'];
    const status        = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '是否默认 1默认 0非默认'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id   = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time  = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time  = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time  = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted    = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['name', 'type', 'freight_rules', 'merchant_id','AppID'], 'required', 'message' => '{attribute}不能为空'],
            ['status', 'default', 'value' => 0],
            [['type', 'status', 'merchant_id'], 'integer'],
            ['name', 'string', 'max' => 10, 'tooLong' => '{attribute}最多10位'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logistics_freight_template}}';
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
        $scenarios['create'] = ['name', 'type', 'freight_rules', 'status', 'merchant_id','AppID'];
        $scenarios['update'] = ['name', 'type', 'freight_rules'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'          => '模板名称',
            'type'          => '计费方式',
            'freight_rules' => '运费规则',
            'status'        => '默认状态',
            'merchant_id'   => '商户ID',
        ];
    }

}
