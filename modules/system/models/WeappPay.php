<?php

namespace system\models;

use framework\common\CommonModels;
use Yii;

/**
* This is the model class for table "weapp_weapp_pay".
* @property int $id
* @property int|null $created_time
* @property int|null $updated_time
* @property int|null $deleted_time
* @property int $is_deleted
* @property string $order_sn 订单号
* @property string $pay_sn 支付号
* @property string $transaction_id 小程序支付交易单号
*/
class WeappPay extends CommonModels
{
    const id                = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const order_sn          = ['varchar' => 50, 'notNull', 'default' => '', 'comment' => '订单号'];
    const pay_sn            = ['varchar' => 50, 'notNull', 'default' => '', 'comment' => '支付号'];
    const transaction_id    = ['varchar' => 64, 'notNull', 'default' => '', 'comment' => '小程序支付交易单号'];
    const created_time      = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time      = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time      = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted        = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%weapp_pay}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['order_sn', 'pay_sn'], 'string', 'max' => 50],
            [['transaction_id'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_sn' => 'Order Sn',
            'transaction_id' => 'Transaction Id',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }
}
