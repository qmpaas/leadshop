<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   wiki
 * @Last Modified time: 2021-01-26 14:22:23
 */
namespace leadmall\api;

use leadmall\Map;
use basics\api\BasicsController as BasicsModules;
use Yii;

/**
 * 获取选项管理器
 * module 关联模型
 * include 关联控制器
 * type 关联方法
 * filter 条件
 */
class OptionController extends BasicsModules implements Map
{
	public $modules = [
        'service' => [
            'module'     => 'goods',
            'controller' => 'service',
        ],
        'freighttemplate' => [
            'module'     => 'logistics',
            'controller' => 'freighttemplate',
        ],
        'packagefreerules' => [
            'module'     => 'logistics',
            'controller' => 'packagefreerules',
        ],
        'address' => [
            'module'     => 'setting',
            'controller' => 'address',
        ],
        'pages' => [
            'module'     => 'fitment',
            'controller' => 'pages',
        ],
    ];

    public function actionIndex()
    {
    	$include = Yii::$app->request->get('include', '');
        $module  = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }

        return $this->runModule($module['module'], $module['controller'], "option");
    }
}
