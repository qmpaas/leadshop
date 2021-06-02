<?php
/**
 * 搜索
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace leadmall\app;

use Yii;
use leadmall\Map;
use basics\app\BasicsController as BasicsModules;

class SearchController extends BasicsModules implements Map
{
    public $modules = [
        'goods' => [
            'module'     => 'goods',
            'controller' => 'index',
        ],
        'setting' => [
            'module'     => 'setting',
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

    public function actionCreate()
    {
    	$include = Yii::$app->request->get('include', '');
        $module  = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }

        return $this->runModule($module['module'], $module['controller'], "search");
        
    }

}
