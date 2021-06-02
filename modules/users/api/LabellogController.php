<?php
/**
 * 用户标签控制器
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace users\api;

use framework\common\BasicController;
use Yii;

class LabellogController extends BasicController
{
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 手动打标签
     */
    public function actionCreate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'batch': //商家备注
                return $this->batchSave();
                break;
            default:
                return $this->save();
                break;
        }

    }

    public function save()
    {

        if (!N('UID')) {
            Error('用户ID缺失');
        }
        if (!N('label_id', 'array')) {
            Error('标签ID格式出错');
        }
        $post        = Yii::$app->request->post();
        $UID         = $post['UID'];
        $label_id    = array_column($post['label_id'], 'id');
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $time        = time();

        $log_list = M('users', 'LabelLog')::find()->where(['is_deleted' => 0, 'UID' => $UID])->select('id,label_id')->asArray()->all(); //获取用户已获得的标签ID
        $get_list = array_column($log_list, 'label_id');
        $log_list = array_column($log_list, 'id');

        $out_list = array_diff($get_list, $label_id); //修改后失去的标签
        $in_list  = array_diff($label_id, $get_list); //修改后获得的标签

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        $del_res     = M('users', 'LabelLog')::deleteAll(['id' => $log_list]); //批量插入前先删除之前数据
        $col         = ['UID', 'label_id', 'created_time'];
        $row         = [];
        foreach ($label_id as $v) {
            array_push($row, [$UID, $v, $time]);
        }
        $prefix     = Yii::$app->db->tablePrefix;
        $table_name = $prefix . 'user_label_log';
        $batch_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
        $out_res    = M('users', 'Label')::updateAllCounters(['users_number' => -1], ['id' => $out_list]);
        $in_res     = M('users', 'Label')::updateAllCounters(['users_number' => 1], ['id' => $in_list]);

        if (!empty($out_list)) {
            $out_label_info = M('users', 'Label')::find()->where(['id' => $out_list, 'type' => 2])->select('id,filter_user')->asArray()->all();
            //如果删除的是自动标签,则将改用户加入该标签黑名单
            foreach ($out_label_info as $label_info) {
                $filter_user = to_array($label_info['filter_user']);
                if (!in_array($UID, $filter_user)) {
                    array_push($filter_user, intval($UID));
                    $filter_user = to_json($filter_user);
                    $filter_res  = M('users', 'Label')::updateAll(['filter_user' => $filter_user], ['id' => $label_info['id']]);
                }
            }
        }

        if (count($log_list) === $del_res && count($out_list) === $out_res && count($in_list) === $in_res && count($row) === $batch_res) {
            $transaction->commit();
            return true;
        } else {
            $transaction->rollBack(); //事务回滚
            Error('保存失败');
        }
    }

    public function batchSave()
    {
        if (!N('UID', 'array')) {
            Error('用户ID错误');
        }
        if (!N('label_id', 'array')) {
            Error('标签ID错误');
        }
        $post        = Yii::$app->request->post();
        $UID         = $post['UID'];
        $label_id    = array_column($post['label_id'], 'id');
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $time        = time();

        $all_log_list = M('users', 'LabelLog')::find()->where(['is_deleted' => 0, 'UID' => $UID, 'label_id' => $label_id])->select('id,UID,label_id')->asArray()->all(); //获取用户已获得的标签ID
        // $all_label_list = array_column($all_log_list, null, 'label_id');
        $label_user_data = [];
        //每个标签下存在的用户
        foreach ($all_log_list as $log) {
            if (isset($label_user_data[$log['label_id']])) {
                array_push($label_user_data[$log['label_id']], $log['UID']);
            } else {
                $label_user_data[$log['label_id']] = [$log['UID']];
            }

        }

        $label_data = [];
        foreach ($label_id as $id) {
            if (isset($label_user_data[$id])) {
                $label_data[$id] = $label_user_data[$id];
            } else {
                $label_data[$id] = [];
            }

        }

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        $col         = ['UID', 'label_id', 'created_time'];
        $row         = [];
        $users_count = count($UID);
        foreach ($label_data as $lab_id => $label_users) {
            $count = $users_count - count($label_users); //计算有几个人没有此标签
            if ($count > 0) {
                $count_res = M('users', 'Label')::updateAllCounters(['users_number' => $count], ['id' => $lab_id]);
                if ($count_res) {
                    $in_list = array_diff($UID, $label_users); //获取没有此标签人id
                    foreach ($in_list as $v) {
                        array_push($row, [$v, $lab_id, $time]);
                    }
                } else {
                    $transaction->rollBack(); //事务回滚
                    Error('保存失败');
                }
            }

        }
        $prefix     = Yii::$app->db->tablePrefix;
        $table_name = $prefix . 'user_label_log';
        $batch_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
        if ($batch_res === count($row)) {
            $transaction->commit();
            return true;
        } else {
            $transaction->rollBack(); //事务回滚
            Error('保存失败');
        }
    }

    /**
     * 自动打标签
     */
    public static function giveLabel($event)
    {
        $UID         = $event->pay_uid;
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $time        = time();
        $get_list    = M('users', 'LabelLog')::find()->where(['is_deleted' => 0, 'UID' => $UID])->select('label_id')->asArray()->all(); //获取用户已获得的标签ID
        $get_list    = array_column($get_list, 'label_id');
        $where       = ['AppID' => $AppID, 'merchant_id' => $merchant_id, 'is_deleted' => 0, 'type' => 2, 'status' => 1];
        $label_list  = M('users', 'Label')::find()->where(['and', $where, ['not in', 'id', $get_list]])->select('id,conditions_status,conditions_setting,filter_user')->asArray()->all(); //获取用户还未拥有的自动标签
        if (count($label_list) > 0) {
            $user_statistical = M('users', 'UserStatistical')::find()->where(['UID' => $UID])->select('buy_number,buy_amount')->asArray()->one();
            $order_goods      = M('order', 'OrderGoods')::find()
                ->alias('ordergoods')
                ->joinWith([
                    'order as order' => function ($query) {
                        $query->select('UID,order_sn');
                    },
                    'goods as goods',
                ])
                ->where(['and', ['order.UID' => $UID], ['>', 'order.status', 200]])
                ->select('ordergoods.order_sn,ordergoods.goods_id')
                ->asArray()
                ->all();

            $goods_list = []; //购买商品列表
            $group_list = []; //购买商品的分组列表
            foreach ($order_goods as $goods) {
                array_push($goods_list, $goods['goods_id']);
                if ($goods['goods']) {
                    $group      = explode('-', trim($goods['goods']['group'], '-'));
                    $group_list = array_merge($group_list, $group);
                }
            }
            $goods_list = array_unique($goods_list);
            $group_list = array_unique($group_list);
            foreach ($group_list as &$group) {
                $group = intval($group);
            }

            $col = ['UID', 'label_id', 'created_time'];
            $row = [];
            foreach ($label_list as $v) {
                $check_number = 0; //需要判断的条件
                $pass_number  = 0; //通过判断的条件
                $status       = $v['conditions_status'];
                $setting      = to_array($v['conditions_setting']);
                $filter_user  = to_array($v['filter_user']);

                if (in_array($UID, $filter_user)) {
                    continue;
                }

                //购物时间
                if ($setting['shopping_time']['checked']) {
                    $check_number++;
                    $shopping_time = $setting['shopping_time']['value'];
                    if ($time >= $shopping_time['start'] && $time <= $shopping_time['end']) {
                        $pass_number++;
                    }
                }

                //购物次数
                if ($setting['shopping_number']['checked']) {
                    $check_number++;
                    $shopping_number = $setting['shopping_number']['value'];
                    if ($user_statistical['buy_number'] >= $shopping_number) {
                        $pass_number++;
                    }
                }

                //购物金额
                if ($setting['shopping_amount']['checked']) {
                    $check_number++;
                    $shopping_amount = $setting['shopping_amount']['value'];
                    if ($user_statistical['buy_amount'] >= $shopping_amount) {
                        $pass_number++;
                    }
                }

                //购买指定商品
                if ($setting['shopping_goods']['checked']) {
                    $check_number++;
                    $shopping_goods = $setting['shopping_goods']['value'];
                    if (count(array_intersect($goods_list, $shopping_goods)) > 0) {
                        $pass_number++;
                    }
                }

                //购买指定商品
                if ($setting['shopping_group']['checked']) {
                    $check_number++;
                    $shopping_group = $setting['shopping_group']['value'];
                    if (count(array_intersect($group_list, $shopping_group)) > 0) {
                        $pass_number++;
                    }
                }

                if ($status == 1) {
                    if ($pass_number > 0 && $pass_number == $check_number) {
                        array_push($row, [$UID, $v['id'], $time]);
                    }
                } else {
                    if ($pass_number > 0) {
                        array_push($row, [$UID, $v['id'], $time]);
                    }
                }
            }

            if (count($row) > 0) {
                $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
                $prefix      = Yii::$app->db->tablePrefix;
                $table_name  = $prefix . 'user_label_log';
                $batch_res   = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
                $batch_label = array_column($row, 1);
                $edit_res    = M('users', 'Label')::updateAllCounters(['users_number' => 1], ['id' => $batch_label]);
                if ($batch_res && $edit_res) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack(); //事务回滚
                }

            }
        }

    }

    /**
     * 删除用户标签
     */
    public function actionDelete()
    {
        $id    = Yii::$app->request->get('id', 0);
        $id    = intval($id);
        $model = M('users', 'LabelLog')::find()->where(['id' => $id, 'is_deleted' => 0])->one();
        if (empty($model)) {
            Error('标签不存在');
        }

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        $res2        = M('users', 'Label')::updateAllCounters(['users_number' => -1], ['id' => $model->label_id]);
        $res         = $model->delete();
        if ($res && $res2) {
            $label_info = M('users', 'Label')::find()->where(['id' => $model->label_id])->one();
            //如果删除的是自动标签,则将改用户加入该标签黑名单
            if ($label_info->type == 2) {
                $filter_user = to_array($label_info->filter_user);
                if (!in_array(intval($model->UID), $filter_user)) {
                    array_push($filter_user, intval($model->UID));
                    $label_info->filter_user = to_json($filter_user);
                    if (!$label_info->save()) {
                        $transaction->rollBack(); //事务回滚
                        Error('删除失败');
                    }
                }

            }

            $transaction->commit();
            return true;

        } else {
            $transaction->rollBack(); //事务回滚
            Error('删除失败');
        }
    }

}
