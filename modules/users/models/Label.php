<?php
/**
 * 用户标签
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace users\models;

use framework\common\CommonModels;

class Label extends CommonModels
{
    const id                 = ['bigkey' => 10, 'unique', 'comment' => 'ID'];
    const name               = ['varchar' => 50, 'notNull', 'comment' => '标签名称'];
    const type               = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '标签类型 1手动 2自动'];
    const status             = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '启用状态 0不启用  1启用'];
    const conditions_status  = ['tinyint' => 1, 'notNull', 'default' => 1, 'comment' => '达标条件  1满足所有  2任意一个'];
    const conditions_setting = ['text' => 0, 'comment' => '条件设置'];
    const filter_user        = ['text' => 0, 'comment' => '过滤用户'];
    const users_number       = ['int' => 10, 'default' => 0, 'comment' => '拥有用户数量'];
    const AppID              = ['varchar' => 50, 'notNull', 'comment' => '应用ID'];
    const merchant_id        = ['bigint' => 10, 'notNull', 'comment' => '商户ID'];
    const created_time       = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time       = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time       = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted         = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['name', 'type', 'AppID', 'merchant_id'], 'required', 'message' => '{attribute}不能为空'],
            [['status', 'conditions_status', 'conditions_setting', 'filter_user'], 'required',
                'when' => function ($model) {
                    return $model->type === 2 ? true : false;
                }, 'message' => '{attribute}不能为空'],
            [['merchant_id', 'status'], 'integer', 'message' => '{attribute}必须是整数'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_label}}';
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
        $scenarios['create'] = ['name', 'type', 'status', 'conditions_status', 'conditions_setting', 'filter_user', 'AppID', 'merchant_id'];
        $scenarios['update'] = ['name', 'status', 'conditions_status', 'conditions_setting', 'filter_user'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'               => '标签名',
            'type'               => '标签类型',
            'status'             => '启用状态',
            'conditions_status'  => '达标条件',
            'conditions_setting' => '条件设置',
            'filter_user'        => '过滤用户',
        ];
    }

    public function getLog()
    {
        return $this->hasMany(LabelLog::className(), ['label_id' => 'id']);
    }
}
