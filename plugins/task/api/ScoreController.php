<?php
/**
 * 插件模式
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task\api;

use basics\api\BasicsController as BasicsModules;
use Yii;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=api/leadmall/plugin&include=task&model=task
 */
class ScoreController extends BasicsModules
{
    public $ModelTask  = 'plugins\task\models\Task';
    public $ModelLog   = 'plugins\task\models\TaskLog';
    public $ModelUser  = 'plugins\task\models\TaskUser';
    public $ModelScore = 'plugins\task\models\TaskScore';

    /**
     * [actionIndex description]
     * @return [type] [description]
     * 通过以下方法可以执行内调公共方法
     * $this->plugins("task", ["demo", "098809809"])
     */
    public function actionIndex()
    {
        //开始结束时间
        $start_time = Yii::$app->request->get('start_time', 0);
        $end_time   = Yii::$app->request->get('end_time', 0);
        //最低最高分数
        $min_score = Yii::$app->request->get('min_score', 0);
        $max_score = Yii::$app->request->get('max_score', 0);
        //用户ID
        $UID = Yii::$app->request->get('UID', 0);
        //默认筛选条件
        $where = ['AND'];
        //组合拼接筛选条件
        if ($start_time) {
            $where[] = [">=", "start_time", $start_time];
        }
        if ($end_time) {
            $where[] = ["<=", "start_time", $end_time];
        }
        if ($min_score) {
            $where[] = [">=", "number", $min_score];
        }
        if ($max_score) {
            $where[] = ["<=", "number", $max_score];
        }
        if ($UID) {
            $where[] = ["=", "UID", $UID];
        }
        return $this->ModelScore::find()->where($where)->orderBy('id DESC')->asArray()->all();
    }

}
