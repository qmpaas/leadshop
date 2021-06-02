<?php
/**
 * 插件
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

/**
 * 执行插件
 * include =>需要加载的插件名称
 *
 */
class PluginController extends BasicsModules implements Map
{
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * 判断是否安装
     * @param  string  $name  插件名
     * @return boolean
     */
    public function is_install($name = '')
    {
        # code...
    }

    /**
     * 获取插件信息
     * @return [type] [description]
     */
    public function get_plugin()
    {
        $include = Yii::$app->request->get('include', '');
        $model   = Yii::$app->request->get('model', '');
        if (!$include) {
            Error('未找到插件');
        }
        $class = ['', 'plugins', $include, 'api', ucfirst($model) . "Controller"];
        $class = implode('\\', $class);
        return (new $class($this->id, $this->module));
    }

    public function actionIndex()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'index');
        $action = in_array('$action', ['view', 'delete', 'update', 'creacte']) ? 'index' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    public function actionView()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'index');
        $action = in_array('$action', ['index', 'delete', 'update', 'creacte']) ? 'index' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    public function actionDelete()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'index');
        $action = in_array('$action', ['view', 'index', 'update', 'creacte']) ? 'index' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    public function actionUpdate()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'index');
        $action = in_array('$action', ['view', 'delete', 'index', 'creacte']) ? 'index' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    public function actionCreate()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'index');
        $action = in_array('$action', ['view', 'delete', 'update', 'index']) ? 'index' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }
}
