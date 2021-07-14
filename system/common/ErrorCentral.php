<?php
/**
 * 错误抛出类
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:43:40
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-06-25 17:30:53
 */
namespace framework\common;

use yii\web\ForbiddenHttpException;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\ServerErrorHttpException;

class ErrorCentral
{
    public function __construct($msg = '系统错误', $code = 403, $type = 'wechat')
    {
        if ($code == 403) {
            throw new ForbiddenHttpException($msg);
        } elseif ($code == 416) {
            throw new RangeNotSatisfiableHttpException($msg);
        } else {
            if ($type == 'wechat') {
                throw new WechatHttpException($msg, $code);
            } else {
                throw new ServerErrorHttpException('系统错误');
            }
        }
    }
}
