<?php
/**
 * 订单批量操作模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace order\models;

use framework\common\CommonModels;

class OrderBatchHandle extends CommonModels
{
    const id             = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const handle_sn      = ['varchar' => 50, 'notNull', 'comment' => '参数json'];
    const order_number   = ['smallint' => 4, 'notNull', 'comment' => '发货订单数'];
    const success_number = ['smallint' => 4, 'notNull', 'comment' => '成功发货数'];
    const error_data     = ['longtext' => 0, 'notNull', 'comment' => '失败数据json'];
    const AppID          = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id    = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time   = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time   = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time   = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted     = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['handle_sn', 'order_number', 'success_number', 'error_data', 'merchant_id', 'AppID'], 'required', 'message' => '{attribute}不能为空'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_batch_handle}}';
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
            'handle_sn'      => '操作编号',
            'order_number'   => '订单数量',
            'success_number' => '成功数量',
            'error_data'     => '失败记录',
        ];
    }

}
