<?php
/**
 * 分组管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace leadmall\app;

use leadmall\Map;
use basics\app\BasicsController as BasicsModules;
use Yii;

class GroupController extends BasicsModules implements Map
{

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
        $module = Yii::$app->request->get('include', false);
        switch ($module) {
            case 'goods':
                return $this->runModule("goods", "group", "index");
                break;

            default:
                Error('未定义操作');
                break;
        }

    }

    public function actionView()
    {
        $module = Yii::$app->request->get('m', false);
        switch ($module) {

            default:
                Error('未定义操作');
                break;
        }

    }

    public function actionCreate()
    {
        $module = Yii::$app->request->get('m', false);
        switch ($module) {

            default:
                Error('未定义操作');
                break;
        }

    }

    public function actionUpdate()
    {
        $module = Yii::$app->request->get('m', false);
        switch ($module) {

            default:
                Error('未定义操作');
                break;
        }

    }

    public function actionDelete()
    {
        $module = Yii::$app->request->get('m', false);
        switch ($module) {

            default:
                Error('未定义操作');
                break;
        }

    }
}
