<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/18
 * Time: 11:36
 */

namespace app\components;


use yii\base\Model;

class PaymentOrder extends Model
{
    public $orderNo;
    public $amount;
    public $title;
    public $notify;
    public $supportPayTypes;
    public $payType;
    public $openid;
    public $return_url;
    public $attach;

    public function rules()
    {
        return [
            [['orderNo', 'amount', 'title', 'notify','attach'], 'required',],
            [['orderNo'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 128],
            [['notify'], 'string', 'max' => 512],
            [['attach'], 'string', 'max' => 127],
            [['amount'], function ($attribute, $params) {
                if (!is_float($this->amount) && !is_int($this->amount) && !is_double($this->amount)) {
                    $this->addError($attribute, '`amount`必须是数字类型。');
                }
            }],
            [['amount'], 'number', 'min' => 0, 'max' => 100000000],
            [['payType', 'openid', 'return_url'], 'safe'],
            [['payType'], 'default', 'value' => 'wechat']
        ];
    }

    /**
     * PaymentOrder constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!$this->validate()) {
            dd($this->errors);
        }
    }
}
