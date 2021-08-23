<?php
/**
 * 搜索
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

class SearchController extends BasicsModules implements Map
{
    public $modules = [
        'goods'         => [
            'module'     => 'goods',
            'controller' => 'index',
        ],
        'order'         => [
            'module'     => 'order',
            'controller' => 'index',
        ],
        'orderafter'    => [
            'module'     => 'order',
            'controller' => 'after',
        ],
        'orderevaluate' => [
            'module'     => 'order',
            'controller' => 'evaluate',
        ],
        'setting'       => [
            'module'     => 'setting',
            'controller' => 'index',
        ],
        'fitment'       => [
            'module'     => 'fitment',
            'controller' => 'index',
        ],
        'users'         => [
            'module'     => 'users',
            'controller' => 'index',
        ],
        'label'         => [
            'module'     => 'users',
            'controller' => 'label',
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

        $module = $this->modules[$include] ?? false;
        if (!$module) {
            Error('未定义操作');
        }

        return $this->runModule($module['module'], $module['controller'], "search");
    }

}
