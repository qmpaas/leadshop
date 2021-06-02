<?php
/**
 * 订单支付模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace order\models;

use framework\common\CommonModels;

class OrderPay extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const pay_sn       = ['varchar' => 50, 'notNull', 'comment' => '支付单号'];
    const order_list   = ['text' => 0, 'notNull', 'comment' => '订单列表'];
    const pay_type     = ['tinyint' => 1, 'comment' => '支付类型 1微信  2支付宝'];
    const status       = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '未支付  已支付'];
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
            [['pay_sn', 'order_list', 'total_amount','AppID'], 'required', 'message' => '{attribute}不能为空'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_pay}}';
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
            'pay_sn'       => '支付单号',
            'order_list'   => '订单编号',
            'total_amount' => '支付金额',
        ];
    }

}
