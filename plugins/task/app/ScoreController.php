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
class ScoreController extends BasicsModules
{
    public $ModelTask  = 'plugins\task\models\Task';
    public $ModelLog   = 'plugins\task\models\TaskLog';
    public $ModelUser  = 'plugins\task\models\TaskUser';
    public $ModelScore = 'plugins\task\models\TaskScore';

    /**
     * 获取已经完成和可以领取积分
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $type    = Yii::$app->request->get("type", null);
        $keyword = Yii::$app->request->get("keyword", null);
        $status  = Yii::$app->request->get("status", null);
        $today   = Yii::$app->request->get("today", 1);
        $UID     = Yii::$app->user->identity->id;
        //获取单个任务是否完成
        if ($type == 'single') {
            $where = ['UID' => $UID];
            if ($status !== null) {
                $where = ['and', $where, ['s.status' => $status]];
            }
            if ($today) {
                $where = ['and', $where, ['>=', 's.start_time', strtotime(date("Y-m-d"), time())]];
                $where = ['and', $where, ['<=', 's.start_time', strtotime(date('Y-m-d', strtotime('+1 day')))]];
            }
            return $this->ModelScore::find()
                ->joinWith('task as t')
                ->from(['s' => $this->ModelScore::tableName()])
                ->where($where)
                ->andWhere(['t.keyword' => $keyword])
                ->asArray()
                ->one();
        }
        //判断是否是要获取已经完成任务的
        if ($type == 'fulfil') {
            $row_A = $this->ModelScore::find()
                ->joinWith('task as t')
                ->from(['s' => $this->ModelScore::tableName()])
                ->where(['s.status' => 1, 'UID' => $UID])
                ->andWhere(['>=', 's.start_time', strtotime(date("Y-m-d"), time())])
                ->andWhere(['<=', 's.start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
                ->andWhere(['not in', 't.keyword', ['perfect', 'binding', 'order', 'goods', 'invite']])
                ->orderBy("t.id DESC")
                ->asArray()
                ->all();
            $order_data = $this->ModelScore::find()
                ->joinWith('task as t')
                ->from(['s' => $this->ModelScore::tableName()])
                ->where(['UID' => $UID])
                ->andWhere(['>=', 's.start_time', strtotime(date("Y-m-d"), time())])
                ->andWhere(['<=', 's.start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
                ->andWhere(['t.keyword' => 'order'])
                ->orderBy("t.id DESC")
                ->asArray()
                ->all();
            $row_B = [];
            //处理次数选择
            if ($order_data) {
                $sum  = count($order_data);
                $item = $order_data[0];
                if ($item['task']['maximum']) {
                    if ($sum >= $item['task']['maximum']) {
                        if ($item['status']) {
                            $row_B[] = $item;
                        } else {
                            $row_B = [];
                        }
                    }
                }
            }
            $row_C = $this->ModelScore::find()
                ->joinWith('task as t')
                ->from(['s' => $this->ModelScore::tableName()])
                ->where(['s.status' => 1, 'UID' => $UID])
                ->andWhere(['in', 't.keyword', ['perfect', 'binding']])
                ->asArray()
                ->all();
            $goods_data = $this->ModelScore::find()
                ->joinWith('task as t')
                ->from(['s' => $this->ModelScore::tableName()])
                ->where(['UID' => $UID])
                ->andWhere(['>=', 's.start_time', strtotime(date("Y-m-d"), time())])
                ->andWhere(['<=', 's.start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
                ->andWhere(['t.keyword' => 'goods'])
                ->orderBy("t.id DESC")
                ->asArray()
                ->all();
            $row_D = [];
            //处理次数选择
            if ($goods_data) {
                $sum  = count($goods_data);
                $item = $goods_data[0];
                if ($item['task']['maximum']) {
                    if ($sum >= $item['task']['maximum']) {
                        if ($item['status']) {
                            $row_D[] = $item;
                        } else {
                            $row_D = [];
                        }
                    }
                }
            }
            //获取邀请记录
            $invite_data = $this->ModelScore::find()
                ->joinWith('task as t')
                ->from(['s' => $this->ModelScore::tableName()])
                ->where(['UID' => $UID])
                ->andWhere(['>=', 's.start_time', strtotime(date("Y-m-d"), time())])
                ->andWhere(['<=', 's.start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
                ->andWhere(['t.keyword' => 'invite'])
                ->orderBy("t.id DESC")
                ->asArray()
                ->all();
            $row_E = [];
            //处理次数选择
            if ($invite_data) {
                $sum  = count($invite_data);
                $item = $invite_data[0];
                if ($item['task']['maximum']) {
                    if ($sum >= ceil($item['task']['maximum'] / $item['task']['acquire'])) {
                        if ($item['status']) {
                            $row_E[] = $item;
                        } else {
                            $row_E = [];
                        }
                    }
                }
            }
            return array_merge($row_A, $row_B, $row_C, $row_D, $row_E);
        } else {
            $ScoreClass = $this->ModelScore::find()
                ->joinWith('task as t')
                ->from(['s' => $this->ModelScore::tableName()])
                ->where(['s.status' => 0, 'UID' => $UID])
                ->asArray()
                ->all();
            return $ScoreClass;
        }
    }

    /**
     * 下单结束后创建减少积分
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //执行数据
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //获取数据
            $order_id   = Yii::$app->request->post('order_id');
            $number     = Yii::$app->request->post('number');
            $id         = Yii::$app->request->get("id");
            $UID        = Yii::$app->user->identity->id;
            $returned   = false;
            $ScoreClass = $this->ModelScore::find()
                ->where(["id" => $id])
                ->andwhere(['status' => 0])
                ->andwhere(['UID' => $UID])
                ->one();
            if ($ScoreClass) {
                //设置积分状态
                $ScoreClass->status = 1;
                $ScoreClass->save();
                //获取用户信息
                $UserClass = $this->ModelUser::find()->where(["UID" => $UID])->one();
                if (!$UserClass) {
                    $UserClass = (new $this->ModelUser());
                }
                //处理用户积分信息
                $UserClass->UID = $UID;
                $UserClass->number -= $number;
                $UserClass->consume -= $number;
                //执行积分数据写入
                $returned = $UserClass->save();
            }
            //事务执行
            $transaction->commit();
            //返回结果集
            return $returned;
        } catch (Exception $e) {
            $transaction->rollBack();
            Error("批量写入数据失败");
        }
    }

    /**
     * 预下单的时候创建扣除积分
     * @return [type] [description]
     */
    public function actionCreate()
    {
        //获取数据
        $order_id = Yii::$app->request->post('order_id');
        $remark   = Yii::$app->request->post('remark', '购买商品');
        $status   = Yii::$app->request->post('status', 0);
        $number   = Yii::$app->request->post('number');
        $id       = Yii::$app->request->get("id");
        $UID      = Yii::$app->user->identity->id;
        //预下单积分处理
        $ScoreClass = $this->ModelScore::find()->where(["order_id" => $order_id])->one();
        if (!$ScoreClass) {
            $ScoreClass = (new $this->ModelScore());
        }
        $ScoreClass->UID        = $UID;
        $ScoreClass->start_time = time();
        $ScoreClass->status     = $status;
        $ScoreClass->order_id   = $order_id;
        $ScoreClass->type       = 'del';
        $ScoreClass->number     = $number;
        $ScoreClass->remark     = $remark;
        return $ScoreClass->save();
    }
}
