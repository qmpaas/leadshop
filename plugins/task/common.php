<?php
/**
 * 积分任务公共模型
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace plugins\task;

use app\components\subscribe\TaskSendMessage;
use basics\common\BasicsController as BasicsCommon;
use sms\app\IndexController as smsController;
use Yii;

class common extends BasicsCommon
{
    public $ModelTask      = 'plugins\task\models\Task';
    public $ModelLog       = 'plugins\task\models\TaskLog';
    public $ModelUser      = 'plugins\task\models\TaskUser';
    public $ModelScore     = 'plugins\task\models\TaskScore';
    public $ModelUserinfo  = 'users\models\User';
    public $ModelOrderinfo = 'order\models\Order';

    /**
     * 处理接口白名单
     * @var array
     */
    public $whitelists = ['score', 'index', 'order'];

    public function actionDemo($UID)
    {
        return 12313;
    }

    /**
     * 处理任务接口
     * @return [type] [description]
     */
    public function actionScore($keyword, $number = 0, $UID = "", $order_sn = "")
    {
        //获取对应回来的任务信息
        $task = $this->ModelTask::find()->where(array("keyword" => $keyword))->asArray()->one();

        //处理文件
        if (!$task) {
            return false;
        }

        //处理任务状态为开启
        if (!$task['status']) {
            return false;
        }

        //获取用户信息
        if ($UID <= 0) {
            $UID = Yii::$app->user->identity->id;
        }

        //根据参数获取
        switch ($task['type']) {
            # 第一种类型
            # 时间限制是按照当天时间计算
            case 1:
                //开始计算
                // $today    = strtotime(date("Y-m-d"), time());
                // $tomorrow = strtotime(date('Y-m-d', strtotime('+1 day')));
                // P([$today, $tomorrow, time()]);

                //统计数量
                $UserLog = $this->getLogTotal($task, $UID);

                //统计真实获得积分
                $ScoreLog = $this->getScoreTotal($task, $UID);

                //判断积分值必须大于0
                if ($task['acquire'] && $task['status']) {

                    /**
                     * 一下条件类型相同的-后面优化的时候合并数据链，先期怕有变动调试所以每一条都单独处理
                     * 存在代码冗余
                     * 处理信息符合条件 A-2
                     * 满多少值在规定范围内
                     */
                    if ($task['keyword'] == 'goods') {
                        if ($order_sn) {
                            //搜索是否已经存在
                            $OrderInfo = $this->ModelOrderinfo::find()
                                ->andwhere(['order_sn' => $order_sn])
                                ->asArray()
                                ->one();
                            // P2($OrderInfo);
                            // P2([floatval($OrderInfo['pay_amount']), floatval($task['total'])]);

                            if (floatval($OrderInfo['pay_amount']) >= floatval($task['total'])) {
                                $acquire = floor($OrderInfo['pay_amount'] / floatval($task['total'])) * $task['acquire'];
                                // P2($acquire);
                                //判断是否存在最大限制
                                if ($task['maximum']) {
                                    //判断如果小于极限的时候
                                    if ($ScoreLog['total'] >= intval($task['maximum'])) {
                                        return true;
                                    }
                                }
                                //单条记录
                                $this->addLog($acquire, $UID, $task);
                                $msg = sprintf($task['remark'], $task['total'], $acquire);

                                //积分记录
                                return $this->addScore($acquire, $UID, $task['id'], 'add', $msg, false, "", $order_sn);
                            }
                        }
                    }

                    /**
                     * 处理信息符合条件 A-1
                     * 满多少值在规定范围内
                     */
                    if ($task['keyword'] == 'order') {
                        // P2($UserLog);
                        // P2($task);
                        if ($UserLog['total'] <= $task['total']) {
                            $acquire = $task['acquire'];
                            //判断是否存在最大限制
                            if ($task['maximum']) {
                                //判断如果小于极限的时候
                                if ($ScoreLog['total'] >= intval($task['maximum'])) {
                                    return true;
                                }
                            }
                            //单条记录
                            $this->addLog($acquire, $UID, $task);
                            //重新统计数据
                            $UserLog = $this->getLogTotal($task, $UID);
                            //执行判断
                            if (($UserLog['total'] == $task['total']) && $ScoreLog['total'] <= $task['maximum']) {
                                $msg = sprintf($task['remark'], $task['total'], $acquire);
                                //积分记录
                                return $this->addScore($acquire, $UID, $task['id'], 'add', $msg, false, "", $order_sn);
                            }
                        }
                    }

                    // if ($task['keyword'] == 'order') {
                    //     if ($UserLog['total'] < $task['total']) {
                    //         $acquire = $task['acquire'];
                    //         //判断是否存在最大限制
                    //         if ($task['maximum']) {
                    //             //判断如果小于极限的时候
                    //             if ($ScoreLog['count'] < intval($task['maximum'])) {
                    //                 //判断如果限制的积分数量不会整除的时候
                    //                 if ((($ScoreLog['total'] + 1) * $task['acquire']) > intval($task['maximum'])) {
                    //                     $acquire = intval($task['maximum']) - ($ScoreLog['total'] * $task['acquire']);
                    //                 }
                    //             } else {
                    //                 return true;
                    //             }
                    //         }

                    //         //单条记录
                    //         $this->addLog($acquire, $UID, $task);
                    //         $msg = sprintf($task['remark'], $task['total'], $acquire);

                    //         //积分记录
                    //         return $this->addScore($acquire, $UID, $task['id'], 'add', $msg);
                    //     }
                    // }

                    // 原始方案备份
                    // if ($task['keyword'] == 'order') {
                    //     if (($UserLog['total'] < $task['total']) && $ScoreLog['total'] < (intval($task['maximum']) / intval($task['acquire']))) {
                    //         $this->addLog($task['acquire'] / $task['total'], $UID, $task);
                    //     }
                    //     //重新统计数据
                    //     $UserLog = $this->getLogTotal($task, $UID);
                    //     //判断是否要写入积分池
                    //     $UserLog['total'] == $task['total'];

                    //     if (($UserLog['total'] == $task['total']) && $ScoreLog['total'] < $task['maximum']) {
                    //         $msg = sprintf($task['remark'], $task['total'], $task['acquire']);
                    //         return $this->addScore($task['acquire'], $UID, $task['id'], 'add', $msg);
                    //     }
                    // }

                    /**
                     * 处理信息符合条件 A-2
                     * 满多少值在规定范围内
                     */
                    if ($task['keyword'] == 'signin') {
                        if (($UserLog['total'] < $task['total']) && $ScoreLog['count'] <= $task['maximum']) {
                            //单条记录
                            $this->addLog($task['acquire'], $UID, $task);
                            $msg = sprintf($task['remark'], $task['acquire']);
                            //积分记录
                            $ret = $this->addScore($task['acquire'], $UID, $task['id'], 'add', $msg, 1);
                            //执行连续签到
                            $ret2 = $this->actionScore("sustain", 1, $UID);
                            if (is_array($ret2)) {
                                if ($ret2['msg']) {
                                    $ret['data'] += $ret2['data'];
                                    $ret['msg'] = $msg . ',' . $ret2['msg'];
                                }
                                return $ret;
                            } else {
                                //返回数据
                                return $ret;
                            }

                        }
                    }

                    /**
                     * 处理信息符合条件 A-1
                     * 分享转发
                     */
                    if ($task['keyword'] == 'share') {
                        if (($UserLog['total'] < $task['total']) && $ScoreLog['total'] < 1) {
                            $this->addLog($task['acquire'] / $task['total'], $UID, $task);
                        }
                        //重新统计数据
                        $UserLog = $this->getLogTotal($task, $UID);
                        //判断是否要写入积分池
                        if (($UserLog['total'] >= $task['total'])) {
                            $msg = sprintf($task['remark'], $task['total'], $task['acquire']);
                            return $this->addScore($task['acquire'], $UID, $task['id'], 'add', $msg);
                        }
                    }

                    /**
                     * 处理信息符合条件 A-1
                     * 浏览商品
                     */
                    if ($task['keyword'] == 'browse') {
                        //如果已经存在则不记录
                        if ($this->getLog($number, $UID, $task, true)) {
                            return true;
                        }
                        if (($UserLog['total'] < $task['total']) && $ScoreLog['total'] < 1) {
                            $this->addLog($number, $UID, $task);
                        }
                        //重新统计数据
                        $UserLog = $this->getLogTotal($task, $UID);
                        //判断是否要写入积分池
                        if (($UserLog['total'] >= $task['total'])) {
                            $msg = sprintf($task['remark'], $task['total'], $task['acquire']);
                            return $this->addScore($task['acquire'], $UID, $task['id'], 'add', $msg);
                        }
                    }

                    // if ($UserLog['total'] <= $task['total']) {
                    //     $acquire = $task['acquire'];
                    //     //判断是否存在最大限制
                    //     if ($task['maximum']) {
                    //         //判断如果小于极限的时候
                    //         if ($ScoreLog['total'] >= intval($task['maximum'])) {
                    //             return true;
                    //         }
                    //     }
                    //     //单条记录
                    //     $this->addLog($acquire, $UID, $task);
                    //     //重新统计数据
                    //     $UserLog = $this->getLogTotal($task, $UID);
                    //     //执行判断
                    //     if (($UserLog['total'] == $task['total']) && $ScoreLog['total'] <= $task['maximum']) {
                    //         $msg = sprintf($task['remark'], $task['total'], $acquire);
                    //         //积分记录
                    //         return $this->addScore($acquire, $UID, $task['id'], 'add', $msg, false, "", $order_sn);
                    //     }
                    // }

                    /**
                     * 处理信息符合条件 A-1
                     * 邀请好友
                     */
                    if ($task['keyword'] == 'invite') {
                        //搜索是否已经存在
                        $UserData = $this->ModelLog::find()
                            ->where(["task_id" => $task['id']])
                            ->andwhere(['number' => $number])
                            ->andwhere(['extend' => $task['keyword']])
                            ->one();

                        //判断用户是否存在的问题
                        if ($UserData) {
                            return true;
                        }

                        //超过极限不执行
                        if ($ScoreLog['count'] >= $task['maximum']) {
                            return true;
                        }

                        //判断超限的情况
                        $_mod = ceil($task['maximum'] / $task['acquire']);

                        //判断限制记录
                        //$UserLog['total']标识邀请记录
                        if ($UserLog['total'] < $task['total']) {
                            $this->addLog($number, $UID, $task);
                        }
                        //重新统计数据
                        $UserLog = $this->getLogTotal($task, $UID);

                        $num = isset($ScoreLog['total']) ? $ScoreLog['total'] + 1 : 1;

                        if ($UserLog['total'] >= $task['total']) {
                            $msg     = sprintf($task['remark'], $task['total'], $task['acquire']);
                            $acquire = $task['acquire'];
                            if ($ScoreLog['total'] >= ($_mod - 1)) {
                                $acquire = $task['maximum'] - ($acquire * ($_mod - 1));
                            }
                            return $this->addScore($acquire, $UID, $task['id'], 'add', $msg);
                        }

                    }

                    /**
                     * 处理信息符合条件 A-3
                     * 连续签到的特殊处理
                     */
                    if ($task['keyword'] == 'sustain') {
                        $UserLog = $this->getLogDay($task, $UID, $task['total']);
                        //判断是否要写入积分池
                        if (($UserLog['total'] == $task['total']) && $ScoreLog['count'] < $task['maximum']) {
                            $msg = sprintf($task['remark'], $task['total'], $task['acquire']);
                            return $this->addScore($task['acquire'], $UID, $task['id'], 'add', $msg, 1);
                        }
                    }

                    return true;
                }

                break;
            # 第二种类型
            # 累计多少此处可以获得一定数量积分
            case 2:
                //判断积分值必须大于0
                if ($task['acquire'] && $task['status']) {
                    //用于存储个人信息
                    if ($task['keyword'] == 'perfect') {
                        $tak_extend = to_array($task['extend']);

                        /**
                         * 处理信息符合条件 B-1
                         * 读取用户的扩展信息
                         */
                        $UserLog = $this->getLogIn($task, $UID, to_array($task['extend']));
                        //统计真实获得积分
                        $ScoreLog = $this->getScoreTotal($task, $UID, false);
                        //搜索是否已经存在
                        $UserInfo = $this->ModelUserinfo::find()
                            ->andwhere(['id' => $UID])
                            ->asArray()
                            ->one();

                        //循环获取已经设置的属性
                        $extend_list = [];
                        foreach ($tak_extend as $key => $value) {
                            if (isset($UserInfo[$value])) {
                                if (!empty($UserInfo[$value])) {
                                    $extend_list[] = $value;
                                }
                            }
                        }

                        // P($extend_list);
                        // P(count(to_array($task['extend'])));

                        //判断是否可以获得积分
                        if ((count($extend_list) == count(to_array($task['extend']))) && !$ScoreLog['count']) {
                            $msg = sprintf($task['remark'], $task['acquire']);
                            return $this->addScore($task['acquire'], $UID, $task['id'], 'add', $msg);
                        }

                        return true;
                    }
                    //处理手机号的绑定
                    if ($task['keyword'] == 'binding') {
                        //搜索是否已经存在
                        $UserData = $this->ModelLog::find()
                            ->where(["task_id" => $task['id']])
                            ->andwhere(['UID' => $UID])
                            ->andwhere(['extend' => $task['keyword']])
                            ->one();
                        if (!$UserData) {
                            $ModelLog             = new $this->ModelLog();
                            $ModelLog->UID        = $UID;
                            $ModelLog->task_id    = $task['id'];
                            $ModelLog->number     = $task['acquire'] / $task['total'];
                            $ModelLog->extend     = $task['keyword'];
                            $ModelLog->status     = 0; //未使用
                            $ModelLog->start_time = time();
                            $ModelLog->insert();
                        }
                        /**
                         * 处理信息符合条件 B-1
                         * 读取用户的扩展信息
                         */
                        $UserLog = $this->getLogIn($task, $UID, $task['keyword']);
                        //统计真实获得积分
                        $ScoreLog = $this->getScoreTotal($task, $UID, false);
                        //判断是否要写入积分池
                        if (($UserLog['total'] == count(to_array($task['extend']))) && $ScoreLog['count'] < $task['maximum']) {
                            $msg = sprintf($task['remark'], $task['acquire']);
                            return $this->addScore($task['acquire'], $UID, $task['id'], 'add', $msg);
                        }
                        return true;
                    }
                }
                break;
            # 第三种类型-用于扩展预留
            # 周期性获得积分
            case 3:
                # code...
                break;
            # 第四种类型-用于扩展预留
            # 完成特定的任务
            default:
                # code...
                break;
        }
    }

    /**
     * 获取用户真实积分统计
     * @return [type] [description]
     */
    public function getScoreTotal($task, $UID, $is_day = true)
    {
        $where = ["UID" => $UID, "task_id" => $task['id']];
        if ($is_day) {
            $where = ['and', $where, ['>=', 'start_time', strtotime(date("Y-m-d"), time())]];
            $where = ['and', $where, ['<=', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))]];
        }

        return $this->ModelScore::find()
            ->alias('c')->select([
            'sum(c.number) count',
            'count(1) total',
            'status',
        ])
            ->where($where)
        // ->andwhere(['>=', 'start_time', strtotime(date("Y-m-d"), time())])
        // ->andwhere(['<=', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))])
            ->asArray()
            ->one();
    }

    /**
     * 获取用户积分获取统计数
     * @param  [type] $task [description]
     * @param  [type] $UID  [description]
     * @return [type]       [description]
     */
    public function getLogTotal($task, $UID, $status = 0)
    {
        $where = ["UID" => $UID];
        if ($status != -1) {
            $where = ['and', $where, ["status" => $status]];
        }
        return $this->ModelLog::find()
            ->alias('c')->select([
            'sum(c.number) count',
            'count(1) total',
        ])
            ->where($where)
            ->andwhere(["task_id" => $task['id']])
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
     * 默认为未使用
     * @param string $number [description]
     */
    public function addLog($number = '', $UID, $task, $status = 0)
    {
        $ModelLog             = new $this->ModelLog();
        $ModelLog->UID        = $UID;
        $ModelLog->task_id    = $task['id'];
        $ModelLog->number     = $number;
        $ModelLog->extend     = $task['keyword'];
        $ModelLog->status     = $status; //未使用
        $ModelLog->start_time = time();
        $ModelLog->insert();
    }

    /**
     * 执行日志添加
     * @param string $number [description]
     */
    public function getLog($number = '', $UID, $task, $is_day = false)
    {
        $where = [
            "UID"    => $UID,
            "extend" => $task['keyword'],
            "status" => 0,
            "number" => $number,
        ];
        if ($is_day) {
            //根据多条件进行筛选
            $where = ['and', $where, ['>=', 'start_time', strtotime(date("Y-m-d"), time())]];
            $where = ['and', $where, ['<=', 'start_time', strtotime(date('Y-m-d', strtotime('+1 day')))]];

        }
        return $this->ModelLog::find()->where($where)->one();
    }

    /**
     * 执行积分记录
     * @param  string  $number [description]
     * @param  [type]  $UID    [description]
     * @param  integer $id     [description]
     * @param  string  $type   [description]
     * @param  string  $remark [description]
     * @return [type]          [description]
     */
    public function actionScoreadd($number = '', $UID, $id = 0, $type = 'add', $remark = '积分收入', $identifier = "")
    {
        return $this->addScore($number, $UID, $id, $type, $remark, 1, $identifier);
    }

    /**
     * [FunctionName description]
     * @param string $value [description]
     */
    public function actionOrder($number = '', $UID, $order_sn = "", $identifier = "order")
    {
        $remark = "下单购买商品";
        //执行几分
        $ModelLog             = new $this->ModelScore();
        $ModelLog->UID        = $UID;
        $ModelLog->task_id    = 0;
        $ModelLog->order_sn   = $order_sn;
        $ModelLog->number     = -$number;
        $ModelLog->start_time = time();
        $ModelLog->type       = "del";
        $ModelLog->identifier = $identifier;
        $ModelLog->status     = 1;
        $ModelLog->remark     = $remark;
        $ModelLog->insert();
        //执行用户几分修改
        $this->addUserscore($UID, -$number, $remark);
        return ["code" => 0, "msg" => $remark, "data" => -$number];
    }

    /**
     * 执行日志添加
     * @param string $number [description]
     */
    public function addScore($number = '', $UID, $id = 0, $type = 'add', $remark = '积分收入', $is_install = false, $identifier = "", $order_sn = '')
    {
        $ModelLog             = new $this->ModelScore();
        $ModelLog->UID        = $UID;
        $ModelLog->task_id    = $id;
        $ModelLog->order_sn   = $order_sn;
        $ModelLog->number     = $number;
        $ModelLog->start_time = time();
        $ModelLog->type       = $type;
        $ModelLog->identifier = $identifier;
        $ModelLog->status     = $is_install ? 1 : 0; //标识未领取
        $ModelLog->remark     = $remark;
        $ModelLog->insert();
        //修改状态
        $this->fixLog(1, $UID, $id);
        if ($is_install) {
            //处理用户积分
            $this->addUserscore($UID, $number, $remark);
        }

        return ["code" => 0, "msg" => $remark, "data" => $number];
    }

    /**
     * 更新积分数据
     * @param string $number [description]
     */
    public function updateScore($number = '', $UID, $id = 0, $type = 'add', $remark = "")
    {
        $ModelLog = $this->ModelScore::find()
            ->where([
                "UID"     => $UID,
                "task_id" => $id,
                "type"    => $type,
                "status"  => 0,
            ])
            ->one();
        if ($ModelLog) {
            $ModelLog->number       = $number;
            $ModelLog->updated_time = time();
            //修改状态
            $this->fixLog(1, $UID, $id);
            return $ModelLog->save();
        } else {
            $this->addScore($number, $UID, $id, $type, $remark);
        }
    }

    /**
     * 处理用户数据添加
     */
    public function addUserscore($UID, $number, $remark)
    {
        $UserInfo  = $this->ModelUser::find()->where(["UID" => $UID])->one();
        $changeNum = $number;
        if (!$UserInfo) {
            $UserInfo      = new $this->ModelUser();
            $UserInfo->UID = $UID;
        }

        $total = 0;

        if ($UserInfo->number) {
            $number = $UserInfo->number + $number;
        }
        if ($UserInfo->total) {
            $total = $UserInfo->total + $total;
        }

        if ($number < 0) {
            $number = 0;
        }
        //处理界面
        $balance          = $number;
        $UserInfo->number = $number;
        $UserInfo->total  = $total;
        $UserInfo->save();

        //处理执行消息订阅
        \Yii::$app->subscribe
            ->setUser($UID)
            ->setPage('plugins/task/index')
            ->send(new TaskSendMessage([
                'number'  => $changeNum,
                'balance' => $balance,
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
                    'name2' => $changeNum,
                    'name3' => $number,
                ],
            );
            //执行短信发送
            (new smsController($this->id, $this->module))->sendSms($event);
        }

        return $UserInfo;
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
