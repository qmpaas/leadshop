<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\app;

use basics\app\BasicsController as BasicsModules;
use leadmall\Map;

class CloudController extends BasicsModules
{
    public function actionIndex()
    {
        return \Yii::$app->cloud->auth->getAuthData();
    }
}
