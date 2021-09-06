<?php

namespace finance\api;

use app\components\subscribe\PromoterWithdrawalErrorMessage;
use app\components\subscribe\PromoterWithdrawalSuccessMessage;
use finance\models\Finance;
use framework\common\BasicController;
use promoter\models\Promoter;
use users\models\User;
use yii\data\ActiveDataProvider;

class IndexController extends BasicController
{
    public $post;
    /**
     * @var false|float|mixed
     */
    private $actualPrice;

    /**@var User $user */
    private $user;

    /**@var Finance $finance */
    private $finance;

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
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $get = \Yii::$app->request->get();
        $query = Finance::find()
            ->alias('f')
            ->innerJoinWith(['promoter p' => function ($query) {
                $query->with(['levelInfo' => function ($query1) {
                    $query1->select(['level', 'name']);
                }]);
            }])
            ->innerJoinWith(['user u'])
            ->with(['oauth' => function ($query) {
                $query->select(['UID', 'type']);
            }])
            ->where(["f.AppID" => \Yii::$app->params['AppID'], "f.is_deleted" => 0]);
        $keyword = $get['keyword'] ?? false;
        if ($keyword) {
            $query->andWhere([
                'or',
                ['f.UID' => $keyword],
                ['like', 'u.realname', $keyword],
                ['like', 'f.mobile', $keyword],
                ['like', 'u.nickname', $keyword],
                ['like', 'u.mobile', $keyword],
                ['like', 'p.apply_content', $keyword],
            ]);
        }
        $begin = $get['begin_time'] ?? false;
        $end = $get['end_time'] ?? false;
        if ($begin) {
            $query->andWhere(['>=', 'f.created_time', $begin]);
        }
        if ($end) {
            $query->andWhere(['<=', 'f.created_time', $end]);
        }
        $type = $get['type'] ?? false;
        if ($type) {
            $query->andWhere(['type' => $type]);
        }
        $level = $get['level'] ?? false;
        if ($level) {
            $query->andWhere(['p.level' => $level]);
        }
        $status = $get['status'] ?? false;
        if ($status || $status == 0) {
            if ($status != -1) {
                $query->andWhere(['f.status' => $status]);
            }
        }
        $data = new ActiveDataProvider(
            [
                'query' => $query->orderBy(['f.created_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );
        $list = $data->getModels();
        //将所有返回内容中的本地地址代替字符串替换为域名
        $newList = str2url($list);
        foreach ($newList as &$item) {
            $serviceCharge = qm_round($item['price'] * $item['service_charge'] / 100, 2);
            $actualPrice = qm_round($item['price'] - $serviceCharge, 2);
            $item['service_charge'] = $serviceCharge;
            $item['actual_price'] = $actualPrice;
            $item['extra'] = to_array($item['extra']);
        }
        $data->setModels($newList);
        return $data;
    }

    public function actionUpdate()
    {
        $id = \Yii::$app->request->get('id', false);
        $this->post = \Yii::$app->request->post();
        $finance = Finance::findOne(['id' => $id]);
        if (!$finance) {
            Error('该提现记录不存在!');
        }
        if ($finance->status == 2) {
            Error('提现已打款');
        }

        if ($finance->status == 3) {
            Error('提现已被驳回');
        }

        $status = $this->post['status'];
        if ($status <= $finance->status) {
            Error('状态错误, 请刷新重试');
        }

        $this->user = User::find()
            ->where(['id' => $finance->UID, 'is_deleted' => 0])
            ->one();
        if (!$this->user) {
            Error('用户不存在');
        }
        $t = \Yii::$app->db->beginTransaction();
        try {
            switch ($status) {
                case 1:
                    $this->apply($finance);
                    break;
                case 2:
                    $this->remit($finance);
                    break;
                case 3:
                    $this->reject($finance);
                    break;
                default:
                    throw new \Exception('错误的提现类型');
            }
            $t->commit();
            return true;
        } catch (\Exception $exception) {
            $t->rollBack();
            Error($exception->getMessage());
        }
    }

    /**
     * 审核通过
     * @param Finance $finance
     * @return bool
     * @throws \Exception
     */
    private function apply($finance)
    {
        $extra = to_array($finance->extra);
        $finance->status = 1;
        $extra['apply_at'] = date('Y-m-d H:i:s', time());
        $extra['apply_content'] = $this->post['content'] ?? '申请通过';
        $finance->extra = to_json($extra);
        try {
            $this->module->event->sms = [
                'type' => 'promoter_withdrawal',
                'mobile' => [$this->user->mobile],
                'params' => [
                    'result' => '成功',
                ],
            ];
            $this->module->trigger('send_sms');
        } catch (\Exception $exception) {
            \Yii::error('====提现发送短信失败====');
            \Yii::error($exception->getMessage());
        }
        if (!$finance->save()) {
            Error($finance->getErrorMsg());
        }
        return true;
    }

    /**
     * 打款
     * @param Finance $finance
     * @return bool
     */
    private function remit($finance)
    {
        // 保存提现信息
        $extra = to_array($finance->extra);
        $finance->status = 2;
        $extra['remittance_at'] = date('Y-m-d H:i:s', time());
        $extra['remittance_content'] = $this->post['content'] ?? '申请通过';
        $finance->extra = to_json($extra);
        if (!$finance->save()) {
            Error($finance->getErrorMsg());
        }
        $this->finance = $finance;

        $serviceCharge = qm_round($finance->price * $finance->service_charge / 100, 2);
        $this->actualPrice = qm_round($finance->price - $serviceCharge, 2);

        $type = $finance->type;
        if (method_exists($this, $type)) {
            $this->$type();
        } else {
            Error('错误的提现方式');
        }

        try {
            $subscribeData = [
                'money' => $finance->price,
                'serviceCharge' => qm_round($finance->price * $finance->service_charge / 100, 2),
                'type' => $finance->getTypeText2(),
            ];
            $subscribe = new PromoterWithdrawalSuccessMessage($subscribeData);
            \Yii::$app->subscribe->setUser($this->user->id)->setPage('promoter/pages/withdraw-list')->send($subscribe);
        } catch (\Exception $exception) {
            \Yii::error('====提现发送订阅消息失败====');
            \Yii::error($exception->getMessage());
        }
        return true;
    }

    /**
     * 审核不通过
     * @param $finance
     * @return bool
     */
    private function reject($finance)
    {
        // 保存提现信息
        $extra = to_array($finance->extra);
        $finance->status = 3;
        $extra['reject_at'] = date('Y-m-d H:i:s', time());
        $extra['reject_content'] = '拒绝打款';
        if (isset($this->post['content']) || !empty($this->post['content'])) {
            $extra['reject_content'] = $this->post['content'];
        }
        $finance->extra = to_json($extra);
        if (!$finance->save()) {
            Error($finance->getErrorMsg());
        }
        $promoter = Promoter::findOne(['UID' => $finance->UID]);
        $promoter->commission = $promoter->commission + qm_round($finance->price, 2);
        $promoter->save();
        try {
            $this->module->event->sms = [
                'type' => 'promoter_withdrawal',
                'mobile' => [$this->user->mobile],
                'params' => [
                    'result' => '拒绝',
                ],
            ];
            $this->module->trigger('send_sms');
        } catch (\Exception $exception) {
            \Yii::error('====提现发送短信失败====');
            \Yii::error($exception->getMessage());
        }

        try {
            $subscribeData = [
                'money' => $finance->price,
                'name' => $extra['reject_content'] ?: '拒绝打款',
                'time' => date('Y-m-d H:i:s', time()),
            ];
            $subscribe = new PromoterWithdrawalErrorMessage($subscribeData);
            \Yii::error($this->user);
            \Yii::$app->subscribe->setUser($this->user->id)->setPage('promoter/pages/withdraw-list')->send($subscribe);
        } catch (\Exception $exception) {
            \Yii::error('====提现发送订阅消息失败====');
            \Yii::error($exception->getMessage());
        }

        return true;
    }

    // 微信手动打款
    private function wechat()
    {
        return true;
    }

    // 支付宝手动打款
    private function alipay()
    {
        return true;
    }

    // 银行手动打款
    private function bankCard()
    {
        return true;
    }

    /**
     * 根据用户身份自动打款（微信）
     */
    private function wechatDib()
    {
        \Yii::$app->payment->transfer($this->user, $this->finance, '分销商提现', function () {
            return true;
        });
        return true;
    }
}
