<?php

namespace promoter\api;

use framework\common\BasicController;
use goods\models\Goods;
use Yii;

class GoodsController extends BasicController
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
        return 1111;
    }

    public function actionUpdate()
    {
        $post = Yii::$app->request->post();
        $id   = Yii::$app->request->get('id', false);
        $type = Yii::$app->request->get('type', false);
        if ($type == 'all') {
            $tab_key = Yii::$app->request->get('tab_key', 'all');
            switch ($tab_key) {
                case 'onsale': //上架中
                    $where = ['is_sale' => 1, 'is_recycle' => 0, 'status' => 0];
                    break;
                case 'nosale': //下架中
                    $where = ['is_sale' => 0, 'is_recycle' => 0, 'status' => 0];
                    break;
                case 'soldout': //售罄
                    $where = ['and', ['is_recycle' => 0, 'status' => 0], ['<=', 'stocks', 0]];
                    break;
                default: //默认获取全部
                    $where = ['is_recycle' => 0, 'status' => 0];
                    break;
            }
        } else {

            if (!$id) {
                Error('ID缺失');
            }
            $id = explode(',', $id);

            $where = ['id' => $id];
        }

        if (isset($post['is_promoter'])) {
            $data = ['is_promoter' => $post['is_promoter']];
        } else {
            Error('参数缺失');
        }

        $id_list = [];
        if ($post['is_promoter'] === 1) {
            $goods_list = Goods::find()->where($where)->select('id,max_price,max_profits')->with([
                'goodsdata' => function ($q) {
                    $q->select('id,goods_id,price,cost_price');
                },
            ])->asArray()->all();
            $count_rules = StoreSetting('commission_setting', 'count_rules');
            foreach ($goods_list as $value) {

                if (!$value['max_price']) {
                    $max_price   = 0;
                    $max_profits = 0;
                    $check_cost  = true;
                    foreach ($value['goodsdata'] as $v) {
                        if ($v['cost_price'] === null) {
                            $check_cost = false;
                        }
                        if ($check_cost && ($v['price'] - $v['cost_price']) > $max_profits) {
                            $max_profits = ($v['price'] - $v['cost_price']);
                        }
                        if ($v['price'] > $max_price) {
                            $max_price = $v['price'];
                        }
                    }
                    if (!$check_cost) {
                        $max_profits = null;
                    }
                    $res = Goods::updateAll(['max_price' => $max_price, 'max_profits' => $max_profits], ['id' => $value['id']]);
                    if (!$res) {
                        Error('系统繁忙');
                    }
                    $value['max_price'] = $max_price;
                    $value['max_profits'] = $max_profits;
                }
                if ($count_rules === 2 && $value['max_profits'] === null) {
                    Error('存在未设置成本价的商品');
                }
                array_push($id_list, $value['id']);

            }
        } else {
            $goods_list = Goods::find()->where($where)->select('id')->asArray()->all();
            $id_list    = array_column($goods_list, 'id');
        }

        $t      = Yii::$app->db->beginTransaction();
        $result = Goods::updateAll($data, ['id' => $id_list]);

        //分销商品表没有记录的商品进行插入
        if ($data['is_promoter']) {
            $p_g_list = M('promoter', 'PromoterGoods')::find()->where(['goods_id' => $id_list])->select('goods_id')->asArray()->all();
            $p_g_list = array_column($p_g_list, 'goods_id');
            $row      = [];
            $time     = time();
            foreach ($id_list as $v) {
                if (!in_array($v, $p_g_list)) {
                    array_push($row, [$v, 0, $time]);
                }
            }
            if (count($row)) {
                $col        = ['goods_id', 'sales', 'created_time'];
                $prefix     = Yii::$app->db->tablePrefix;
                $table_name = $prefix . 'promoter_goods';
                $batch_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
                if (!$batch_res) {
                    $t->rollBack();
                    Error('操作失败');
                }
            }
        }

        if ($result || $result === 0) {
            $t->commit();
            return $result;
        } else {
            $t->rollBack();
            Error('操作失败');
        }
    }
}
