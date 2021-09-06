<?php

namespace promoter\app;

use app\components\ComPromoter;
use finance\models\Finance;
use framework\common\BasicController;
use promoter\models\Promoter;
use promoter\models\PromoterCommission;
use promoter\models\PromoterLevel;
use promoter\models\PromoterLevelChangeLog;
use promoter\models\PromoterLoseLog;
use promoter\models\PromoterOrder;
use users\models\User;
use Yii;
use yii\data\ActiveDataProvider;

class IndexController extends BasicController
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
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'bind':
                return $this->bind();
                break;
            case 'recruiting':
                return $this->Recruiting();
                break;
            case 'apply_check':
                return $this->applyCheck();
                break;
            case 'apply_audit':
                return $this->applyAudit();
                break;
            default:
                return $this->promoterInfo();
                break;
        }
    }

    public function actionCreate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'search':
                return $this->search();
                break;
            case 'tab':
                return $this->tabCount();
                break;
            default:
                return $this->apply();
                break;
        }
    }

    /**
     * 分销商中心
     */
    private function promoterInfo()
    {
        $UID   = Yii::$app->user->identity->id;
        $AppID = Yii::$app->params['AppID'];

        $model = Promoter::findOne(['UID' => $UID]);

        if (!$model) {
            Error('分销商不存在');
        }

        $data                 = $model->toArray();
        $data['all_children'] = $model->getAllChildren();
        $self_buy             = StoreSetting('promoter_setting', 'self_buy');
        if ($self_buy == 2) {
            $data['all_children']++;
        }
        $p_c                           = PromoterCommission::find()->where(['beneficiary' => $UID])->select('sum(commission) all_commission_amount,sum(sales_amount) sales_amount,count(id) promoter_order_number')->asArray()->one();
        $data['all_commission_amount'] = $p_c['all_commission_amount'] ?: 0;
        $data['promoter_order_number'] = $p_c['promoter_order_number'] ?: 0;
        $data['sales_amount']          = $p_c['sales_amount'] ?: 0;
        $data['wait_account']          = qm_round($data['all_commission_amount'] - $data['commission_amount']);
        $data['is_withdrawal']         = qm_round($data['commission_amount'] - $data['commission']);

        $level_data         = PromoterLevel::find()->where(['and', ['>=', 'level', $data['level']], ['AppID' => $AppID, 'is_deleted' => 0], ['or', ['is_auto' => 1], ['level' => 1]]])->select('name,level,condition')->orderBy(['level' => SORT_ASC])->limit(2)->asArray()->all();
        $data['level_name'] = $level_data[0]['name'];
        $next_level         = $level_data[1] ?? null;
        if (!empty($next_level)) {
            $next_level['condition'] = to_array($next_level['condition']);
            $next_level['lack']      = null;
            $process                 = 1;
            foreach ($next_level['condition'] as $k => $v) {
                if ($v['checked']) {
                    switch ($k) {
                        case 'all_children':
                            if ($data['all_children'] < $v['num']) {
                                if (($data['all_children'] / $v['num']) < $process) {
                                    $process            = $data['all_children'] / $v['num'];
                                    $next_level['lack'] = ['condition' => '当前下线数', 'lack_num' => ($v['num'] - $data['all_children']), 'get_num' => $data['all_children'], 'all_num' => $v['num']];
                                }
                            }
                            break;
                        case 'total_bonus':
                            if ($data['all_commission_amount'] < $v['num']) {
                                if (($data['all_commission_amount'] / $v['num']) < $process) {
                                    $process            = $data['all_commission_amount'] / $v['num'];
                                    $next_level['lack'] = ['condition' => '累计佣金 ￥', 'lack_num' => qm_round($v['num'] - $data['all_commission_amount']), 'get_num' => $data['all_commission_amount'], 'all_num' => $v['num']];
                                }
                            }
                            break;
                        case 'total_money':
                            if ($p_c['sales_amount'] < $v['num']) {
                                if (($p_c['sales_amount'] / $v['num']) < $process) {
                                    $process            = $p_c['sales_amount'] / $v['num'];
                                    $next_level['lack'] = ['condition' => '累计销售金额 ￥', 'lack_num' => qm_round($v['num'] - $p_c['sales_amount']), 'get_num' => $p_c['sales_amount'], 'all_num' => $v['num']];
                                }
                            }
                            break;

                    }
                }
            }
        }

        $data['down_level_status'] = 0;
        $level_change_log          = PromoterLevelChangeLog::find()->where(['UID' => $UID, 'look_status' => 0])->select('old_level')->orderBy(['old_level' => SORT_DESC])->one();
        if ($level_change_log) {
            if ($level_change_log->old_level > $data['level']) {
                $data['down_level_status'] = -1;
            } elseif ($level_change_log->old_level < $data['level']) {
                $data['down_level_status'] = 1;
            }
            PromoterLevelChangeLog::updateAll(['look_status' => 1], ['UID' => $UID, 'look_status' => 0]);
        }
        $data['next_level'] = $next_level;
        $cashed             = Finance::find()->where(['between', 'created_time', strtotime(date('Y-m-d 00:00:00', time())), strtotime(date('Y-m-d 23:59:59', time()))])
            ->andWhere(['UID' => \Yii::$app->user->id, 'status' => [0, 1, 2]])->sum('price');
        $maxMoney         = StoreSetting('commission_setting', 'max_money');
        $data['can_cash'] = qm_round($maxMoney - $cashed) > 0 ? qm_round($maxMoney - $cashed) : 0;
        return $data;
    }

    /**
     * 用户下线统计列表
     */
    private function tabCount()
    {
        $UID = Yii::$app->user->identity->id;

        $model = Promoter::findOne(['UID' => $UID]);

        $data             = [];
        $data['children'] = [];
        $setting          = StoreSetting('promoter_setting');
        $level_number     = $setting['level_number'];

        $first_children      = array_column($model->firstChildren, 'id');
        $data['children'][0] = [
            'type'   => 'first',
            'number' => count($first_children),
            'parent' => $UID,
        ];

        if ($setting['self_buy'] === 2) {
            $data['children'][0]['number']++;
        }

        $today_time = strtotime('today');

        $data['first_today_get']  = User::find()->where(['and', ['>=', 'bind_time', $today_time], ['parent_id' => $UID]])->count('id');
        $data['first_today_lose'] = PromoterLoseLog::find()->where(['and', ['>=', 'created_time', $today_time], ['parent_id' => $UID]])->count('id');

        if ($level_number > 1) {
            $second_children     = array_column($model->secondChildren, 'id');
            $data['children'][1] = [
                'type'   => 'second',
                'number' => count($second_children),
                'parent' => to_json($first_children),
            ];
            $data['second_today_get']  = User::find()->where(['and', ['>=', 'bind_time', $today_time], ['parent_id' => $first_children]])->count('id');
            $data['second_today_lose'] = PromoterLoseLog::find()->where(['and', ['>=', 'created_time', $today_time], ['parent_id' => $first_children]])->count('id');
        }

        if ($level_number > 2) {
            $third_children      = $model->thirdChildren;
            $data['children'][2] = [
                'type'   => 'third',
                'number' => count($third_children),
                'parent' => to_json($second_children),
            ];
            $data['third_today_get']  = User::find()->where(['and', ['>=', 'bind_time', $today_time], ['parent_id' => $second_children]])->count('id');
            $data['third_today_lose'] = PromoterLoseLog::find()->where(['and', ['>=', 'created_time', $today_time], ['parent_id' => $second_children]])->count('id');
        }

        return $data;
    }

    /**
     * 用户下线列表
     */
    private function search()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $post   = Yii::$app->request->post();
        $parent = $post['parent'] ?? false;
        if (!$parent) {
            Error('父级缺失');
        }
        $UID = Yii::$app->user->identity->id;

        $parent = to_array($parent);

        $AppID = Yii::$app->params['AppID'];
        $where = ['u.AppID' => $AppID, 'u.parent_id' => $parent];

        $nickname = $post['nickname'] ?? '';
        if ($nickname) {
            $where = ['and', $where, ['like', 'u.nickname', $nickname]];
        }

        $type = $post['type'] ?? 0;
        if ($type) {
            if ($type === 1) {
                $where = ['and', $where, ['p.status' => 2]];
            } elseif ($type === 2) {
                $where = ['and', $where, ['or', ['<>', 'p.status', 2], ['p.status' => null]]];
            }
        }

        $promoter_setting = StoreSetting('promoter_setting');
        //自购返佣.获取分销商一级下线时
        if ($promoter_setting['self_buy'] === 2 && $type != 2 && !is_array($parent)) {
            $where = ['or', $where, ['u.id' => $parent]];
        }

        $query = User::find()
            ->alias('u')
            ->leftJoin(['p' => Promoter::tableName()], 'u.id = p.UID')
            ->where($where);

        $subQuery = PromoterCommission::find()
            ->alias('c')
            ->leftJoin(['or' => PromoterOrder::tableName()], 'c.order_goods_id = or.order_goods_id')
            ->andWhere(['and', ['c.beneficiary' => $UID], ['>=', 'or.status', 0]])
            ->groupBy('or.UID')
            ->select('sum(c.commission) commission,sum(c.sales_amount) sales_amount,count(c.id) promoter_order_number,or.UID');
        $query->leftJoin(['sq' => $subQuery], 'sq.UID = u.id');
        $query->addSelect([
            'p.UID', 'p.status', 'p.join_time',
            'u.id', 'u.avatar', 'u.nickname', 'u.mobile', 'u.realname', 'u.bind_time',
            "commission"            => "IF(sq.`commission`,sq.`commission`, 0)",
            "sales_amount"          => "IF(sq.`sales_amount`,sq.`sales_amount`, 0)",
            "promoter_order_number" => "IF(sq.`promoter_order_number`,sq.`promoter_order_number`, 0)",
        ]);

        $data = new ActiveDataProvider(
            [
                'query'      => $query->orderBy(['p.join_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        return $data;
    }

    /**
     * 申请成为分销商
     */
    private function apply()
    {
        $UID  = Yii::$app->user->identity->id;
        $data = Promoter::findOne(['UID' => $UID]);
        switch ($data->status) {
            case 0:
            case 4:
                Error('您未收到招募');
                break;
            case 2:
                Error('您已经是分销商了');
                break;
            case 1:
                Error('已提交过申请,请耐心等待');
                break;
            default:
                $check = $this->applyCheck();
                if (!$check['status']) {
                    Error('您未满足成为成为分销商的条件');
                }

                $promoter_setting = StoreSetting('promoter_setting');
                if ($promoter_setting['need_apply'] === 1) {
                    $apply_content = Yii::$app->request->post('apply_content', null);
                    if (empty($apply_content)) {
                        Error('申请内容不能为空');
                    }
                    $data->apply_content = to_json($apply_content);
                }
                if ($promoter_setting['need_check'] === 0) {
                    $data->status    = 2;
                    $data->invite_id = abs($data->invite_id);
                    $data->join_time = time();
                } else {
                    $data->status = 1;
                }
                $data->apply_time = time();

                $t   = Yii::$app->db->beginTransaction();
                $res = $data->save();
                if ($res && $data->status === 2) {
                    $ComPromoter = new ComPromoter();
                    $ComPromoter->setLevel([$UID], 2);
                    if ($data->invite_id) {
                        $c_res = Promoter::updateAllCounters(['invite_number' => 1], ['UID' => $data->invite_id]);
                        if (!$c_res) {
                            $t->rollBack();
                            Error('系统错误');
                        }
                    }
                    if ($data->apply_content) {
                        $apply_content = to_array($data->apply_content);
                        if (is_array($apply_content) && $apply_content[0]['value']) {
                            $u_res = User::updateAll(['realname' => $apply_content[0]['value']], ['id' => $UID]);
                            if ($u_res !== 1 && $u_res !== 0) {
                                $t->rollBack();
                                Error('系统错误');
                            }
                        }
                    }
                }
                $t->commit();
                break;
        }
        return $data;
    }

    private function applyAudit()
    {
        $UID = Yii::$app->user->identity->id;
        return Promoter::findOne(['UID' => $UID]);
    }

    private function bind()
    {
        $get_parent = Yii::$app->request->get('parent_id', 0);
        $UID        = Yii::$app->user->identity->id;
        if ($get_parent && $get_parent != $UID) {
            $data             = M('users', 'User')::findOne($UID);
            $promoter_setting = StoreSetting('promoter_setting');
            if ($promoter_setting['status']) {
                $parent_id = (int) $data->parent_id;
                if ($parent_id <= 0 && $get_parent != abs($parent_id)) {
                    $p_info = Promoter::findOne(['UID' => $get_parent]);
                    if ($p_info) {
                        if ($p_info->status == 2) {
                            $ComPromoter = new ComPromoter();
                            $check       = $ComPromoter->relationCheck($UID, $get_parent, 4);
                            if ($check['status']) {
                                if ($promoter_setting['bind_way'] === 2) {
                                    $get_parent = 0 - $get_parent;
                                }
                                $data->parent_id = $get_parent;
                                if ($get_parent > 0) {
                                    $data->bind_time = time();
                                }
                                return $data->save();
                            }
                        }
                    }
                }

            }
        }

        return true;
    }

    /**
     * 招募令接口
     */
    private function Recruiting()
    {
        $UID       = Yii::$app->user->identity->id;
        $invite_id = Yii::$app->request->get('invite_id', 0);
        $data      = Promoter::findOne(['UID' => $UID]);
        $check     = Promoter::findOne(['UID' => $invite_id]);//判断邀请人是不是分销商
        if (!$check) {
            $invite_id = 0;
        }
        if (empty($data) || ($data->status !== 1 && $data->status !== 2 && $data->status !== 3)) {
            if (empty($data)) {
                $model               = M('promoter', 'Promoter', true);
                $model->UID          = $UID;
                $model->status       = -1;
                $model->invite_id    = (0 - $invite_id);
                $model->created_time = time();
                $res                 = $model->save();
                $data                = $model;
            } else {
                if ($data->invite_id <= 0) {
                    $data->invite_id = (0 - $invite_id);
                }
                if ($data->status >= 0) {
                    $data->status = $data->status === 0 ? -1 : -2;
                }
                $res = $data->save();
            }
            if ($res) {
                return $data;
            } else {
                Error('系统繁忙');
            }
        }

        return $data;

    }

    /**
     * 分销商,申请检测
     */
    private function applyCheck()
    {
        $UID         = Yii::$app->user->identity->id;
        $conditions  = StoreSetting('promoter_setting', 'conditions');
        $return_data = [
            'type'     => $conditions['type'],
            'status'   => true,
            'pay_show' => true,
        ];

        switch ($conditions['type']) {
            case 2:
                $data                       = M('users', 'UserStatistical')::findOne(['UID' => $UID]);
                $return_data['denominator'] = $conditions['buy_amount'];
                if ($data->buy_amount < $conditions['buy_amount']) {
                    $return_data['status']    = false;
                    $return_data['pay_show']  = false;
                    $return_data['numerator'] = $data->buy_amount;
                }
                break;
            case 3:
                $data                       = M('users', 'UserStatistical')::findOne(['UID' => $UID]);
                $return_data['denominator'] = $conditions['buy_number'];
                if ($data->buy_number < $conditions['buy_number']) {
                    $return_data['status']    = false;
                    $return_data['pay_show']  = false;
                    $return_data['numerator'] = $data->buy_number;
                }
                break;
            case 4:
                $data = M('users', 'UserStatistical')::findOne(['UID' => $UID]);
                if ($data->buy_number === 0) {
                    $return_data['status']   = false;
                    $return_data['pay_show'] = false;
                }
                break;
            case 5:
                $id_list = array_column($conditions['goods_list'], 'id');
                $check   = M('order', 'OrderGoods')::find()
                    ->alias('g')
                    ->joinWith(['order as o'])
                    ->where(['and', ['g.goods_id' => $id_list, 'o.UID' => $UID], ['>', 'o.status', 200]])
                    ->asArray()
                    ->one();
                if (empty($check)) {
                    $return_data['status']   = false;
                    $return_data['pay_show'] = false;
                }
                break;
            default:
                $return_data['pay_show'] = false;
                break;
        }

        return $return_data;
    }

    public static function checkPromoter($event)
    {
        $setting = StoreSetting('promoter_setting');
        //无需审核,无需申请,无条件时可自动成为分销商
        if ($setting['need_check'] === 0 && $setting['need_apply'] === 0 && $setting['conditions']['type'] === 1) {
            $UID  = $event->visit_info['UID'];
            $data = Promoter::findOne(['UID' => $UID]);
            if (empty($data) || ($data->status !== 1 && $data->status !== 2)) {
                $time = time();
                if (empty($data)) {
                    $model               = M('promoter', 'Promoter', true);
                    $model->UID          = $UID;
                    $model->created_time = $time;
                    $data                = $model;
                }
                $data->status     = 2;
                $data->invite_id  = 0;
                $data->apply_time = $time;
                $data->join_time  = $time;
                if ($data->save()) {
                    $ComPromoter = new ComPromoter();
                    $ComPromoter->setLevel([$UID], 3);
                    return $data;
                } else {
                    Yii::error('自动成分销商失败');
                }
            }

        }
    }
}
