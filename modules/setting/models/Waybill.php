<?php

namespace setting\models;

use framework\common\CommonModels;

/**
 * This is the model class for table "{{%waybill}}".
 *
 * @property int $id ID
 * @property string $code 物流公司编号
 * @property string $name 名称
 * @property string $mobile 联系方式
 * @property string $province 省
 * @property string $city 市
 * @property string $district 区县
 * @property string $address 详细地址
 * @property string $AppID 应用ID
 * @property int $merchant_id 商户ID
 * @property int|null $created_time 创建时间
 * @property int|null $updated_time 更新时间
 * @property int|null $deleted_time 删除时间
 * @property int|null $is_deleted 是否删除
 */
class Waybill extends CommonModels
{
    const id = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const code = ['varchar' => 10, 'notNull', 'comment' => '物流公司编号'];
    const name = ['varchar' => 256, 'notNull', 'comment' => '名称'];
    const mobile = ['varchar' => 32, 'notNull', 'comment' => '联系方式'];
    const province = ['varchar' => 50, 'notNull', 'comment' => '省'];
    const city = ['varchar' => 50, 'notNull', 'comment' => '市'];
    const district = ['varchar' => 50, 'notNull', 'comment' => '区县'];
    const address = ['varchar' => 255, 'notNull', 'comment' => '详细地址'];
    const AppID = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%waybill}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'mobile', 'province', 'city', 'district', 'address', 'AppID', 'merchant_id'], 'required'],
            [['merchant_id', 'created_time', 'updated_time', 'deleted_time', 'is_deleted'], 'integer'],
            [['code'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 256],
            [['mobile'], 'string', 'max' => 32],
            [['province', 'city', 'district', 'AppID'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => '快递公司',
            'name' => '名称',
            'mobile' => '联系方式',
            'province' => '省',
            'city' => '市',
            'district' => '区',
            'address' => '地址',
            'AppID' => 'App ID',
            'merchant_id' => 'Merchant ID',
            'created_time' => 'Created Time',
            'updated_time' => 'Updated Time',
            'deleted_time' => 'Deleted Time',
            'is_deleted' => 'Is Deleted',
        ];
    }
}
