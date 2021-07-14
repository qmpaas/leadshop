<?php
/**
 * 商品详情模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task\models;

use framework\common\CommonModels;

class Task extends CommonModels
{
    const id        = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const name      = ['varchar' => 255, 'comment' => '任务名称'];
    const keyword   = ['varchar' => 255, 'comment' => '任务标识符'];
    const formula   = ['varchar' => 255, 'comment' => '计算公式'];
    const icon      = ['varchar' => 255, 'comment' => '任务图标'];
    const type      = ['varchar' => 3, 'default' => 'add', 'comment' => '任务类型'];
    const total     = ['decimal' => '10,2', 'default' => 0, 'comment' => '条件'];
    const acquire   = ['bigint' => 10, 'default' => 1, 'comment' => '获取积分'];
    const maximum   = ['bigint' => 10, 'default' => 1, 'comment' => '最大值'];
    const remark    = ['varchar' => 255, 'comment' => '积分说明'];
    const prompt    = ['varchar' => 255, 'comment' => '积分提示说明'];
    const extra     = ['varchar' => 255, 'comment' => '第三个说明'];
    const url       = ['varchar' => 255, 'comment' => '跳转链接'];
    const status    = ['tinyint' => 1, 'default' => 0, 'comment' => '任务状态 0关闭 1开启'];
    const extend    = ['text' => 0, 'comment' => '扩展配置'];
    const page_tips = ['varchar' => 255, 'comment' => '微页面说明'];

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
        return '{{%task}}';
    }

}
