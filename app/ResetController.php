<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
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
