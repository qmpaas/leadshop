<?php
/**
 * 商品分类模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace goods\models;

use framework\common\CommonModels;

class GoodsGroup extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const name         = ['varchar' => 50, 'notNull', 'comment' => '分类名称'];
    const parent_id    = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '父级ID'];
    const type         = ['tinyint' => 1, 'default' => 1, 'comment' => '1一层  2二层  3三层'];
    const path         = ['varchar' => 50, 'notNull', 'comment' => '分类路径'];
    const sort         = ['smallint' => 3, 'notNull', 'default' => 1, 'comment' => '排序'];
    const icon         = ['varchar' => 255, 'notNull', 'comment' => '分类封面'];
    const image        = ['varchar' => 255, 'notNull', 'default' => '', 'comment' => '分类广告图'];
    const goods_show   = ['tinyint' => 2, 'notNull', 'comment' => '分类商品展示方式'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id  = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const is_show      = ['tinyint' => 1, 'default' => 1, 'comment' => '是否显示 0不显示  1显示'];
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
            [['name', 'parent_id', 'sort', 'merchant_id', 'AppID', 'icon', 'goods_show'], 'required', 'message' => '{attribute}不能为空'],
            [['type'], 'required',
                'when' => function ($model) {
                    return (!$model->parent_id > 0) ? true : false;
                }, 'message' => '{attribute}不能为空'],
            [['parent_id', 'sort', 'is_show', 'merchant_id'], 'integer'],
            ['sort', 'compare', 'compareValue' => 999, 'operator' => '<=', 'message' => '{attribute}最多3位'],
            ['name', 'string', 'max' => 8, 'tooLong' => '{attribute}最多8位'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_group}}';
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
        $scenarios['create'] = ['name', 'parent_id', 'sort', 'merchant_id', 'AppID', 'icon', 'goods_show', 'path', 'type', 'is_show'];
        $scenarios['update'] = ['name', 'sort', 'icon', 'goods_show', 'type', 'is_show'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => '分类标题',
            'parent_id'   => '上级ID',
            'sort'        => '排序',
            'icon'        => '图标',
            'image'       => '广告图',
            'goods_show'  => '商品展示形式',
            'path'        => '分类路径',
            'merchant_id' => '商户ID',
            'is_show'     => '分组是否显示',
        ];
    }

}
