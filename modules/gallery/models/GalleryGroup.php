<?php
/**
 * 素材分组模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace gallery\models;

use framework\common\CommonModels;

class GalleryGroup extends CommonModels
{

    const id           = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const name         = ['varchar' => 50, 'notNull', 'comment' => '分组名称'];
    const type         = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '1图片 2视频'];
    const parent_id    = ['bigint' => 10, 'notNull', 'default' => 0, 'comment' => '父级ID'];
    const path         = ['varchar' => 50, 'notNull', 'comment' => '分组路径'];
    const sort         = ['smallint' => 4, 'notNull', 'default' => 1, 'comment' => '排序'];
    const UID          = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const AppID        = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id  = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
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
            [['name', 'type','merchant_id', 'AppID', 'UID'], 'required', 'message' => '{attribute}不能为空'],
            [['parent_id', 'sort', 'merchant_id', 'UID'], 'integer'],
            ['sort', 'compare', 'compareValue' => 999, 'operator' => '<=', 'message' => '{attribute}最多3位'],
            ['name', 'string', 'max' => 8, 'tooLong' => '{attribute}最多8位'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gallery_group}}';
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
        $scenarios['create'] = ['name', 'parent_id', 'type','sort', 'merchant_id', 'path', 'UID','AppID'];
        $scenarios['update'] = ['name', 'sort'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => '分组标题',
            'parent_id'   => '上级ID',
            'sort'        => '排序',
            'path'        => '分组路径',
            'merchant_id' => '商户ID',
            'AppID'       => '应用ID',
        ];
    }

}
