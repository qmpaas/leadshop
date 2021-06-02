<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-05-11 15:08:38
 *
 */
namespace leadmall\api;

use leadmall\Map;
use system\api\AccountController as AccountModules;

/**
 * 后台用户管理器
 */
class ResetController extends AccountModules implements Map
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
        return $this->actionReset();
    }
}
