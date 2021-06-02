<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-05-12 09:40:24
 *
 */
namespace leadmall\app;

use leadmall\Map;
use users\app\LoginController as LoginModules;

/**
 * 后台用户管理器
 */
class ResetController extends LoginModules implements Map
{

    public function getUserInfo()
    {
        # code...
    }
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    /**
     * 后台登录
     * @return [type] [description]
     */
    public function actionCreate()
    {
        return $this->actionReset();
    }
}
