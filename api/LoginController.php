<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\api;

use leadmall\Map;
use system\api\AccountController as AccountModules;

/**
 * 后台用户管理器
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
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
