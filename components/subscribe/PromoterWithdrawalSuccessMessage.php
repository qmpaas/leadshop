<?php
/**
 * @copyright ©2021 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德 569937993@qq.com
 * Date: 2021/6/5
 * Time: 12:00
 */

namespace app\components\subscribe;

class PromoterWithdrawalSuccessMessage extends BaseSubscribeMessage
{
    public $money;
    public $serviceCharge;
    public $type;

    public function tplName()
    {
        return 'promoter_withdrawal_success';
    }

    public function msg()
    {
        return [
            'amount1' => [
                'value' => $this->money,
            ],
            'amount2' => [
                'value' => $this->serviceCharge,
            ],
            'thing3' => [
                'value' => $this->type
            ]
        ];
    }

    public function desc()
    {
        return '提现成功通知';
    }
}