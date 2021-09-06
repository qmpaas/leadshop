<?php

namespace finance\models;

use framework\common\CommonModels;
use promoter\models\Promoter;
use users\models\Oauth;
use users\models\User;

/**
 * This is the model class for table "{{%finance}}".
 *
 * @property int $id ID
 * @property int|null $UID 用户ID
 * @property string $order_sn 提现订单号
 * @property float $price 提现金额
 * @property float $service_charge 提现手续费（%）
 * @property int $type 提现方式 wechatDib: '自动到账微信零钱', wechat: '提现到微信', alipay: '提现到支付宝',bankCard: '提现到银行卡'
 * @property string $extra 额外信息
 * @property int $status 提现状态 0--申请 1--同意 2--已打款 3--驳回
 * @property string $remark 备注
 * @property string $name 真实姓名
 * @property string $model 提现来源(promoter)
 * @property int $mobile 手机
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_deleted 是否删除
 */
class Finance extends CommonModels
{
    const id = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const UID = ['bigint' => 20, 'comment' => '用户ID'];
    const order_sn = ['varchar' => 50, 'notNull', 'comment' => '提现订单号'];
    const price = ['decimal' => '10,2', 'notNull', 'comment' => '提现金额'];
    const service_charge = ['decimal' => '10,2', 'notNull', 'default' => 0, 'comment' => '提现手续费（%）'];
    const type = ['varchar' => 10, 'notNull', 'comment' => "提现方式 wechatDib: '自动到账微信零钱', wechat: '提现到微信', alipay: '提现到支付宝',bankCard: '提现到银行卡'"];
    const extra = ['longtext' => 0, 'notNull', 'default' => '', 'comment' => '额外信息'];
    const status = ['tinyint' => 0, 'notNull', 'default' => 0, 'comment' => '提现状态 0--申请 1--同意 2--已打款 3--驳回'];
    const remark = ['varchar' => 1024, 'notNull', 'default' => '', 'comment' => '备注'];
    const name = ['varchar' => 50, 'notNull', 'default' => '', 'comment' => '真实姓名'];
    const model = ['varchar' => 50, 'notNull', 'comment' => '提现来源(promoter)'];
    const mobile = ['varchar' => 20, 'notNull', 'default' => '', 'comment' => '手机'];
    const AppID = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    const PAY_TYPE = 'pay_type'; // 提现方式
    const CASH_SERVICE_CHARGE = 'cash_service_charge'; // 提现手续费

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%finance}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UID', 'status', 'merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['order_sn', 'price', 'model', 'AppID', 'merchant_id', 'type'], 'required'],
            [['price', 'service_charge'], 'number'],
            [['extra', 'type', 'mobile'], 'string'],
            [['mobile'], 'string', 'max' => 20],
            [['order_sn', 'name', 'model', 'AppID'], 'string', 'max' => 50],
            [['remark'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'UID' => 'Uid',
            'order_sn' => 'Order Sn',
            'price' => 'Price',
            'service_charge' => 'Service Charge',
            'type' => 'Type',
            'extra' => 'Extra',
            'status' => 'Status',
            'remark' => 'Remark',
            'name' => 'Name',
            'model' => 'Model',
            'mobile' => 'Mobile',
            'AppID' => 'App ID',
            'merchant_id' => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }

    public function getStatusText($status)
    {
        $text = ['申请中', '同意申请，待打款', '已打款', '驳回'];
        return isset($text[$status]) ? $text[$status] : '未知状态' . $status;
    }

    public function getTypeText($type)
    {
        $typeList = [
            'wechatDib' => '自动到账微信零钱',
            'wechat' => '提现到微信',
            'alipay' => '提现到支付宝',
            'bankCard' => '提现到银行卡'
        ];
        return isset($typeList[$type]) ? $typeList[$type] : '未知类型：' . $type;
    }

    public function getTypeText2($type = '')
    {
        if (!$type) {
            $type = $this->type;
        }
        $typeList = [
            'wechatDib' => '自动到账微信零钱',
            'wechat' => '提现到微信',
            'alipay' => '提现到支付宝',
            'bankCard' => '提现到银行卡'
        ];
        return isset($typeList[$type]) ? $typeList[$type] : '未知类型：' . $type;
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'UID']);
    }

    public function getOauth()
    {
        return $this->hasOne(Oauth::className(), ['UID' => 'UID']);
    }

    public function getPromoter()
    {
        return $this->hasOne(Promoter::className(), ['UID' => 'UID']);
    }
}
