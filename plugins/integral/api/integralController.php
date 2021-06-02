<?php
/**
 * 插件模式
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\integral\api;

use basics\api\BasicsController as BasicsModules;

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
