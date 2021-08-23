<?php
/**
 * 订单导出模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace order\models;

use framework\common\CommonModels;

class OrderAfterExport extends CommonModels
{
    const id               = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const conditions       = ['text' => 0, 'notNull', 'comment' => '导出条件json'];
    const parameter        = ['text' => 0, 'notNull', 'comment' => '参数json'];
    const order_after_data = ['longtext' => 0, 'notNull', 'comment' => '数据json'];
    const AppID            = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id      = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time     = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time     = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time     = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted       = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['conditions', 'parameter', 'order_after_data', 'merchant_id', 'AppID'], 'required', 'message' => '{attribute}不能为空'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_after_export}}';
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
            'conditions'       => '条件',
            'parameter'        => '字段',
            'order_after_data' => '数据',
        ];
    }

}
