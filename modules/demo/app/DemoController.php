<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/25
 * Time: 14:09
 */
namespace demo\app;

use app\components\PaymentOrder;
use framework\common\BasicController;

class DemoController  extends BasicController
{
    public function actionPay()
    {
        $res = \Yii::$app->payment->unifiedOrder( new PaymentOrder([
              'openid' => 'oN3X_0JMS7rpuD35M15FxCCvxsuQ',
              'orderNo' => '123',
              'amount' => 1,
              'title' => '哈哈哈',
              'notify' => 'a'
          ]));
        exit(json_encode($res));
    }
}
