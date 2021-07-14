<?php
/**
 * 插件模式
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task\api;

use basics\api\BasicsController as BasicsModules;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=api/leadmall/plugin&include=task&model=task
 */
class DemoController extends BasicsModules
{
    public $modelClass = 'plugins\task\models\Task';

    /**
     * GET多条记录
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $this->manifest();
    }

    /**
     * GET单条记录
     * @return [type] [description]
     */
    public function actionView()
    {
        return 233333;
    }

    /**
     * POST写入
     * @return [type] [description]
     */
    public function actionCreate()
    {

    }

    /**
     * 更新数据
     * @return [type] [description]
     */
    public function actionUpdate()
    {

    }

    /**
     * 删除数据
     * @return [type] [description]
     */
    public function actionDelete()
    {

    }
}
