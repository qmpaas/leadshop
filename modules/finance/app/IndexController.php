<?php

namespace finance\app;

use finance\models\Finance;
use framework\common\BasicController;
use promoter\models\Promoter;
use yii\data\ActiveDataProvider;

class IndexController extends BasicController
{
    public $post;

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

    public function actionCreate()
    {
        $behavior = \Yii::$app->request->get('model');
        if (!method_exists($this, $behavior)) {
            Error('未定义操作');
        }
        $this->post = \Yii::$app->request->post();
        return $this->$behavior();
    }

    private function promoter()
    {
        $promoter = Promoter::findOne(['UID' => \Yii::$app->user->id]);
        if (!$promoter) {
            Error('申请的用户不是分销商，无法提现');
        }
        $price = $this->post['price'];
        if ($price <= 0) {
            Error('提现佣金必须大于0');
        }
        if ($promoter->commission < $price) {
            Error('提现佣金超出分销商可提现佣金');
        }
        $minMoney = StoreSetting('commission_setting', 'min_money');
        $maxMoney = StoreSetting('commission_setting', 'max_money');
        $poundage = StoreSetting('commission_setting', 'poundage');
        $withdrawWay = StoreSetting('commission_setting', 'withdrawal_way');
        $type = $this->post['type'];
        if (!in_array($type, $withdrawWay)) {
            Error('未知的提现方式');
        }
        if ($price < $minMoney) {
            Error('不得小于最低提现金额');
        }
        if ($price > $maxMoney) {
            Error('不得大于每日最高提现金额');
        }

        $cashed = Finance::find()->where(['between', 'created_time',  strtotime(date('Y-m-d 00:00:00',time())), strtotime(date('Y-m-d 23:59:59',time()))])
            ->andWhere(['UID' => \Yii::$app->user->id, 'status' => [0, 1, 2]])->sum('price');
        if ($cashed > $maxMoney) {
            Error('不得大于每日最高提现金额');
        }

        $exists = Finance::find()->where([
            'AppID' => \Yii::$app->params['AppID'], 'status' => 0, 'UID' => \Yii::$app->user->id, 'is_deleted' => 0,
        ])->exists();

        if ($exists) {
            Error('尚有未审核的提现申请');
        }
        if ($type == 'wechat') {
            if (empty($this->post['extra']['wechat'])) {
                Error('请输入微信号');
            }
            if (empty($this->post['extra']['name'])) {
                Error('请输入姓名');
            }
        }
        if ($type == 'alipay') {
            if (empty($this->post['extra']['alipay'])) {
                Error('请输入支付宝账号');
            }
            if (empty($this->post['extra']['name'])) {
                Error('请输入姓名');
            }
        }
        if ($type == 'bankCard') {
            if (empty($this->post['extra']['bank_user_name'])) {
                Error('请输入开户人信息');
            }
            if (empty($this->post['extra']['bank_name'])) {
                Error('请输入开户行信息');
            }
            if (empty($this->post['extra']['bank_no'])) {
                Error('请输入银行账号');
            }
        }
        $extra = to_json($this->post['extra']);
        $t = \Yii::$app->db->beginTransaction();
        $cash = new Finance();
        $cash->attributes = $this->post;
        $cash->model = 'promoter';
        $cash->AppID = \Yii::$app->params['AppID'];
        $cash->merchant_id = 1;
        $cash->UID = \Yii::$app->user->id;
        $cash->price = qm_round($price, 2);
        $cash->service_charge = $poundage;
        $cash->type = $this->post['type'];
        $cash->extra = $extra;
        $cash->status = 0;
        $cash->order_sn = date('YmdHis') . rand(10000, 99999);
        $cash->is_deleted = 0;
        if ($cash->save()) {
            $promoter->commission = qm_round($promoter->commission - $cash->price, 2);
            $promoter->save();
            $t->commit();
            return $cash->id;
        } else {
            $t->rollBack();
            Error($cash->getErrorMsg());
        }
    }

    public function actionIndex()
    {
        $behavior = \Yii::$app->request->get('model');
        if (!method_exists($this, $behavior)) {
            Error('未定义操作');
        }
        $this->post = \Yii::$app->request->post();
        $behavior = $behavior . 'List';
        return $this->$behavior();
    }

