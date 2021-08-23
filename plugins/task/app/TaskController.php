<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\app;

use app\components\subscribe\TaskSendMessage;
use basics\app\BasicsController as BasicsModules;
use sms\app\IndexController as smsController;
use Yii;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=app/leadmall/plugin&include=task&model=task
 */
class TaskController extends BasicsModules
{
    public $ModelTask  = 'plugins\task\models\Task';
    public $ModelLog   = 'plugins\task\models\TaskLog';
    public $ModelUser  = 'plugins\task\models\TaskUser';
    public $ModelScore = 'plugins\task\models\TaskScore';

    /**
     * 获取任务列表信息
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $keyword = Yii::$app->request->get("keyword", "");
        if ($keyword) {
            $task = $this->ModelTask::find()
                ->where(['keyword' => $keyword])
                ->asArray()
                ->one();
            if ($task) {
                if ($task['keyword'] == 'perfect') {
                    $task['remark'] = sprintf($task['remark'], $task['acquire']);
                }
                if ($task['keyword'] == 'binding') {
                    $task['remark'] = sprintf($task['remark'], $task['acquire']);
                }
                if ($task['keyword'] == 'browse') {
                    $UID     = Yii::$app->user->identity->id;
                    $logList = $this->ModelLog::find()
                        ->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])
                        ->andwhere(['>=', 'start_time', strtotime(date("Y-m-d"), time())])
                        ->andwhere(['<', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
                        ->asArray()
                        ->all();
                    $log             = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                    $task['log']     = $log;
                    $task['logList'] = $logList;
                }
                if ($task['keyword'] == 'signin') {
                    $UID         = Yii::$app->user->identity->id;
                    $task['log'] = $this->getLogDay($task, $UID, $task['total']);
                }
                if ($task['keyword'] == 'share') {
                    $UID         = Yii::$app->user->identity->id;
                    $log         = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                    $task['log'] = $log;
                }
                if ($task['keyword'] == 'order') {
                    $UID         = Yii::$app->user->identity->id;
                    $log         = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                    $task['log'] = $log;
                }
                if ($task['keyword'] == 'goods') {
                    $UID         = Yii::$app->user->identity->id;
                    $log         = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                    $task['log'] = $log;
                }
                $task['remark'] = sprintf($task['remark'], $task['total'], $task['acquire']);
                return $task;
            } else {
                return [];
            }
        } else {
            $status = Yii::$app->request->get("status", -1);
            $where  = [];
            if ($status != -1) {
                $where = ["status" => $status];
            }
            $goods_info = Yii::$app->request->get("goods_info", "");
            if ($goods_info) {
                $where = ['and', $where, ['keyword' => ['goods', 'order']]];
            }
            $taskRow = $this->ModelTask::find()->where($where)->asArray()->indexBy('keyword')->all();
            if ($taskRow) {
                foreach ($taskRow as $key => &$task) {
                    if ($task['keyword'] == 'perfect') {
                        $task['declare'] = sprintf($task['remark'], $task['maximum']);
                    }
                    if ($task['keyword'] == 'binding') {
                        $task['declare'] = sprintf($task['remark'], $task['maximum']);
                    }
                    if ($task['keyword'] == 'browse') {
                        $UID             = Yii::$app->user->identity->id;
                        $task['declare'] = sprintf($task['remark'], $task['total'], $task['acquire']);
                        $log             = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                        $task['log']     = $log;
                    }
                    if ($task['keyword'] == 'signin') {
                        $UID             = Yii::$app->user->identity->id;
                        $task['declare'] = sprintf($task['remark'], $task['total'], $task['acquire']);
                        $log             = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                        $task['log']     = $log;
                    }
                    if ($task['keyword'] == 'share') {
                        $UID             = Yii::$app->user->identity->id;
                        $task['declare'] = sprintf($task['remark'], $task['total'], $task['acquire']);
                        $log             = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                        $task['log']     = $log;
                    }
                    if ($task['keyword'] == 'order') {
                        $task['declare'] = sprintf($task['remark'], $task['total'], $task['acquire']);
                        $task['log']     = 0;
                        if (!empty(Yii::$app->user->identity)) {
                            $UID         = Yii::$app->user->identity->id;
                            $log         = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                            $task['log'] = $log;
                        }
                    }
                    if ($task['keyword'] == 'goods') {
                        $task['declare'] = sprintf($task['remark'], $task['total'], $task['acquire']);
                        $task['log']     = 0;
                        if (!empty(Yii::$app->user->identity)) {
                            $UID         = Yii::$app->user->identity->id;
                            $log         = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                            $task['log'] = $log;
                        }
                    };

                    if ($task['keyword'] == 'invite') {
                        $UID             = Yii::$app->user->identity->id;
                        $task['declare'] = sprintf($task['remark'], $task['total'], $task['acquire']);
                        $log             = $this->ModelLog::find()->where(['task_id' => $task['id'], 'status' => 0, 'UID' => $UID])->count('task_id');
                        $task['log']     = $log;
                    }
                }
                return $taskRow;
            } else {
                return $taskRow;
            }
        }
    }

    /**
     * 处理积分领取接口
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //param_type 判断单规格多规格
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //获取数据
            $post       = Yii::$app->request->post();
            $task_id    = Yii::$app->request->post("task_id");
            $UID        = Yii::$app->user->identity->id;
            $returned   = false;
            $ScoreClass = $this->ModelScore::find()
                ->where(["task_id" => $task_id])
                ->andwhere(['status' => 0])
                ->andwhere(['UID' => $UID])
                ->asArray()
                ->all();
            $item = [];
            if ($ScoreClass) {
                $remark = "积分变动";
                //更新积分状态
                $this->ModelScore::updateAll(['status' => 1], [
                    "task_id" => $task_id,
                    'UID'     => $UID,
                ]);
                $number = 0;
                foreach ($ScoreClass as $key => $value) {
                    $number += $value['number'];
                    $item   = $value;
                    $remark = $value['remark'];
                }
                //获取用户信息
                $UserClass = $this->ModelUser::find()->where(["UID" => $UID])->one();
                if (!$UserClass) {
                    $UserClass = (new $this->ModelUser());
                }
                //修改获得的积分
                $item['number'] = $number;
                //处理用户积分信息
                $UserClass->UID = $UID;
                $UserClass->number += $number;
                $UserClass->total += $number;
                //执行积分数据写入
                $UserClass->save();

                //处理执行消息订阅
                \Yii::$app->subscribe
                    ->setUser($UID)
                    ->setPage('plugins/task/index')
                    ->send(new TaskSendMessage([
                        'number'  => $number,
                        'balance' => $UserClass->number,
                        'remark'  => $remark,
                        'time'    => date("Y年m月d日 H:m", time()),
                    ]));

                $UserData = \users\models\User::find()->where(["id" => $UID])->one();
                //判断手机号是否存在
                if ($UserData && $UserData->mobile) {
                    //处理短信模板
                    $event      = array('sms' => []);
                    $event      = json_decode(json_encode($event));
                    $event->sms = array(
                        'type'   => 'score_changes',
                        'mobile' => [$UserData->mobile],
                        'params' => [
                            'name1' => '变动',
                            'name2' => $number,
                            'name3' => $UserClass->number,
                        ],
                    );
                    //执行短信发送
                    (new smsController($this->id, $this->module))->sendSms($event);
                }

            }
            //事务执行
            $transaction->commit();
            //返回结果集
            return $item;
        } catch (Exception $e) {
            $transaction->rollBack();
            Error("批量写入数据失败");
        }
    }

    /**
     * 处理任务接口
     * @return [type] [description]
     */
    public function actionCreate()
    {
        //获取数据
        $post   = Yii::$app->request->post();
        $number = Yii::$app->request->post('number');
        $UID    = Yii::$app->user->identity->id;
        if ($post['keyword'] == 'invite') {
            $UID    = $number;
            $number = Yii::$app->user->identity->id;
        }
        //执行下单操作
        return $this->plugins("task", ["score", [$post['keyword'], $number, $UID]]);
    }

