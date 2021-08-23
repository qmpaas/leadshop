<?php
/**
 * 商品访问模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace statistical\models;

use framework\common\CommonModels;

class GoodsVisitLog extends CommonModels
{
    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const goods_id     = ['bigint' => 50, 'notNull', 'comment' => '商定编号'];
    const UID          = ['bigint' => 20, 'comment' => '用户ID'];
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
            [['goods_id', 'merchant_id', 'AppID'], 'required', 'message' => '{attribute}不能为空'],
            [['goods_id', 'UID', 'merchant_id'], 'integer', 'message' => '{attribute}必须为整数']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%statistical_goods_visit_log}}';
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
     * 场景处理
     * @return [type] [description]
     */
    public function scenarios()
    {
        $scenarios         = parent::scenarios();
        $scenarios['save'] = ['goods_id', 'UID', 'merchant_id', 'AppID'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'goods_id' => '商品ID',
            'UID'      => '用户ID',
        ];
    }

}
