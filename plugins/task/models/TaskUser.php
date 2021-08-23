<?php
/**
 * 商品详情模型
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\models;

use framework\common\CommonModels;

class TaskUser extends CommonModels
{
    const id      = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const UID     = ['bigint' => 20, 'notNull', 'comment' => '用户ID'];
    const number  = ['bigint' => 10, 'default' => 0, 'comment' => '积分值'];
    const total   = ['bigint' => 10, 'default' => 0, 'comment' => '积分累计'];
    const consume = ['bigint' => 10, 'default' => 0, 'comment' => '已消费积分'];

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
        return '{{%task_user}}';
    }

}