    private function promoterList()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $page = $headers->get('X-Pagination-Current-Page') ?? 1;
        $query = Finance::find()
            ->where(['AppID' => \Yii::$app->params['AppID'], 'model' => 'promoter', 'UID' => \Yii::$app->user->id, 'is_deleted' => 0])
            ->with(['user']);
        $status = \Yii::$app->request->get('status') ?? false;
        if ($status !== false && $status != -1) {
            $query->andWhere(['status' => $status]);
        }
        $firstDay = \Yii::$app->request->get('first_day') ?? false;
        if ($firstDay) {
            $firstDayDate = date('Y-m-01', $firstDay);
            $lastDay = strtotime(date('Y-m-d 23:59:59', strtotime("$firstDayDate +1 month -1 day")));
            $query->andWhere(['between', 'created_time', $firstDay, $lastDay]);
        } else {
            $lastRecord = Finance::find()
                ->select('created_time')
                ->where(['AppID' => \Yii::$app->params['AppID'], 'model' => 'promoter', 'UID' => \Yii::$app->user->id, 'is_deleted' => 0])
                ->orderBy(['created_time' => SORT_DESC])
                ->limit(1)
                ->one();
            if ($lastRecord) {
                $firstDayDate = date('Y-m-01', $lastRecord->created_time);
                $lastDay = strtotime(date('Y-m-d 23:59:59', strtotime("$firstDayDate +1 month -1 day")));
                $query->andWhere(['between', 'created_time', strtotime($firstDayDate), $lastDay]);
            } else {
                return ['date' => date('Y-m-01', time()), 'list' => []];
            }
        }
        $data = new ActiveDataProvider(
            [
                'query'      => $query->orderBy(['created_time' => SORT_DESC]),
                'pagination' => ['page' => $page - 1, 'pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );
        $list = $data->getModels();
        if (!$list) {
            return ['date' => date('Y-m-01', $firstDay ?: time()), 'list' => []];
        }
        $newList = [];
        foreach ($list as $item) {
            /* @var Finance $item */
            $serviceCharge = qm_round($item->price * $item->service_charge / 100, 2);
            $extra = to_array($item->extra);
            $newItem = [
                'id' => $item->id,
                'order_no' => $item->order_sn,
                'pay_type' => $item->getTypeText2($item->type),
                'type' => $item->type,
                'status' => $item->status,
                'status_text' => $item->getStatusText($item->status),
                'user' => [
                    'avatar' => $item->user->avatar,
                    'nickname' => $item->user->nickname,
                ],
                'cash' => [
                    'price' => qm_round($item->price, 2),
                    'service_charge' => $serviceCharge,
                    'actual_price' => qm_round($item->price - $serviceCharge, 2)
                ],
                'extra' => [
                    //姓名
                    'name' => $extra['name'] ?? '',
                    //电话
                    'mobile' => $extra['mobile'] ?? '',
                    //银行名称
                    'bank_name' => $extra['bank_name'] ?? '',
                    //微信号
                    'wechat' => $extra['wechat'] ?? '',
                    //支付宝账号
                    'alipay' => $extra['alipay'] ?? '',
                    //银行卡号
                    'bank_no' => $extra['bank_no'] ?? '',
                    //开户人
                    'bank_user_name' => $extra['bank_user_name'] ?? '',
                ],
                'time' => [
                    'created_time' => $item->created_time,
                    'apply_time' => isset($extra['apply_time']) ? $extra['apply_time'] : '',
                    'remittance_time' => isset($extra['remittance_time']) ? $extra['remittance_time'] : '',
                    'reject_time' => isset($extra['reject_time']) ? $extra['reject_at'] : '',
                ],
                'content' => [
                    'apply_content' => isset($extra['apply_content']) ? $extra['apply_content'] : '',
                    'remittance_content' => isset($extra['remittance_content']) ? $extra['remittance_content'] : '',
                    'reject_content' => isset($extra['reject_content']) ? $extra['reject_content'] : '',
                ]
            ];
            $newList[] = $newItem;
        }

        $cashList['date'] = date('Y-m', $newList[0]['time']['created_time']);
        $cashList['pagination'] = $data->getPagination();
        foreach ($newList as $item) {
            $cashList['list'][] = $item;
        }
        return $cashList;
    }
}
