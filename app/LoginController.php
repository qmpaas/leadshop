<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\app;

use basics\app\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

/**
 * 后台用户管理器
 */
class LoginController extends BasicsModules implements Map
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
        if ($module !== false) {

        }
        return parent::actionIndex();
    }

    /**
     * 后台登录
     *
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $type = Yii::$app->request->get('type');
        if ($type == 'weapp') {
            return $this->runModule("users", "weapp", "createorupdate");
        } elseif ($type == 'wechat') {
            $module = Yii::$app->request->get('include', false);
            switch ($module) {
                case 'login_url':
                    return $this->runModule("users", "wechat", "url");
                    break;
                case 'login':
                    return $this->runModule("users", "wechat", "createorupdate");
                    break;
                default:
                    Error('未定义操作');
                    break;
            }
        } else {
            Error('登录错误');
        }
    }
}
