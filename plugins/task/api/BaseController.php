<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\api;

use basics\common\BasicsController as BasicsModules;
use setting\models\Setting;
use sms\app\IndexController as smsController;
use Yii;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=api/leadmall/plugin&include=task&model=task
 */
class BaseController extends BasicsModules
{
    public $modelClass = 'plugins\task\models\Task';
    public $ModelUser  = 'plugins\task\models\TaskUser';
    public $ModelScore = 'plugins\task\models\TaskScore';
    /**
     * 处理接口白名单
     * @var array
     */
    public $whitelists = ['index', 'score'];

    /**
     * GET多条记录
     * 用于处理定时任务使用
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $timing = Yii::$app->request->get("timing", false);
        $is_url = Yii::$app->request->get("is_url", false);
        $form   = $this->manifest('config');
        $row    = $this->modelClass::find()->asArray()->all();
        $task   = [];
        foreach ($row as $key => $item) {
            $item['extend'] = to_array($item['extend']);
            $task[$key]     = $item;
        }

        if ($is_url) {
            $url = Yii::$app->request->getHostInfo() . Yii::$app->request->url;
            return $url;
        }

        /**
         * 用于处理定时器
         */
        if ($timing) {
            $this->checkAccessToken();
            if ($form['change_time']['start']) {
                P2("执行系统积分清除计划");
                //获取每年的这个时间
                $Y = date('Y');
                $M = date('m', strtotime($form['change_time']['start']));
                $D = date('d', strtotime($form['change_time']['start']));

                //判断是否是今天需要执行
                //此处用于执行数据清零
                if (date('Ymd', strtotime($Y . $M . $D)) == date('Ymd')) {
                    P2("处理积分清零操作");
                    //这里需要执行积分清零接口
                    //第一步：获取这个时间内365天所有获得积分值
                    //第二步：获取这个时间内365天所有消费的分值
                    //第三步：两个分数值相减，如果大于0的，就从总积分中，减去这个分值
                    //处理积分时间

                    // $aaaa = \Yii::$app->subscribe
                    //     ->setUser(2)
                    //     ->setPage('plugins/task/index')
                    //     ->send(new TaskSendMessage([
                    //         'number'  => 1,
                    //         'balance' => 22,
                    //         'remark'  => "天机石积分",
                    //         'time'    => date("Y年m月d日 H:m", time()),
                    //     ]));
                    // P($aaaa);
                    // exit("大计靠拉伸打开吉安市");
                    //处理积分时间
                    $_M = date('m', strtotime($form['change_time']['end']));
                    $_D = date('d', strtotime($form['change_time']['end']));
                    //标签执行清零区间时间
                    $timeA = strtotime("-1 year", strtotime($Y . $_M . $_D));
                    $timeB = strtotime("+1 day", strtotime($Y . $_M . $_D));
                    //标签执行清零时的时间
                    $timeC = strtotime("+1 day", strtotime($Y . $M . $D));
                    P2(["获得时间", [$timeA, date('Y-m-d H:i:s', $timeA)], [$timeB, date('Y-m-d H:i:s', $timeB)], [$timeC, date('Y-m-d H:i:s', $timeC)]]);
                    //处理年月日
                    $money_count = $this->ModelScore::find()->select(["sum(number) as total", "UID"])
                        ->where(['status' => 1, 'type' => 'add'])
                        ->andwhere(['>=', 'start_time', $timeA])
                        ->andwhere(['<=', 'start_time', $timeB])
                        ->groupBy(['UID'])
                        ->asArray()
                        ->all();
                    P2($money_count);

                    //处理循环用户
                    foreach ($money_count as $key => $value) {
                        $dataUser = $this->ModelUser::find()->where(["UID" => $value['UID']])->one();
                        if ($dataUser) {
                            $dataScore = $this->ModelScore::find()
                                ->where(["UID" => $value['UID']])
                                ->andwhere(["identifier" => "reset"])
                                ->andwhere(['>=', 'start_time', $timeA])
                                ->andwhere(['<=', 'start_time', $timeC])
                                ->one();
                            //处理不存在
                            if (!$dataScore) {
                                if ($dataUser->number >= $value['total']) {
                                    P(["开始执行清除", [-$value['total'], $value['UID'], 0, 'del', '系统积分清零', "reset"]]);
                                    $ret = $this->plugins("task", ["scoreadd", [-$value['total'], $value['UID'], 0, 'del', '系统积分清零', "reset"]]);
                                    P2(["清除处理结果", $ret]);
                                }
                            }
                        }
                    }
                    P("完成清除计划");
                    exit();
                }

                $_M = date('m', strtotime($form['change_time']['end']));
                $_D = date('d', strtotime($form['change_time']['end']));
                //获取几天前
                $reset  = empty($form['reset_remind']) ? 1 : $form['reset_remind'];
                $remind = strtotime("-$reset day", strtotime($Y . $_M . $_D));

                //往前捣鼓15天（具体得看设置的时间）
                //此处用于消息提醒触发
                if ($remind == strtotime(date('Ymd'))) {
                    P2("处理积分清零提醒");
                    //这里需要执行积分提醒接口
                    //第一步：获取这个时间内365天所有获得积分值
                    //第二步：获取这个时间内365天所有消费的分值
                    //第三步：两个分数值相减，如果大于0的，就从总积分中，减去这个分值
                    //处理积分时间
                    //标签执行清零区间时间
                    $timeA = strtotime("-1 year", strtotime($Y . $_M . $_D));
                    $timeB = strtotime("+1 day", strtotime($Y . $_M . $_D));
                    //标签执行清零时的时间
                    $timeC = strtotime("+1 day", strtotime($Y . $M . $D));

                    P2(["获得时间", [$timeA, date('Y-m-d H:i:s', $timeA)], [$timeB, date('Y-m-d H:i:s', $timeB)], [$timeC, date('Y-m-d H:i:s', $timeC)]]);

                    //处理年月日
                    $money_count = $this->ModelScore::find()->select(["sum(number) as total", "UID"])
                        ->where(['status' => 1, 'type' => 'add'])
                        ->andwhere(['>=', 'start_time', $timeA])
                        ->andwhere(['<=', 'start_time', $timeB])
                        ->groupBy(['UID'])
                        ->asArray()
                        ->all();
                    //满足清零条件的用户
                    P($money_count);
                    //处理循环用户
                    foreach ($money_count as $key => $value) {
                        $dataUser = $this->ModelUser::find()->where(["UID" => $value['UID']])->one();
                        if ($dataUser->number >= $value['total']) {
                            $date = date('Ymd', $timeB);
                            P2(["循环执行提醒", $date, $value['UID'], $value['total']]);
                            $this->sendSMS($value['UID'], $date, $value['total']);
                        }
                    }
                }
            }
            P2("定时任务接口");
            exit();
            return "处理白名单接口";
        } else {
            //http://www.qmpaas.com/index.php?q=/api/leadmall/plugin&include=task&model=base
            $url = $this->getUrlAddress();
            return ["form" => $form, "task" => $task, "url" => $url];
        }

    }

    /**
     * 获取URL地址
     * @return [type] [description]
     */
    public function getUrlAddress()
    {
        $behavior = \Yii::$app->request->get('behavior');
        $name     = \Yii::$app->request->post('name');
        $name && \Yii::$app->crontab->getCrontab($name);
        $setting = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'merchant_id' => 1, 'keyword' => 'crontab_access_token']);
        if (!$setting || $behavior == 'reset') {
            $accessToken = $this->generateAccessToken($setting);
        } else {
            $accessToken = $setting['content'];
        }
        $url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/index.php?q=/api/leadmall/plugin&include=task&model=base&timing=1&access_token=' . $accessToken . '&appid=' . \Yii::$app->params['AppID'];
        if ($name) {
            return $url . '&name=' . $name;
        }
        return $url;
    }

    /**
     * 获取Token信息
     * @param  [type] $setting [description]
     * @return [type]          [description]
     */
    private function generateAccessToken($setting)
    {
        if (!$setting) {
            $setting = new Setting();
        }
        $accessToken          = \Yii::$app->security->generateRandomString(16);
        $setting->AppID       = \Yii::$app->params['AppID'];
        $setting->merchant_id = 1;
        $setting->keyword     = 'crontab_access_token';
        $setting->content     = $accessToken;
        if (!$setting->save()) {
            Error($setting->getErrorMsg());
        }
        return $accessToken;
    }

    /**
     * 检查Token信息
     * @return [type] [description]
     */
    private function checkAccessToken()
    {
        $appid = \Yii::$app->request->get('appid');
        if (!$appid) {
            Error('店铺AppID不存在');
        }
        $file = \Yii::$app->basePath . "/stores/{$appid}.json";
        if (!file_exists($file)) {
            Error('店铺不存在');
        }
        \Yii::$app->params = json_decode(file_get_contents($file), true);
        $accessToken       = \Yii::$app->request->get('access_token');
        if (!$accessToken) {
            Error('定时任务access_token不存在');
        }
        $res = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'keyword' => 'crontab_access_token']);
        if (!$res) {
            Error('定时任务access_token尚未配置');
        }
        if ($res['content'] != $accessToken) {
            Error('定时任务access_token不正确');
        }
    }

    public function sendSMS($UID, $date, $code)
    {
        $UserData = \users\models\User::find()->where(["id" => $UID])->one();
        //判断手机号是否存在
        if ($UserData && $UserData->mobile) {
            //处理短信模板
            $event      = array('sms' => []);
            $event      = json_decode(json_encode($event));
            $event->sms = array(
                'type'   => 'score_due',
                'mobile' => [$UserData->mobile],
                'params' => [
                    'date' => $date,
                    'code' => $code,
                ],
            );
            P2(["短信发送对象", $event]);
            //执行短信发送
            (new smsController($this->id, $this->module))->sendSms($event);
        }
    }

    /**
     * GET单条记录
     * @return [type] [description]
     */
    public function actionView()
    {
        return 233333444;
    }

    /**
     * POST写入
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $form = Yii::$app->request->post("form");
        $task = Yii::$app->request->post("task");

        //保存系统配置问题
        $this->manifest('config', $form);
        //循环查找数据链
        foreach ($task as $key => $item) {
            $model = $this->modelClass::find()->where(['keyword' => $item['keyword']])->one();
            if (!$model) {
                $model = new $this->modelClass();
            }
            //处理需要更新的字段信息1
            $model->name    = $item['name'];
            $model->type    = $item['type'];
            $model->keyword = $item['keyword'];
            $model->total   = $item['total'];
            $model->acquire = $item['acquire'];
            $model->maximum = $item['maximum'];
            $model->status  = $item['status'];
            $model->remark  = $item['remark'];
            $model->extend  = to_json($item['extend']);
            $model->save();
        }
        return true;
    }

    /**
     * 更新数据
     * @return [type] [description]
     */
    public function actionUpdate()
    {

        return 111;
    }

    /**
     * 删除数据
     * @return [type] [description]
     */
    public function actionDelete()
    {

    }
}
