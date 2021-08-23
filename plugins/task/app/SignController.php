<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\app;

use basics\app\BasicsController as BasicsModules;
use Yii;

/**
 * 签到记录
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=app/leadmall/plugin&include=task&model=task
 */
class SignController extends BasicsModules
{
    public $ModelScore = 'plugins\task\models\TaskScore';

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
        //根据多条件进行筛选
        $condition = array(
            "and",
            ["UID" => $UID],
            ["task_id" => 3],
            ['>=', 'start_time', strtotime(date('Y-m-d', strtotime('-2 day')))],
            ['<=', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))],
        );
        //获取积分
        return $this->ModelScore::find()
            ->where($condition)
            ->orderBy("start_time ASC")
            ->andWhere($condition)
            ->asArray()
            ->all();
    }
}
