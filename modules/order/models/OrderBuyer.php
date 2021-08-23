<?php
/**
 * 订单买家模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace order\models;

use framework\common\CommonModels;

class OrderBuyer extends CommonModels
{
    const id             = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const order_sn       = ['varchar' => 50, 'notNull', 'comment' => '订单号'];
    const name           = ['varchar' => 50, 'notNull', 'comment' => '收件人'];
    const mobile         = ['varchar' => 50, 'notNull', 'comment' => '联系电话'];
    const province       = ['varchar' => 50, 'notNull', 'comment' => '省'];
    const city           = ['varchar' => 50, 'notNull', 'comment' => '市'];
    const district       = ['varchar' => 50, 'notNull', 'comment' => '区县'];
    const address        = ['varchar' => 255, 'notNull', 'comment' => '详细地址'];
    const note           = ['varchar' => 255, 'comment' => '买家备注'];
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
            [['order_sn',  'name', 'mobile', 'province', 'city', 'district', 'address'], 'required', 'message' => '{attribute}不能为空'],
            [['note', 'order_sn',  'name', 'mobile', 'province', 'city', 'district', 'address'], 'string', 'message' => '{attribute}必须是字符串'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_buyer}}';
    }

    /**
     * 增加额外属性
     * @return [type] [description]
     */
    public function attributes()
    {
        $attributes          = parent::attributes();
        return $attributes;
    }

    /**
     * 场景处理
     * @return [type] [description]
     */
    public function scenarios()
    {
        $scenarios           = parent::scenarios();
        $scenarios['create'] = ['order_sn', 'name', 'mobile', 'province', 'city', 'district', 'address', 'note'];
        $scenarios['update'] = ['name', 'mobile', 'province', 'city', 'district', 'address'];
        return $scenarios;
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_sn' => '订单编号',
            'name'     => '收货人',
            'mobile'   => '手机',
            'province' => '省',
            'city'     => '市',
            'district' => '区',
            'address'  => '详细地址',
            'note'     => '用户备注',
        ];
    }

}
