<?php
/**
 * 素材模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace goods\models;

use framework\common\CommonModels;

class GoodsService extends CommonModels
{
    const id           = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const title        = ['varchar' => 50, 'notNull', 'comment' => '服务名称'];
    const content      = ['text' => 0, 'comment' => '服务详情'];
    const sort         = ['smallint' => 3, 'notNull', 'default' => 1, 'comment' => '排序'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id  = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];
    const status       = ['tinyint' => 1, 'default' => 0, 'comment' => '0 未启用  1启用'];

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
            [['title', 'merchant_id', 'AppID'], 'required', 'message' => '{attribute}不能为空'],
            [['sort', 'merchant_id', 'status'], 'integer'],
            ['sort', 'compare', 'compareValue' => 999, 'operator' => '<=', 'message' => '{attribute}最多3位'],
            ['title', 'string', 'max' => 20, 'tooLong' => '{attribute}最多20位'],
            ['content', 'string', 'max' => 200, 'tooLong' => '{attribute}最多200位'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_service}}';
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
        $scenarios['create'] = ['title', 'sort', 'merchant_id', 'AppID', 'content'];
        $scenarios['update'] = ['title', 'sort', 'content', 'status'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title'       => '服务名称',
            'sort'        => '排序',
            'status'      => '状态',
            'content'     => '服务详情',
            'merchant_id' => '商户ID',
        ];
    }

}
