<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/1/18
 * Time: 11:21
 *
'task_refund_tpl'   => [
'id'              => '310',
'keyword_id_list' => [1, 2, 3, 4],
'title'           => '积分变更提醒',
'categoryId'      => '307', // 类目id
'type'            => 2, // 订阅类型 2--一次性订阅 1--永久订阅
'data'            => [
'character_string1' => '',
'character_string2' => '',
'thing3'            => '',
'time4'             => '',
],
],
 */

namespace app\components\subscribe;

class TaskSendMessage extends BaseSubscribeMessage
{
    public $number;
    public $balance;
    public $remark;
    public $time;

    public function tplName()
    {
        return 'task_refund_tpl';
    }

    public function msg()
    {
        return [
            'character_string1' => [
                'value' => $this->number,
            ],
            'character_string2' => [
                'value' => $this->balance,
            ],
            'thing3'            => [
                'value' => $this->remark,
            ],
            'time4'             => [
                'value' => $this->time,
            ],
        ];
    }

    public function desc()
    {
        return '积分变更提醒';
    }
}
