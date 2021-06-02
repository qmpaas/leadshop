<?php

namespace goods\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{goods_param_template}}".
 *
 * @property int $id ID
 * @property string $param_name 规格名
 * @property string $param_data 规格值
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int $created_time 创建时间
 * @property int $updated_time 更新时间
 * @property int $deleted_time 删除时间
 * @property int $is_deleted 是否删除
 */
class GoodsParamTemplate extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const param_name   = ['varchar' => 256, 'notNull', 'comment' => '规格名'];
    const param_data   = ['text' => 0, 'comment' => '规格值'];
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

        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods_param_template}}';
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
        $scenarios = parent::scenarios();
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [

        ];
    }
}
