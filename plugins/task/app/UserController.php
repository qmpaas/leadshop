<?php
/**
 * 插件模式
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task\app;

use basics\app\BasicsController as BasicsModules;
use Yii;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=app/leadmall/plugin&include=task&model=task
 */
class UserController extends BasicsModules
{
    public $modelUser = 'plugins\task\models\TaskUser';

    /**
     * [actionIndex description]
     * @return [type] [description]
     */
    public function actionIndex()
    {
        /**
         * 处理用户积分
         */
        $UID = Yii::$app->user->identity->id;
        //获取积分
        $data = $this->modelUser::find()->where(["UID" => $UID])->asArray()->one();
        if ($data) {
            return $data;
        } else {
            $UserClass      = (new $this->modelUser());
            $UserClass->UID = $UID;
            //执行积分数据写入
            $UserClass->save();
            return $UserClass::find()->where(['UID'=>$UID])->asArray()->one();
        }
    }

    /**
     * POST写入
     * @return [type] [description]
     */
    public function actionCreate()
    {

    }
}
