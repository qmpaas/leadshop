<?php
/**
 * 商品详情模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task\models;

use framework\common\CommonModels;

class TaskLog extends CommonModels
{
    const id         = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const task_id    = ['bigint' => 20, 'comment' => '任务ID'];
    const UID        = ['bigint' => 20, 'comment' => '用户ID'];
    const start_time = ['bigint' => 10, 'default' => 0, 'comment' => '开始时间'];
    const status     = ['tinyint' => 3, 'default' => 1, 'comment' => '任务装填： 0 未完成 1 已完成'];
    const number     = ['bigint' => 10, 'default' => 1, 'comment' => '积分分值'];
    const extend     = ['text' => 0, 'comment' => '扩展信息处理'];

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
        return '{{%task_log}}';
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
