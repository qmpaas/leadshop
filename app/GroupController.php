<?php
/**
 * 分组管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
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
