<?php
/**
 * 分销处理控制器
 */

namespace app\components;

use promoter\models\Promoter;
use promoter\models\PromoterCommission;
use promoter\models\PromoterLevel;
use promoter\models\PromoterOrder;
use users\models\User;
use Yii;

class ComPromoter
{
    /**
     * 分销商上下级绑定,关系判断(下级不能在上级的返佣链中)
     * 不存在返佣链中则返回true
     * $max_num 邀请绑定时为4  后台转移时为5
     * $num 当前第几层循环
     */
    public function relationCheck($target, $source, $max_num = 5, $num = 0)
    {
        if ($num === $max_num) {
            return [
                'status' => true,
            ];
        }
        $data      = User::findOne($source);
        $parent_id = abs($data->parent_id);
        if ($parent_id == $target) {
            return [
                'status' => false,
                'data'   => $source,
            ];
        } else {
            if (!$parent_id) {
                return [
                    'status' => true,
                ];
            }
            $num++;
            return $this->relationCheck($target, $parent_id, $num, $max_num);
        }
    }

    /**
     * 用户失去下级记录
     */
    public function loseLog($data, $type)
    {
        $col  = ['parent_id', 'UID', 'type', 'created_time'];
        $row  = [];
        $time = time();
        foreach ($data as $v) {
            array_push($row, [$v['parent_id'], $v['id'], $type, $time]);
        }
        $prefix = Yii::$app->db->tablePrefix;
        $table  = $prefix . 'promoter_lose_log';
        $res    = Yii::$app->db->createCommand()->batchInsert($table, $col, $row)->execute();
        return $res;
    }

