<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\integral\app;

use basics\app\BasicsController as BasicsModules;

/**
 * 执行插件
 * include =>需要加载的插件名称
 *
 */
class integralController extends BasicsModules
{
    public $modelClass = 'plugins\integral\models\Integral';

    public function actionIndex()
    {
        return $this->modelClass::find()->asArray()->all();
    }
}
