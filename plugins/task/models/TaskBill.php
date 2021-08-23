<?php
/**
 * 商品详情模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\models;

use framework\common\CommonModels;

class TaskBill extends CommonModels
{
    const id            = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const task_goods_id = ['bigint' => 20, 'comment' => '积分商品ID'];
    const goods_id      = ['bigint' => 20, 'comment' => '商品ID'];
    const order_id      = ['bigint' => 20, 'comment' => '订单ID'];
    const UID           = ['bigint' => 20, 'comment' => '买家ID'];
    const remark        = ['varchar' => 255, 'comment' => '收支说明'];
    const type          = ['varchar' => 3, 'default' => 'add', 'comment' => '收支类型：add 增加 del 减少'];
    const number        = ['bigint' => 10, 'default' => 1, 'comment' => '积分收支'];
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

        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%uask_bill}}';
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

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return $scenarios;
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
