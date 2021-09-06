<?php
/**
 * @copyright ©2021 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/6/5
 * Time: 12:00
 */

namespace app\components\subscribe;

class PromoterWithdrawalErrorMessage extends BaseSubscribeMessage
{
    public $money;
    public $name;
    public $time;

    public function tplName()
    {
        return 'promoter_withdrawal_error';
    }

    public function msg()
    {
        return [
            'amount1' => [
                'value' => $this->money,
            ],
            'name2' => [
                'value' => $this->name,
            ],
            'time3' => [
                'value' => $this->time
            ]
        ];
    }

    public function desc()
    {
        return '提现失败通知';
    }
}