    /**
     * 获取用户真实积分统计
     * @return [type] [description]
     */
    public function getScoreTotal($task, $UID)
    {
        return $this->ModelScore::find()
            ->alias('c')->select([
            'sum(c.number) count',
            'count(1) total',
        ])
            ->where(["UID" => $UID])
            ->andwhere(["task_id" => $task['id']])
            ->andwhere(['>=', 'start_time', strtotime(date("Y-m-d"), time())])
            ->andwhere(['<=', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
            ->asArray()
            ->one();
    }

    /**
     * 获取用户积分获取统计数
     * @param  [type] $task [description]
     * @param  [type] $UID  [description]
     * @return [type]       [description]
     */
    public function getLogTotal($task, $UID)
    {
        return $this->ModelLog::find()
            ->alias('c')->select([
            'sum(c.number) count',
            'count(1) total',
        ])
            ->where(["UID" => $UID])
            ->andwhere(["task_id" => $task['id']])
            ->andwhere(["<>", "status", 1])
            ->andwhere(['>=', 'start_time', strtotime(date("Y-m-d"), time())])
            ->andwhere(['<=', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
            ->asArray()
            ->one();
    }

    /**
     * 特殊记录获取
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getLogDay($task, $UID, $day)
    {
        return $this->ModelLog::find()
            ->alias('c')->select([
            'sum(c.number) count',
            'count(1) total',
        ])
            ->where(["UID" => $UID])
            ->andwhere(["status" => 1])
            ->andwhere(["extend" => 'signin'])
            ->andwhere(['>=', 'start_time', strtotime(date('Y-m-d', strtotime("-$day day")))])
            ->andwhere(['<', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
            ->asArray()
            ->one();
    }

    /**
     * 获取在范围内
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getLogIn($task, $UID, $extend)
    {
        if (!empty($extend)) {
            return $this->ModelLog::find()
                ->alias('c')->select([
                'sum(c.number) count',
                'count(1) total',
            ])
                ->where(["UID" => $UID])
                ->andwhere(['extend' => $extend])
                ->asArray()
                ->one();
        } else {
            return [
                "count" => 0,
                "total" => 0,
            ];
        }

    }

    /**
     * 获取用户统计数
     * @param  [type] $task [description]
     * @param  [type] $UID  [description]
     * @return [type]       [description]
     */
    public function getLogList($task, $UID)
    {
        return $this->ModelLog::find()
            ->alias('c')->select([
            'sum(c.number) count',
            'count(1) total',
        ])
            ->where(["UID" => $UID])
            ->andwhere(["task_id" => $task['id']])
            ->andwhere(["<>", "status", 1])
            ->asArray()
            ->one();
    }

    /**
     * 执行日志添加
     * @param string $number [description]
     */
    public function addLog($number = '', $UID, $task)
    {
        $ModelLog             = new $this->ModelLog();
        $ModelLog->UID        = $UID;
        $ModelLog->task_id    = $task['id'];
        $ModelLog->number     = $number;
        $ModelLog->extend     = $task['keyword'];
        $ModelLog->status     = 0; //未使用
        $ModelLog->start_time = time();
        $ModelLog->insert();
    }

    /**
     * 执行日志添加
     * @param string $number [description]
     */
    public function addScore($number = '', $UID, $id, $type = 'add', $remark = '积分收入', $is_install = false)
    {
        $ModelLog             = new $this->ModelScore();
        $ModelLog->UID        = $UID;
        $ModelLog->task_id    = $id;
        $ModelLog->order_sn   = "";
        $ModelLog->number     = $number;
        $ModelLog->start_time = time();
        $ModelLog->type       = $type;
        $ModelLog->status     = $is_install ? 1 : 0; //标识未领取
        $ModelLog->remark     = $remark;
        $ModelLog->insert();
        //修改状态
        $this->fixLog(1, $UID, $id);
        if ($is_install) {
            //处理用户积分
            $UserInfo = $this->ModelUser::find()->where(["UID" => $UID])->one();
            $UserInfo->number += $number;
            $UserInfo->total += $number;
            $UserInfo->save();
        }

        return ["code" => 0, "msg" => $remark, "data" => $number];
    }

    /**
     * 执行日志状态更新
     * @param string $number [description]
     */
    public function fixLog($status = 1, $UID, $id)
    {
        //根据多条件进行筛选
        $condition = array(
            "and",
            ["UID" => $UID],
            ["task_id" => $id],
            ['>=', 'start_time', strtotime(date("Y-m-d"), time())],
            ['<=', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))],
        );
        $this->ModelLog::updateAll(['status' => $status], $condition);
    }
}
