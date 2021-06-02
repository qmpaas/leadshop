<?php
/**
 * 分组管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace leadmall\api;

use leadmall\Map;
use basics\api\BasicsController as BasicsModules;
use Yii;

class ExportController extends BasicsModules implements Map
{
    public $modules = [
        'order' => [
            'module'     => 'order',
            'controller' => 'export',
        ],
        'orderafter' => [
            'module'     => 'order',
            'controller' => 'afterexport',
        ],
        'goods' => [
            'module'     => 'goods',
            'controller' => 'export',
        ],
        'user' => [
            'module'     => 'users',
            'controller' => 'export',
        ]
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

    public function actionIndex()
    {
        $include = Yii::$app->request->get('include', '');
        $module  = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }

        return $this->runModule($module['module'], $module['controller'], "index");
    }

    public function actionView()
    {
        $include = Yii::$app->request->get('include', '');
        $module  = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }

        return $this->runModule($module['module'], $module['controller'], "view");

    }

    public function actionCreate()
    {
        $include = Yii::$app->request->get('include', '');
        $module  = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }

        return $this->runModule($module['module'], $module['controller'], "create");

    }

    public function actionUpdate()
    {
        Error('占位方法');

    }

    public function actionDelete()
    {
        $include = Yii::$app->request->get('include', '');
        $module  = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }

        return $this->runModule($module['module'], $module['controller'], "delete");
    }
}
