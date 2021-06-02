<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   wiki
 * @Last Modified time: 2021-01-23 15:05:52
 *
 */
namespace leadmall\api;

use leadmall\Map;
use system\api\AccountController as AccountModules;

/**
 * 后台用户管理器
 */
class LoginController extends AccountModules implements Map
{

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
        return $this->actionLogin();
    }

}
