<?php
/**
 * 搜索
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
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
