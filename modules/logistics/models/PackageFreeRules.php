<?php
/**
 * 包邮规则模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace logistics\models;

use framework\common\CommonModels;

class PackageFreeRules extends CommonModels
{
    const id           = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const name         = ['varchar' => 50, 'notNull', 'comment' => '包邮规则名称'];
    const type         = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '包邮类型 1订单满额包邮 2订单满件包邮 3商品满额包邮 4商品满件包邮'];
    const free_area    = ['text' => 0, 'comment' => '包邮区域'];
    const status       = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '是否默认 1默认 0非默认'];
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
            [['name', 'type', 'free_area', 'merchant_id','AppID'], 'required', 'message' => '{attribute}不能为空'],
            ['status', 'default', 'value' => 0],
            [['type', 'status', 'merchant_id'], 'integer'],
            ['name', 'string', 'max' => 10, 'tooLong' => '{attribute}最多10位'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logistics_package_free}}';
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
        $scenarios['create'] = ['name', 'type', 'free_area', 'status', 'merchant_id','AppID'];
        $scenarios['update'] = ['name', 'type', 'free_area'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => '规则名称',
            'type'        => '包邮类型',
            'free_area'   => '包邮信息',
            'status'      => '默认状态',
            'merchant_id' => '商户ID',
        ];
    }

}
