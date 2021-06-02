<?php
/**
 * 回收站管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace leadmall\api;

use leadmall\Map;
use basics\api\BasicsController as BasicsModules;
use Yii;

class RecycleController extends BasicsModules implements Map
{
    public $modules = [
        'goods' => [
            'module'     => 'goods',
            'controller' => 'index',
        ],
        'order' => [
            'module'     => 'order',
            'controller' => 'index',
        ],
    ];

    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionUpdate()
    {
        $include = Yii::$app->request->get('include', '');
        $module  = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }
        return $this->runModule($module['module'], $module['controller'], "restore");
    }

    public function actionDelete()
    {
        $include = Yii::$app->request->get('include', '');
        $module  = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }

        return $this->runModule($module['module'], $module['controller'], "remove");
    }
}
