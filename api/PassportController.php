<?php

namespace leadmall\api;

use leadmall\Map;
use system\api\AccountController as AccountModules;

/**
 * 后台用户管理器
 */
class PassportController extends AccountModules implements Map
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

    public function actionUpdate()
    {
        return $this->changePwd();
    }
}
