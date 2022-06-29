<?php

namespace promoter\api;

use app\components\ComPromoter;
use app\components\subscribe\PromoterVerifyMessage;
use framework\common\BasicController;
use promoter\models\Promoter;
use promoter\models\PromoterCommission;
use promoter\models\PromoterLevel;
use promoter\models\PromoterOrder;
use users\models\Oauth;
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

    public function actionSearch()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $keyword = Yii::$app->request->post('keyword', []);

        $AppID = Yii::$app->params['AppID'];
        $where = ['and', ['u.AppID' => $AppID], ['<>', 'p.status', 0], ['<>', 'p.status', -1]];

        //关键词搜索
        $search = $keyword['search'] ?? false;
        if ($search) {
            $where = ['and', $where, ['or', ['like', 'u.nickname', $search], ['like', 'u.realname', $search], ['u.mobile' => $search], ['u.id' => $search]]];
        }

        //分销等级
        $level = $keyword['level'] ?? false;
        if ($level) {
            $where = ['and', $where, ['p.level' => $level, 'p.status' => 2]];
        }

        //邀请方
        $invite = $keyword['invite'] ?? false;
        if ($invite) {
            $where = ['and', $where, ['like', 'i.nickname', $invite]];
        }

        //分销状态
        $status = $keyword['status'] ?? 0;
        if ($status > 0) {
            if ($status === 4) {
                $where = ['and', $where, ['or', ['p.status' => 4], ['p.status' => -2]]];
            } else {
                $where = ['and', $where, ['p.status' => $status]];
            }
        }

        //申请时间区间
        $apply_time_start = $keyword['apply_time_start'] ?? false;
        if ($apply_time_start > 0) {
            $where = ['and', $where, ['>=', 'p.apply_time', $apply_time_start]];
        }
        $apply_time_end = $keyword['apply_time_end'] ?? false;
        if ($apply_time_end > 0) {
            $where = ['and', $where, ['<=', 'p.apply_time', $apply_time_end]];
        }

        //加入时间区间
        $join_time_start = $keyword['join_time_start'] ?? false;
        if ($join_time_start > 0) {
            $where = ['and', $where, ['>=', 'p.join_time', $join_time_start]];
        }
        $join_time_end = $keyword['join_time_end'] ?? false;
        if ($join_time_end > 0) {
            $where = ['and', $where, ['<=', 'p.join_time', $join_time_end]];
        }

        //用户来源筛选
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['o.type' => $source]];
        }

        $query = Promoter::find()
            ->alias('p')
            ->leftJoin(['l' => PromoterLevel::tableName()], 'l.level = p.level')
            ->leftJoin(['u' => User::tableName()], 'u.id = p.UID')
            ->leftJoin(['o' => Oauth::tableName()], 'o.UID = p.UID')
            ->leftJoin(['i' => User::tableName()], 'i.id = p.invite_id')
            ->where($where);

        $comQuery = PromoterCommission::find()
            ->alias('pc')
            ->leftJoin(['po' => PromoterOrder::tableName()], 'pc.order_goods_id = po.order_goods_id')
            ->andWhere(['>=', 'po.status', 0])
            ->groupBy('pc.beneficiary')
            ->select('pc.beneficiary,sum(pc.sales_amount) sales_amount,sum(pc.commission) all_commission_amount');
        $query->leftJoin(['com' => $comQuery], 'com.beneficiary = p.UID');

        $level = StoreSetting('promoter_setting', 'level_number');
        if ($level >= 1) {
            $subQuery1 = User::find()
                ->alias('a')
                ->leftJoin(['b' => User::tableName()], 'a.id = b.parent_id')
                ->andWhere(['!=', 'b.id', ''])
                ->groupBy('a.id')
                ->select('count(a.id) num, a.id');
            $query->leftJoin(['sq1' => $subQuery1], 'sq1.id = p.UID');
            $all_children = "IF(sq1.`num`,sq1.`num`, 0)";
            if ($level >= 2) {
                $subQuery2 = User::find()
                    ->alias('a')
                    ->leftJoin(['b' => User::tableName()], 'a.id = b.parent_id')
                    ->leftJoin(['c' => User::tableName()], 'b.id = c.parent_id')
                    ->andWhere(['!=', 'b.id', ''])
                    ->andWhere(['!=', 'c.id', ''])
                    ->groupBy('a.id')
                    ->select('count(a.id) num, a.id');
                $query->leftJoin(['sq2' => $subQuery2], 'sq2.id = p.UID');
                $all_children = "IF(sq1.`num`,sq1.`num`, 0) + IF(sq2.`num`,sq2.`num`, 0)";
                if ($level >= 3) {
                    $subQuery3 = User::find()
                        ->alias('a')
                        ->leftJoin(['b' => User::tableName()], 'a.id = b.parent_id')
                        ->leftJoin(['c' => User::tableName()], 'b.id = c.parent_id')
                        ->leftJoin(['d' => User::tableName()], 'c.id = d.parent_id')
                        ->andWhere(['!=', 'b.id', ''])
                        ->andWhere(['!=', 'c.id', ''])
                        ->andWhere(['!=', 'd.id', ''])
                        ->groupBy('a.id')
                        ->select('count(a.id) num, a.id');
                    $query->leftJoin(['sq3' => $subQuery3], 'sq3.id = p.UID');
                    $all_children = "IF(sq1.`num`,sq1.`num`, 0) + IF(sq2.`num`,sq2.`num`, 0) + IF(sq3.`num`,sq3.`num`, 0)";
                }
            }
        }

        $query->addSelect([
            'p.id', 'p.UID', 'p.commission', 'p.status', 'p.apply_time', 'p.apply_content', 'p.join_time', 'p.repel_time', 'p.invite_number',
            'com.sales_amount', 'com.all_commission_amount',
            'l.name level_name',
            'u.avatar', 'u.nickname', 'u.mobile', 'u.realname',
            'o.type',
            'i.nickname invite_nickname',
            "all_children" => $all_children,
        ]);

        $sort    = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['p.apply_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                if ($key == 'all_children') {
                    $orderBy['all_children'] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
                } elseif ($key == 'sales_amount' || $key == 'all_commission_amount') {
                    $orderBy['com.' . $key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
                } else {
                    if (!sql_check($key)) {
                        $orderBy['p.' . $key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
                    }
                }

            }
        }

        $data = new ActiveDataProvider(
            [
                'query'      => $query->groupBy('p.UID')->orderBy($orderBy)->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $setting = StoreSetting('promoter_setting');
        $list    = $data->getModels();
        foreach ($list as $key => &$value) {
            if ($setting['self_buy'] === 2) {
                $value['all_children']++;
            }
            $value['apply_content']         = to_array($value['apply_content']);
            $value['sales_amount']          = $value['sales_amount'] ?: 0.00;
            $value['all_commission_amount'] = $value['all_commission_amount'] ?: 0.00;
        }
        $data->setModels($list);

        return $data;

    }

    public function actionIndex()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'children_info':
                return $this->childrenInfo();
                break;
            case 'add_search':
                return $this->addSearch();
                break;
            case 'transfer_search':
                return $this->transferSearch();
                break;
            default:
                return '访问成功';
                break;
        }
    }

    /**
     * 添加分销商
     */
    public function actionCreate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'children_list':
                return $this->childrenList();
                break;
            default:
                return $this->addPromoter();
                break;
        }
    }

    /**
     * 下线列表
     */
    private function childrenList()
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
        $UID = $post['UID'] ?? false;
        if (!$UID) {
            Error('用户ID缺失');
        }

        $parent = to_array($parent);
        $AppID  = Yii::$app->params['AppID'];
        $type   = $post['type'] ?? 0;

        $promoter_setting = StoreSetting('promoter_setting');
        //自购返佣.获取分销商一级下线时
        if ($promoter_setting['self_buy'] === 2 && $type != 2 && !is_array($parent)) {
            $where = ['and', ['u.AppID' => $AppID], ['or', ['u.parent_id' => $parent], ['u.id' => $parent]]];
        } else {
            $where = ['u.AppID' => $AppID, 'u.parent_id' => $parent];
        }

        $nickname = $post['nickname'] ?? '';
        if ($nickname) {
            $where = ['and', $where, ['like', 'u.nickname', $nickname]];
        }

        if ($type) {
            if ($type === 1) {
                $where = ['and', $where, ['p.status' => 2]];
            } elseif ($type === 2) {
                $where = ['and', $where, ['or', ['<>', 'p.status', 2], ['p.status' => null]]];
            }
        }

        $query = User::find()
            ->alias('u')
            ->leftJoin(['p' => Promoter::tableName()], 'u.id = p.UID')
            ->leftJoin(['l' => PromoterLevel::tableName()], 'l.level = p.level')
            ->leftJoin(['o' => Oauth::tableName()], 'o.UID = u.id')
            ->where($where);

        $subQuery = PromoterCommission::find()
            ->alias('c')
            ->leftJoin(['or' => PromoterOrder::tableName()], 'c.order_goods_id = or.order_goods_id')
            ->andWhere(['and', ['c.beneficiary' => $UID], ['>=', 'or.status', 0]])
            ->groupBy('or.UID')
            ->select('sum(c.commission) commission,sum(c.sales_amount) sales_amount,or.UID');
        $query->leftJoin(['sq' => $subQuery], 'u.id = sq.UID');
        $query->addSelect([
            'p.status',
            'l.name level_name',
            'u.id', 'u.avatar', 'u.nickname', 'u.mobile', 'u.realname', 'u.bind_time',
            'o.type',
            "commission" => "IF(sq.`commission`,sq.`commission`, 0)", "sales_amount" => "IF(sq.`sales_amount`,sq.`sales_amount`, 0)",
        ]);

        $data = new ActiveDataProvider(
            [
                'query'      => $query->orderBy(['u.bind_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        return $data;
    }

    /**
     * 添加分销商搜索
     */
    private function addSearch()
    {
        $AppID  = Yii::$app->params['AppID'];
        $where  = ['and', ['u.AppID' => $AppID], ['or', ['<>', 'p.status', 2], ['p.status' => null]]];
        $search = Yii::$app->request->get('search', '');
        if ($search) {
            $where = ['and', $where, ['or', ['u.id' => $search], ['like', 'u.nickname', $search]]];
        }

        $data = User::find()
            ->alias('u')
            ->leftJoin(['p' => Promoter::tableName()], 'u.id = p.UID')
            ->where($where)
            ->select('u.id,u.nickname,u.mobile,u.realname')
            ->orderBy(['u.created_time' => SORT_DESC])
            ->asArray()
            ->all();
        return $data;
    }

    /**
     * 移交分销商搜索
     */
    private function transferSearch()
    {
        $AppID    = Yii::$app->params['AppID'];
        $from_uid = Yii::$app->request->get('from_uid', false);
        if (!$from_uid) {
            Error('来源用户缺失');
        }
        $where  = ['and', ['u.AppID' => $AppID, 'p.status' => 2], ['<>', 'p.UID', $from_uid]];
        $mobile = Yii::$app->request->get('mobile', '');
        if ($mobile) {
            $where = ['and', $where, ['mobile' => $mobile]];
        }

        $data = Promoter::find()
            ->alias('p')
            ->leftJoin(['u' => User::tableName()], 'u.id = p.UID')
            ->where($where)
            ->select('p.UID,u.realname,u.mobile')
            ->orderBy(['u.created_time' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($data as &$v) {
            $v['show_value'] = $v['realname'] . ' ' . $v['mobile'];
        }
        return $data;
    }

    /**
     * 分销商详情
     */
    public function actionView()
    {
        $id = Yii::$app->request->get('id', 0);

        $model = Promoter::find()->where(['UID' => $id])->one();

        $data         = $model->toArray();
        $user_info    = $model->user;
        $data['user'] = [
            'nickname' => $user_info->nickname,
            'avatar'   => $user_info->avatar,
            'realname' => $user_info->realname,
            'mobile'   => $user_info->mobile,
            'type'     => $user_info->oauth->type,
        ];
        $data['level_name'] = $model->levelInfo->name;
        if ($model->transfer_id && $model->status === 4) {
            $data['transfer_name'] = $model->transfer->nickname;
        }
        if ($model->invite_id > 0) {
            $data['invite_nickname'] = $model->invite->nickname;
        }

        $data['children'] = [];
        $setting          = StoreSetting('promoter_setting');
        $level_number     = $setting['level_number'];

        $first_c_p                 = $this->getChildren($id, 1);
        $first_c_o                 = $this->getChildren($id, 2);
        $data['children']['first'] = [
            'promoter' => count($first_c_p),
            'ordinary' => count($first_c_o),
            'parent'   => $id,
        ];

        if ($setting['self_buy'] === 2) {
            $data['children']['first']['promoter']++;
        }

        if ($level_number > 1) {
            $second_c_p                 = $this->getChildren($first_c_p, 1);
            $second_c_o                 = $this->getChildren($first_c_p, 2);
            $data['children']['second'] = [
                'promoter' => count($second_c_p),
                'ordinary' => count($second_c_o),
                'parent'   => to_json($first_c_p),
            ];
        }

        if ($level_number > 2) {
            $third_c_p                 = $this->getChildren($second_c_p, 1);
            $third_c_o                 = $this->getChildren($second_c_p, 2);
            $data['children']['third'] = [
                'promoter' => count($third_c_p),
                'ordinary' => count($third_c_o),
                'parent'   => to_json($second_c_p),
            ];
        }

        $pc_data = PromoterCommission::find()
            ->alias('c')
            ->leftJoin(['o' => PromoterOrder::tableName()], 'c.order_goods_id = o.order_goods_id')
            ->where(['and', ['>=', 'o.status', 0], ['c.beneficiary' => $id]])
            ->select('sum(c.commission) all_commission_amount,sum(c.sales_amount) sales_amount')
            ->asArray()
            ->one();
        $data['sales_amount']          = $pc_data['sales_amount'] ?: 0;
        $data['apply_content']         = to_array($data['apply_content']);
        $data['wait_account']          = qm_round($pc_data['all_commission_amount'] - $data['commission_amount']); //累计佣金-累计已结算佣金=待结算
        $data['is_withdrawal']         = qm_round($data['commission_amount'] - $data['commission']); //累计已结算-待提现=已提现
        $data['all_commission_amount'] = $pc_data['all_commission_amount'] ?: 0;

        return $data;
    }

    /**
     * $type  1分销  2普通
     */
    private function getChildren($parent, $type = 0)
    {
        $AppID = Yii::$app->params['AppID'];
        $where = ['u.AppID' => $AppID, 'u.parent_id' => $parent];
        if ($type === 1) {
            $where = ['and', $where, ['p.status' => 2]];
        } elseif ($type === 2) {
            $where = ['and', $where, ['or', ['<>', 'p.status', 2], ['p.status' => null]]];
        }
        $data = User::find()
            ->alias('u')
            ->joinWith([
                'promoter as p',
            ])
            ->where($where)
            ->select('u.id')
            ->asArray()
            ->all();
        return array_column($data, 'id');
    }

    /**
     * 添加分销商
     */
    private function addPromoter()
    {
        $post       = Yii::$app->request->post();
        $UID        = $post['UID'] ?? 0;
        $user_model = M('users', 'User')::findOne($UID);
        if (!$user_model) {
            Error('用户不存在');
        }
        $t = Yii::$app->db->beginTransaction();

        $user_model->mobile   = $post['mobile'] ?? null;
        $user_model->realname = $post['realname'] ?? '';
        if ($user_model->mobile) {
            if (!preg_match("/^1[0-9]{10}$/", $user_model->mobile)) {
                Error('请填写正确手机号');
            }
            $check = M('users', 'User')::find()->where(['and', ['mobile' => $user_model->mobile], ['<>', 'id', $UID]])->with(['oauth' => function ($query) {
                $query->select('UID,type');
            }])->asArray()->all();
            if (!empty($check)) {
                $oauth = $user_model->oauth;
                foreach ($check as $value) {
                    if ($value['oauth']['type'] === $oauth->type) {
                        Error('手机号已存在');
                    }
                }
            }
        }
        $user_res = $user_model->save();

        $model = Promoter::findOne(['UID' => $UID]);
        $time  = time();
        if (!$model) {
            $model               = new Promoter;
            $model->UID          = $UID;
            $model->join_time    = $time;
            $model->created_time = $time;
        } elseif ($model->status == 2) {
            $t->rollBack();
            Error('该用户已经是分销商了');
        }

        if (!isset($model->apply_time) || !$model->apply_time) {
            $model->apply_time = $time;
        }

        $model->status      = 2;
        $model->level       = $post['level'] ?? 1;
        $model->start_level = $post['level'] ?? 1;
        $model->invite_id   = 0;

        $res = $model->save();

        if ($res && $user_res) {
            if ($model->invite_id) {
                $c_res = Promoter::updateAllCounters(['invite_number' => 1], ['UID' => $model->invite_id]);
                if (!$c_res) {
                    $t->rollBack();
                    Error('系统错误');
                }
            }
            $t->commit();
            $ComPromoter = new ComPromoter();
            $ComPromoter->setLevel([$UID], 3);
            return true;
        } else {
            $t->rollBack();
            Error('添加失败');
        }
    }

    public function actionUpdate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'pass':
                return $this->pass();
                break;
            case 'refuse':
                return $this->refuse();
                break;
            case 'repel':
                return $this->repel();
                break;
            case 'dispense':
                return $this->dispense();
                break;
            default:
                return $this->edit();
                break;
        }
    }

    private function edit()
    {
        $AppID       = Yii::$app->params['AppID'];
        $id          = Yii::$app->request->get('id', 0);
        $start_level = Yii::$app->request->post('level', 1);
        $model       = Promoter::findOne(['UID' => $id]);
        if (!$model) {
            Error('分销申请不存在');
        }
        if ($model->status != 2) {
            Error('该用户还不是分销商');
        }

        $t = Yii::$app->db->beginTransaction();
        if ($start_level != $model->level) {
            if ($start_level > $model->level) {
                $type = 1;
            } else {
                $type = 2;
            }
            $level_name                = PromoterLevel::find()->where(['AppID' => $AppID, 'is_deleted' => 0])->select('name,level')->asArray()->all();
            $level_name                = array_column($level_name, null, 'level');
            $log_model                 = M('promoter', 'PromoterLevelChangeLog', true);
            $log_model->UID            = $model->UID;
            $log_model->old_level      = $model->level;
            $log_model->old_level_name = $level_name[$model->level]['name'];
            $log_model->new_level      = $start_level;
            $log_model->new_level_name = $level_name[$start_level]['name'];
            $log_model->type           = $type;
            $log_model->created_time   = time();
            if (!$log_model->save()) {
                Error('保存失败');
            }
        }

        $model->start_level = $start_level;
        $model->level       = $start_level;

        if ($model->save()) {
            $t->commit();
            return $model;
        } else {
            $t->rollBack();
            Error('保存失败');
        }
    }

    /**
     * 通过
     */
    private function pass()
    {
        $id    = Yii::$app->request->get('id', 0);
        $model = Promoter::findOne(['UID' => $id]);
        if (!$model) {
            Error('分销申请不存在');
        }
        if ($model->status != 1) {
            Error('该用户不处于待审核状态');
        }

        $model->status    = 2;
        $model->join_time = time();
        $invite_nickname  = '';
        if ($model->invite_id < 0) {
            $model->invite_id = abs($model->invite_id);
            $invite_user      = User::findOne($model->invite_id);
            if ($invite_user) {
                $invite_nickname = $invite_user->nickname;
            }
        }

        $t = Yii::$app->db->beginTransaction();
        if ($model->save()) {
            if ($model->invite_id) {
                $c_res = Promoter::updateAllCounters(['invite_number' => 1], ['UID' => $model->invite_id]);
                if (!$c_res) {
                    $t->rollBack();
                    Error('系统错误');
                }
            }
            if ($model->apply_content) {
                $apply_content = to_array($model->apply_content);
                if (is_array($apply_content) && $apply_content[0]['value']) {
                    $u_res = User::updateAll(['realname' => $apply_content[0]['value']], ['id' => $id]);
                    if ($u_res !== 1 && $u_res !== 0) {
                        $t->rollBack();
                        Error('系统错误');
                    }
                }
            }
            $t->commit();
            $ComPromoter = new ComPromoter();
            $ComPromoter->setLevel([$id], 2);
            Yii::$app->subscribe->setUser($model->UID)->setPage('promoter/pages/index')->send(new PromoterVerifyMessage([
                'result' => '通过',
                'name'   => $model->user->nickname,
                'time'   => date('Y-m-d H:i', $model->apply_time),
            ]));
            $this->module->event->sms = [
                'type'   => 'promoter_verify',
                'mobile' => [$model->user->mobile],
                'params' => [
                    'result' => '通过',
                ],
            ];
            $this->module->trigger('send_sms');
            $model->apply_content    = to_array($model->apply_content);
            $data                    = $model->toArray();
            $data['invite_nickname'] = $invite_nickname;
            return $data;
        } else {
            Error('审核失败');
        }

    }

    /**
     * 拒绝
     */
    private function refuse()
    {
        $id    = Yii::$app->request->get('id', 0);
        $note  = Yii::$app->request->post('note', '');
        $model = Promoter::findOne(['UID' => $id]);
        if (!$model) {
            Error('分销申请不存在');
        }
        if ($model->status != 1) {
            Error('该用户不处于待审核状态');
        }

        $model->note   = $note;
        $model->status = 3;

        if ($model->save()) {
            Yii::$app->subscribe->setUser($model->UID)->setPage('promoter/pages/index')->send(new PromoterVerifyMessage([
                'result' => '拒绝',
                'name'   => $model->user->nickname,
                'time'   => date('Y-m-d H:i', $model->apply_time),
            ]));
            $this->module->event->sms = [
                'type'   => 'promoter_verify',
                'mobile' => [$model->user->mobile],
                'params' => [
                    'result' => '拒绝',
                ],
            ];
            $this->module->trigger('send_sms');
            return $model;
        } else {
            Error('审核失败');
        }
    }

    /**
     * 清退
     */
    private function repel()
    {
        $id    = Yii::$app->request->get('id', 0);
        $model = Promoter::findOne(['UID' => $id]);
        if (!$model) {
            Error('分销商不存在');
        }
        if ($model->status != 2) {
            Error('该用户状态不可清退');
        }

        $set_level_uid = [$id];

        $t = Yii::$app->db->beginTransaction();

        $model->invite_id   = 0;
        $model->level       = 1;
        $model->start_level = 1;
        $model->status      = 4;
        $model->repel_time  = time();

        $data          = ['parent_id' => 0, 'bind_time' => null];
        $children_list = M('users', 'User')::find()->where(['parent_id' => $id])->select('id,parent_id')->asArray()->all();
        $lose_list     = $children_list;
        $children_list = array_column($children_list, 'id');

        $transfer_id = Yii::$app->request->post('transfer_id', 0);
        $ComPromoter = new ComPromoter();
        if ($transfer_id) {
            if ($transfer_id == $id) {
                Error('不能移交给自己');
            }
            array_push($set_level_uid, $transfer_id);
            $transfer = M('users', 'User')::findOne($transfer_id);
            if ($transfer->promoter) {
                if ($transfer->promoter->status == 2) {
                    //判断清除用户下级是否有位于移交用户的分佣链中的,有则要剔除
                    $check = $ComPromoter->relationCheck($id, $transfer_id);
                    if (!$check['status']) {
                        $children_list = array_merge(array_diff($children_list, [(int) $check['data']]));
                        $res           = M('users', 'User')::updateAll(['parent_id' => 0], ['id' => $check['data']]); //剔除的parent_id设为0
                        if (!$res) {
                            $t->rollBack();
                            Error('清退失败');
                        }
                    }
                    $data = ['parent_id' => $transfer_id, 'bind_time' => time()];
                } else {
                    Error('移交用户不是分销商');
                }
            } else {
                Error('移交分销商不存在');
            }
        }
        $model->transfer_id = $transfer_id;
        if ($model->save()) {
            if (count($children_list) > 0) {
                $batch_res = M('users', 'User')::updateAll($data, ['id' => $children_list]);
                if (!$batch_res) {
                    $t->rollBack();
                    Error('清退失败');
                }
            }

            $t->commit();
            $ComPromoter->setLevel(array_unique($set_level_uid), 2);
            $ComPromoter->loseLog($lose_list, 2);
            $this->module->event->sms = [
                'type'   => 'clear_identity',
                'mobile' => [$model->user->mobile],
                'params' => [
                    'name' => '分销商',
                ],
            ];
            $this->module->trigger('send_sms');
            return $model;
        } else {
            Error('清退失败');
        }

    }

    /**
     * 下级的脱离与分配
     */
    private function dispense()
    {
        $id    = Yii::$app->request->get('id', 0);
        $model = M('users', 'User')::findOne($id);

        if (!$model) {
            Error('用户不存在');
        }
        $parent_id     = $model->parent_id;
        $set_level_uid = [$parent_id];

        $model->parent_id = 0;

        $transfer_id = Yii::$app->request->post('transfer_id', 0);
        $ComPromoter = new ComPromoter();
        if ($transfer_id) {
            if ($transfer_id == $id) {
                Error('不能移交给目标自己');
            }
            array_push($set_level_uid, $transfer_id);
            $transfer = M('users', 'User')::findOne($transfer_id);
            if ($transfer && $transfer->promoter) {
                if ($transfer->promoter->status == 2) {
                    //判断清除用户下级是否有位于移交用户的分佣链中的,有则要剔除
                    $check = $ComPromoter->relationCheck($id, $transfer_id, 4);
                    if ($check['status']) {
                        $model->parent_id = $transfer_id;
                        $model->bind_time = time();
                    } else {
                        Error('不能将用户移交给目标自己的下线');
                    }
                } else {
                    Error('移交用户不是分销商');
                }
            } else {
                Error('移交分销商不存在');
            }
        }

        $res = $model->save();
        if ($res) {
            $ComPromoter->setLevel(array_unique($set_level_uid), 2);
            $ComPromoter->loseLog([['id' => $id, 'parent_id' => $parent_id]], 1);
            return $res;
        } else {
            Error('解除失败');
        }
    }

}
