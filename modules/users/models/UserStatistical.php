<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace users\models;

use framework\common\CommonModels;

class UserStatistical extends CommonModels
{
    const id              = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const UID             = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const buy_number      = ['int' => 10, 'default' => 0, 'comment' => '购买数量'];
    const buy_amount      = ['decimal' => '10,2', 'default' => 0, 'comment' => '消费总金额'];
    const last_buy_time   = ['bigint' => 10, 'comment' => '上次消费时间'];
    const last_visit_time = ['bigint' => 10, 'comment' => '上次访问时间'];
    const created_time    = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time    = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time    = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted      = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['UID'], 'required', 'message' => '{attribute}不能为空'],
            [['buy_number', 'buy_amount', 'last_buy_time','last_visit_time'], 'number', 'message' => '{attribute}必须为数字']
        ];
    }

    /**
     * 场景处理
     * @return [type] [description]
     */
    public function scenarios()
    {
        $scenarios         = parent::scenarios();
        $scenarios['save'] = ['UID','buy_number', 'buy_amount', 'last_buy_time','last_visit_time'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_statistical}}';
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

        ];
    }
}