    /**
     * 设置会员等级
     * $type 1排除触发用户  2包括触发用户  3只检测触发用户
     */
    public function setLevel($uid_list, $type)
    {
        $setting = StoreSetting('promoter_setting');
        $AppID   = Yii::$app->params['AppID'];
        $time = time();

        $level_number = $type === 1 ? $setting['level_number'] : $setting['level_number'] - 1;
        $check_uid    = [];
        if ($type > 1) {
            //目标包含自己时,目标也进入检测
            $check_uid = $uid_list;
        }
        //获取所有需要检测等级的用户ID
        if ($level_number > 0 && $type != 3) {
            $first_p   = User::find()->where(['id' => $uid_list])->andWhere(['<>', 'parent_id', 0])->select('parent_id')->asArray()->all();
            $first_p   = array_unique(array_column($first_p, 'parent_id'));
            $check_uid = array_merge($check_uid, $first_p);
            if ($level_number > 1 && count($first_p)) {
                $second_p  = User::find()->where(['id' => $first_p])->andWhere(['<>', 'parent_id', 0])->select('parent_id')->asArray()->all();
                $second_p  = array_unique(array_column($second_p, 'parent_id'));
                $check_uid = array_merge($check_uid, $second_p);
                if ($level_number > 2 && count($second_p)) {
                    $third_p   = User::find()->where(['id' => $second_p])->andWhere(['<>', 'parent_id', 0])->select('parent_id')->asArray()->all();
                    $third_p   = array_unique(array_column($third_p, 'parent_id'));
                    $check_uid = array_merge($check_uid, $third_p);
                }
            }
        }

        $check_uid = array_unique($check_uid);
        $comQuery  = PromoterCommission::find()
            ->alias('pc')
            ->leftJoin(['po' => PromoterOrder::tableName()], 'pc.order_goods_id = po.order_goods_id')
            ->andWhere(['>=', 'po.status', 0])
            ->groupBy('pc.beneficiary')
            ->select('pc.beneficiary,sum(pc.sales_amount) sales_amount,sum(pc.commission) all_commission_amount');

        $check_list = Promoter::find()
            ->alias('p')
            ->leftJoin(['com' => $comQuery], 'com.beneficiary = p.UID')
            ->where(['and', ['p.UID' => $check_uid], ['p.status' => 2]])
            ->select('p.UID,p.level,p.start_level,com.all_commission_amount,com.sales_amount')
            ->asArray()
            ->all();

        $level_data        = PromoterLevel::find()->where(['AppID' => $AppID, 'is_auto' => 1,'is_deleted'=>0])->select('name,level,first,second,third,update_type,condition')->orderBy(['level' => SORT_DESC])->asArray()->all();
        $level_name = PromoterLevel::find()->where(['AppID' => $AppID,'is_deleted'=>0])->select('name,level')->asArray()->all();
        $level_name = array_column($level_name, null,'level');
        $level_update_data = [];

        $log_col = ['UID', 'old_level', 'old_level_name', 'new_level', 'new_level_name','type', 'created_time'];
        $log_row = [];
        foreach ($level_data as $lv) {
            $level_update_data[$lv['level']] = [];
            $condition                       = to_array($lv['condition']);
            $new_check_list                  = []; //用于储存不符合当前等级的用户,在下一个等级判断使用
            foreach ($check_list as $v) {

                if ($v['start_level'] >= $lv['level']) {
                    if (isset($level_update_data[$v['start_level']])) {
                        array_push($level_update_data[$v['start_level']], $v['UID']);
                        if ($v['start_level'] != $v['level']) {
                            if ($v['start_level'] > $v['level']) {
                                $type = 1;
                            }else{
                                $type = 2;
                            }
                            array_push($log_row, [$v['UID'],$v['level'],$level_name[$v['level']]['name'],$v['start_level'],$level_name[$v['start_level']]['name'],$type,$time]);
                        }
                    } else {
                        $level_update_data[$v['start_level']] = [$v['UID']];
                    }

                } else {
                    $checked_num = 0; //需要满足数
                    $got_num     = 0; //已获得数

                    if ($condition['total_bonus']['checked']) {
                        $checked_num++;
                        if ($v['all_commission_amount'] >= $condition['total_bonus']['num']) {
                            $got_num++;
                        }
                    }

                    if ($condition['total_money']['checked']) {
                        $checked_num++;
                        if ($v['sales_amount'] >= $condition['total_money']['num']) {
                            $got_num++;
                        }
                    }

                    if ($condition['all_children']['checked']) {
                        $checked_num++;
                        if ($lv['update_type'] === 2 || $got_num === 0) {
                            $model = Promoter::findOne(['UID' => $v['UID']]);
                            $all_children = $model->getAllChildren();
                            if ($setting['self_buy'] === 2) {
                                $all_children ++;
                            }
                            if ($all_children >= $condition['all_children']['num']) {
                                $got_num++;
                            }
                        }
                    }

                    $is_ok = false;
                    if ($lv['update_type'] === 1 && $got_num) {
                        $is_ok = true;
                    } elseif ($lv['update_type'] === 2 && $got_num === $checked_num) {
                        $is_ok = true;
                    }

                    if ($is_ok) {
                        array_push($level_update_data[$lv['level']], $v['UID']);
                        if ($lv['level'] != $v['level']) {
                            if ($lv['level'] > $v['level']) {
                                $type = 1;
                            }else{
                                $type = 2;
                            }
                            array_push($log_row, [$v['UID'],$v['level'],$level_name[$v['level']]['name'],$lv['level'],$level_name[$lv['level']]['name'],$type,$time]);
                        }
                    } else {
                        array_push($new_check_list, $v);
                    }
                }

            }

            $check_list = $new_check_list;
        }

        if (!empty($check_list)) {
            $level1_data = [];
            foreach ($check_list as $value) {
                array_push($level1_data, $value['UID']);
                if ($value['level'] != 1) {
                    $type = 2;
                    array_push($log_row, [$value['UID'],$value['level'],$level_name[$value['level']]['name'],1,$level_name[1]['name'],$type,$time]);
                }
            }
            //最后都没有找到符合的,等级归为初始1级
            Promoter::updateAll(['level' => 1], ['UID' => $level1_data]);
        }

        foreach ($level_update_data as $level => $data) {
            if (!empty($data)) {
                Promoter::updateAll(['level' => $level], ['UID' => $data]);
            }
        }

        if (count($log_row)) {
            $prefix = Yii::$app->db->tablePrefix;
            $log_table  = $prefix . 'promoter_level_change_log';
            Yii::$app->db->createCommand()->batchInsert($log_table, $log_col, $log_row)->execute();
        }
    }
}
