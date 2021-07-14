<?php
/**
 * 用户管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace users\api;

use framework\common\BasicController;
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
        M('users', 'Label')::findOne(['id' => 1]);
        M('users', 'LabelLog')::findOne(['id' => 1]);
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 处理数据分页问题
     * @return [type] [description]
     */
    public function actionStatistical()
    {
        $wxapp  = $this->sourceStatistical('weapp');
        $wechat = $this->sourceStatistical('wechat');

        $data = [
            //用户总数
            'user_number'             => [
                'wxapp'  => $wxapp['user_number'],
                'wechat' => $wechat['user_number'],
                'all'    => $wxapp['user_number'] + $wechat['user_number'],
            ],
            //昨日用户总数
            'user_number_yesteday'    => [
                'wxapp'  => $wxapp['user_number_yesteday'],
                'wechat' => $wechat['user_number_yesteday'],
                'all'    => $wxapp['user_number_yesteday'] + $wechat['user_number_yesteday'],
            ],
            //用户昨天增长
            'user_grow_yesteday'      => [
                'wxapp'  => $wxapp['user_grow_yesteday'],
                'wechat' => $wechat['user_grow_yesteday'],
                'all'    => $wxapp['user_grow_yesteday'] + $wechat['user_grow_yesteday'],
            ],
            //用户今天增长
            'user_grow_today'         => [
                'wxapp'  => $wxapp['user_grow_today'],
                'wechat' => $wechat['user_grow_today'],
                'all'    => $wxapp['user_grow_today'] + $wechat['user_grow_today'],
            ],
            //昨日订单金额统计
            'order_amount_yesteday'   => [
                'wxapp'  => $wxapp['order_amount_yesteday'],
                'wechat' => $wechat['order_amount_yesteday'],
                'all'    => round($wxapp['order_amount_yesteday'] + $wechat['order_amount_yesteday'], 2),
            ],
            //今日订单金额统计
            'order_amount_today'      => [
                'wxapp'  => $wxapp['order_amount_today'],
                'wechat' => $wechat['order_amount_today'],
                'all'    => round($wxapp['order_amount_today'] + $wechat['order_amount_today'], 2),
            ],
            //昨日订单金额统计
            'pay_amount_yesteday'     => [
                'wxapp'  => $wxapp['pay_amount_yesteday'],
                'wechat' => $wechat['pay_amount_yesteday'],
                'all'    => round($wxapp['pay_amount_yesteday'] + $wechat['pay_amount_yesteday'], 2),
            ],
            //今日订单金额统计
            'pay_amount_today'        => [
                'wxapp'  => $wxapp['pay_amount_today'],
                'wechat' => $wechat['pay_amount_today'],
                'all'    => round($wxapp['pay_amount_today'] + $wechat['pay_amount_today'], 2),
            ],
            //昨日客单价
            'average_amount_yesteday' => [
                'wxapp'  => $wxapp['average_amount_yesteday'],
                'wechat' => $wechat['average_amount_yesteday'],
                'all'    => round($wxapp['average_amount_yesteday'] + $wechat['average_amount_yesteday'], 2),
            ],
            //今日客单价
            'average_amount_today'    => [
                'wxapp'  => $wxapp['average_amount_today'],
                'wechat' => $wechat['average_amount_today'],
                'all'    => round($wxapp['average_amount_today'] + $wechat['average_amount_today'], 2),
            ],
        ];

        return $data;
    }

    /**
     * 用户统计,根据来源
     * @param  integer $source [description]
     * @return [type]          [description]
     */
    public function sourceStatistical($source = 'weapp')
    {
        $AppID     = Yii::$app->params['AppID'];
        $where     = [];
        $yesterday = strtotime('yesterday');
        $today     = strtotime(date("Y-m-d"));

        $user_number = M('users', 'User')::find()
            ->alias('user')
            ->joinWith([
                'oauth as oauth',
            ])
            ->where(['user.AppID' => $AppID, 'oauth.type' => $source])
            ->count(); //总人数

        $user_grow_neartwo = M('users', 'User')::find()
            ->alias('user')
            ->joinWith([
                'oauth as oauth',
            ])
            ->where(['and', ['>=', 'user.created_time', $yesterday], ['user.AppID' => $AppID, 'oauth.type' => $source]])
            ->count(); //近两天增长人数

        $user_grow_today = M('users', 'User')::find()
            ->alias('user')
            ->joinWith([
                'oauth as oauth',
            ])
            ->where(['and', ['>=', 'user.created_time', $today], ['user.AppID' => $AppID, 'oauth.type' => $source]])
            ->count(); //今天增长人数

        $user_number_yesteday = $user_number - $user_grow_today; //昨天总人数
        $user_grow_yesteday   = $user_grow_neartwo - $user_grow_today; //昨天增长人数

        $order_list_neartwo    = M('order', 'Order')::find()->where(['and', ['>', 'pay_time', $yesterday], ['AppID' => $AppID, 'source' => $source]])->select('UID,pay_amount,pay_time')->all(); //近两天付款订单列表
        $order_amount_yesteday = 0; //昨天订单金额
        $order_amount_today    = 0; //今天订单金额
        $user_list_yesteday    = []; //昨天付款人
        $user_list_today       = []; //今天付款人
        foreach ($order_list_neartwo as $value) {
            if ($value['pay_time'] >= $today) {
                $order_amount_today += $value['pay_amount'];
                array_push($user_list_today, $value['UID']);
            } else {
                $order_amount_yesteday += $value['pay_amount'];
                array_push($user_list_yesteday, $value['UID']);
            }
        }

        $user_list_yesteday = array_unique($user_list_yesteday);
        $user_list_today    = array_unique($user_list_today);

        $average_amount_yesteday = $order_amount_yesteday / (count($user_list_yesteday) ?: 1); //昨天客单价
        $average_amount_today    = $order_amount_today / (count($user_list_today) ?: 1); //今天客单价

        $data = [
            'user_number'             => $user_number,
            'user_number_yesteday'    => $user_number_yesteday,
            'user_grow_yesteday'      => $user_grow_yesteday,
            'user_grow_today'         => $user_grow_today,
            'order_amount_yesteday'   => round($order_amount_yesteday, 2),
            'order_amount_today'      => round($order_amount_today, 2),
            'pay_amount_yesteday'     => count($user_list_yesteday),
            'pay_amount_today'        => count($user_list_today),
            'average_amount_yesteday' => round($average_amount_yesteday, 2),
            'average_amount_today'    => round($average_amount_today, 2),
        ];
        return $data;
    }

    /**
     * 处理数据搜索问题
     * @return [type] [description]
     */
    public function actionSearch()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        //订单分组
        $keyword = Yii::$app->request->post('keyword', []);

        $AppID = Yii::$app->params['AppID'];
        $where = ['user.AppID' => $AppID];

        //判断插件已经安装，则执行
        if ($this->plugins("task", "status")) {
            $with = [
                'statistical as statistical',
                'oauth as oauth',
                'taskuser as taskuser',
                'labellog as labellog' => function ($q) {
                    $q->with(['label' => function ($query) {
                        $query->select('id,name');
                    }]);
                },
            ];
        } else {
            $with = [
                'statistical as statistical',
                'oauth as oauth',
                'labellog as labellog' => function ($q) {
                    $q->with(['label' => function ($query) {
                        $query->select('id,name');
                    }]);
                },
            ];
        }

        //关键词搜索
        $search = $keyword['search'] ?? false;
        if ($search) {
            $where = ['and', $where, ['or', ['like', 'user.nickname', $search], ['like', 'user.realname', $search], ['user.mobile' => $search], ['user.id' => $search]]];
        }

        //用户来源筛选
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['oauth.type' => $source]];
        }

        //用户标签筛选
        $label = $keyword['label'] ?? false;
        if (!empty($label) && is_array($label)) {
            $where = ['and', $where, ['labellog.label_id' => $label]];
        }

        //购买次数区间
        $buy_number_start = $keyword['buy_number_start'] ?? -1;
        if ($buy_number_start >= 0) {
            $where = ['and', $where, ['>=', 'statistical.buy_number', $buy_number_start]];
        }
        $buy_number_end = $keyword['buy_number_end'] ?? -1;
        if ($buy_number_end >= 0) {
            $where = ['and', $where, ['<=', 'statistical.buy_number', $buy_number_end]];
        }

        //注册时间区间
        $created_time_start = $keyword['created_time_start'] ?? false;
        if ($created_time_start > 0) {
            $where = ['and', $where, ['>=', 'user.created_time', $created_time_start]];
        }
        $created_time_end = $keyword['created_time_end'] ?? false;
        if ($created_time_end > 0) {
            $where = ['and', $where, ['<=', 'user.created_time', $created_time_end]];
        }

        //上次消费时间区间
        $last_buy_time_start = $keyword['last_buy_time_start'] ?? false;
        if ($last_buy_time_start > 0) {
            $where = ['and', $where, ['>=', 'statistical.last_buy_time', $last_buy_time_start]];
        }
        $last_buy_time_end = $keyword['last_buy_time_end'] ?? false;
        if ($last_buy_time_end > 0) {
            $where = ['and', $where, ['<=', 'statistical.last_buy_time', $last_buy_time_end]];
        }

        //最后访问时间区间
        $last_visit_time_start = $keyword['last_visit_time_start'] ?? false;
        if ($last_visit_time_start > 0) {
            $where = ['and', $where, ['>=', 'statistical.last_visit_time', $last_visit_time_start]];
        }
        $last_visit_time_end = $keyword['last_visit_time_end'] ?? false;
        if ($last_visit_time_end > 0) {
            $where = ['and', $where, ['<=', 'statistical.last_visit_time', $last_visit_time_end]];
        }

        //处理排序
        $sort    = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['user.created_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                if (in_array($key, ['buy_number', 'buy_amount', 'last_buy_time', 'last_visit_time'])) {
                    $orderBy['statistical.' . $key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
                } else {

                    $orderBy['user.' . $key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
                }
            }
        }

        $data = new ActiveDataProvider(
            [
                'query'      => M('users', 'User')::find()
                    ->alias('user')
                    ->joinWith($with)
                    ->where($where)
                    ->groupBy(['user.id'])
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        /*$list = $data->getModels();
        foreach ($list as $key => &$value) {

        }
        $data->setModels($list);*/
        return $data;
    }

    /**
     * 用户详情
     * @return [type] [description]
     */
    public function actionView()
    {
        $id = Yii::$app->request->get('id', false);

        //判断插件已经安装，则执行 taskuser
        if ($this->plugins("task", "status")) {
            $with = [
                'statistical',
                'oauth',
                'taskuser',
                'labellog' => function ($query) {
                    $query->where(['is_deleted' => 0])->with(['label' => function ($query) {
                        $query->select('id,name');
                    }]);
                },

            ];
        } else {
            $with = [
                'statistical',
                'oauth',
                'labellog' => function ($query) {
                    $query->where(['is_deleted' => 0])->with(['label' => function ($query) {
                        $query->select('id,name');
                    }]);
                },
            ];
        }

        $result = M('users', 'User')::find()
            ->where(['id' => $id])
            ->with($with)
            ->asArray()
            ->one();

        if (empty($result)) {
            Error('用户找不到');
        }

        $result['pay_number']    = ['all' => 0, 'wxapp' => 0, 'wechat' => 0];
        $result['pay_amount']    = ['all' => 0, 'wxapp' => 0, 'wechat' => 0];
        $result['after_number']  = ['all' => 0, 'wxapp' => 0, 'wechat' => 0];
        $result['return_amount'] = ['all' => 0, 'wxapp' => 0, 'wechat' => 0];

        if ($result['mobile']) {
            $bind_user = M('users', 'User')::find()->where(['mobile' => $result['mobile']])->select('id')->asArray()->all();
            $bind_user = array_column($bind_user, 'id');
        } else {
            $bind_user = $id;
        }

        $order = M('order', 'Order')::find()->where(['and', ['>', 'status', 200], ['UID' => $bind_user]])->asArray()->all();
        foreach ($order as $o_v) {
            $result['pay_number']['all']++;
            $result['pay_amount']['all'] = round(($result['pay_amount']['all'] + $o_v['pay_amount']), 2);
            if ($o_v['source'] == 'weapp') {
                $result['pay_number']['wxapp']++;
                $result['pay_amount']['wxapp'] = round(($result['pay_amount']['wxapp'] + $o_v['pay_amount']), 2);
            } else {
                $result['pay_number']['wechat']++;
                $result['pay_amount']['wechat'] = round(($result['pay_amount']['wechat'] + $o_v['pay_amount']), 2);
            }

        }
        $order_after = M('order', 'OrderAfter')::find()->where(['UID' => $bind_user, 'is_deleted' => 0])->asArray()->all();
        foreach ($order_after as $o_a_v) {
            $result['after_number']['all']++;
            $result['return_amount']['all'] = round(($result['return_amount']['all'] + $o_a_v['actual_refund']), 2);
            if ($o_a_v['source'] == 'weapp') {
                $result['after_number']['wxapp']++;
                $result['return_amount']['wxapp'] = round(($result['return_amount']['wxapp'] + $o_a_v['actual_refund']), 2);
            } else {
                $result['after_number']['wechat']++;
                $result['return_amount']['wechat'] = round(($result['return_amount']['wechat'] + $o_a_v['actual_refund']), 2);
            }
        }

        $result['coupon'] = M('coupon', 'UserCoupon')::find()->where(['UID' => $id, 'is_deleted' => 0])->count('id');

        return $result;
    }

    /**
     * 用户修改
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'setting': //用户设置
                return $this->setting();
                break;
            default:
                Error('未定义操作');
                break;
        }
    }

    /**
     * 电话和姓名设置
     * @return [type] [description]
     */
    public function setting()
    {
        $id   = Yii::$app->request->get('id', false);
        $post = Yii::$app->request->post();

        $model = M('users', 'User')::findOne($id);
        if (empty($model)) {
            Error('用户不存在');
        }

        if (N('mobile')) {
            if (!preg_match("/^1[0-9]{10}$/", $post['mobile'])) {
                Error('请填写正确手机号');
            }
            $check = M('users', 'User')::find()->where(['and', ['mobile' => $post['mobile']], ['<>', 'id', $id]])->with(['oauth' => function ($query) {
                $query->select('UID,type');
            }])->asArray()->all();
            if (!empty($check)) {
                foreach ($check as $value) {
                    if ($value['oauth']['type'] === $model->oauth->type) {
                        Error('手机号已存在');
                    }
                }
            }
        }

        $model->setScenario('setting');
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                return $model;
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }
}
