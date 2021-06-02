<?php
/**
 * 设置管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace cart\app;

use framework\common\BasicController;
use Yii;

class IndexController extends BasicController
{
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 创建购物车
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $post        = Yii::$app->request->post();
        $post['UID'] = Yii::$app->user->identity->id;
        $check       = M()::find()->where(['goods_id' => $post['goods_id'], 'goods_param' => $post['goods_param'], 'UID' => $post['UID']])->one();

        $info = M('goods', 'Goods')::find()->where(['id' => $post['goods_id']])->select('id,name,slideshow,limit_buy_status,limit_buy_value')->with('param')->asArray()->one();
        if ($info['limit_buy_status'] === 1) {
            $owned_number = M()::find()->where(['goods_id' => $post['goods_id'], 'UID' => $post['UID']])->SUM('goods_number');
            if (($owned_number + $post['goods_number']) > $info['limit_buy_value']) {
                Error('添加数超过限购数量');
            }
        }

        if ($check) {
            //存在则数量累加
            $check->goods_number += $post['goods_number'];
            return $check->save();
        } else {

            $param_data       = to_array($info['param']['param_data']);
            $slideshow        = to_array($info['slideshow']); //轮播图
            $first_param_info = array_column($param_data[0]['value'], null, 'value'); //第一个规格信息
            $first_param      = explode('_', $post['goods_param'])[0]; //第一个规格
            $goods_image      = $param_data[0]['image_status'] && $first_param_info[$first_param]['image'] ? $first_param_info[$first_param]['image'] : $slideshow[0]; //存在规格图片则使用,不存在使用第一张轮播图

            $show_goods_param = '';
            $goods_param      = explode('_', $post['goods_param']);
            foreach ($param_data as $key => $param_info) {
                if ($param_info['name']) {
                    $show_goods_param .= $param_info['name'] . '：' . $goods_param[$key] . ' ';
                } else {
                    $show_goods_param .= $goods_param[$key] . ' ';
                }

            }
            $post['goods_name']       = $info['name'];
            $post['goods_image']      = $goods_image;
            $post['show_goods_param'] = $show_goods_param;

            $model = M('cart', 'Cart', true);
            $model->setScenario('create');
            $model->setAttributes($post);
            if ($model->validate()) {
                $res = $model->save();
                if ($res) {
                    return true;
                } else {
                    Error('保存失败');
                }

            }
            return $model;
        }

    }

    /**
     * 购物车列表
     * @return [type] [description]
     */
    public function actionIndex()
    {

        $UID = Yii::$app->user->identity->id;

        $data        = M()::find()->where(['UID' => $UID])->select('id,goods_id,goods_name,goods_image,goods_param,show_goods_param,goods_number')->with('goodsinfo')->asArray()->all();
        $return_data = [
            'normal'  => [], //正常的
            'failure' => [], //失效的
        ];
        foreach ($data as $v) {
            $price            = 0;
            $goods_sn         = '';
            $stocks           = 0;
            $failure_reason   = ''; //param规格不存在  is_sale下架  delete商品不存在  stocks库存不足
            $min_number       = 1;
            $limit_buy_status = 0;
            $limit_buy_value  = null;
            if (empty($v['goodsinfo'])) {
                $failure_reason = 'delete';
            } else {
                $info       = $v['goodsinfo'];
                $goods_data = array_column($info['param']['goods_data'], null, 'param_value');

                if ($v['goodsinfo']['is_sale'] === 0 || !isset($goods_data[$v['goods_param']]) || $v['goods_number'] > $goods_data[$v['goods_param']]['stocks']) {
                    if ($v['goodsinfo']['is_sale'] === 0) {
                        $failure_reason = 'is_sale';
                    } elseif (!isset($goods_data[$v['goods_param']])) {
                        $failure_reason = 'param';
                    } else {
                        $failure_reason = 'stocks';
                    }
                } else {
                    $price            = $goods_data[$v['goods_param']]['price'];
                    $goods_sn         = $goods_data[$v['goods_param']]['goods_sn'];
                    $stocks           = $goods_data[$v['goods_param']]['stocks'];
                    $min_number       = $info['min_number'];
                    $limit_buy_status = $info['limit_buy_status'];
                    $limit_buy_value  = $info['limit_buy_value'];
                }
            }

            $v['failure_reason']   = $failure_reason;
            $v['price']            = $price;
            $v['goods_sn']         = $goods_sn;
            $v['stocks']           = $stocks;
            $v['min_number']       = $min_number;
            $v['limit_buy_status'] = $limit_buy_status;
            $v['limit_buy_value']  = $limit_buy_value;
            $v['is_select']        = 0;
            $v['show']             = false;
            unset($v['goodsinfo']);

            if ($v['failure_reason']) {
                array_push($return_data['failure'], $v);
            } else {
                array_push($return_data['normal'], $v);
            }

        }
        return str2url($return_data);
    }

    /**
     * 重写修改方法
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'save':
                return $this->save();
                break;
            default:
                return $this->update();
                break;
        }
    }

    /**
     * 购物车单个修改
     * @return [type] [description]
     */
    public function update()
    {
        $id   = Yii::$app->request->get('id', 0);
        $post = Yii::$app->request->post();

        $model = M()::findOne($id);
        if (empty($model)) {
            Error('数据不存在');
        }

        $check = M()::find()->where(['and', ['<>', 'id', $id], ['goods_id' => $model->goods_id, 'goods_param' => $post['goods_param'], 'UID' => $model->UID]])->one();

        $info = M('goods', 'Goods')::find()->where(['id' => $model->goods_id])->select('id,name,slideshow,limit_buy_status,limit_buy_value,min_number')->with('param')->asArray()->one();
        if ($info['limit_buy_status'] === 1) {
            $owned_number = M()::find()->where(['and', ['goods_id' => $model->goods_id, 'UID' => $model->UID], ['<>', 'id', $id]])->SUM('goods_number');
            if (($owned_number + $post['goods_number']) > $info['limit_buy_value']) {
                Error('添加数超过限购数量');
            }
        }

        if ($check) {
            //存在则数量累加到之前存在的,并且删除本条数据
            $check->goods_number += $post['goods_number'];
            $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
            $res1        = $check->save();
            $res2        = $model->delete();
            if ($res1 && $res2) {
                $transaction->commit(); //事务执行
                return $check->toArray();
            } else {
                $transaction->rollBack(); //事务回滚
                Error('操作失败');
            }

        } else {
            if (isset($post['goods_param'])) {
                $param_data       = to_array($info['param']['param_data']);
                $slideshow        = to_array($info['slideshow']); //轮播图
                $first_param_info = array_column($param_data[0]['value'], null, 'value'); //第一个规格信息
                $first_param      = explode('_', $post['goods_param'])[0]; //第一个规格
                $goods_image      = $param_data[0]['image_status'] && $first_param_info[$first_param]['image'] ? $first_param_info[$first_param]['image'] : $slideshow[0]; //存在规格图片则使用,不存在使用第一张轮播图

                $show_goods_param = '';
                $goods_param      = explode('_', $post['goods_param']);
                foreach ($param_data as $key => $param_info) {
                    if ($param_info['name']) {
                        $show_goods_param .= $param_info['name'] . '：' . $goods_param[$key] . ' ';
                    } else {
                        $show_goods_param .= $goods_param[$key] . ' ';
                    }

                }
                $post['goods_name']       = $info['name'];
                $post['goods_image']      = $goods_image;
                $post['show_goods_param'] = $show_goods_param;
            }
            $model->setScenario('update');
            $model->setAttributes($post);
            if ($model->validate()) {
                if ($model->save()) {
                    $result     = str2url($model->toArray());
                    $goods_data = array_column($info['param']['goods_data'], null, 'param_value');

                    $result['price']            = $goods_data[$result['goods_param']]['price'];
                    $result['goods_sn']         = $goods_data[$result['goods_param']]['goods_sn'];
                    $result['stocks']          = $goods_data[$result['goods_param']]['stocks'];
                    $result['min_number']       = $info['min_number'];
                    $result['limit_buy_status'] = $info['limit_buy_status'];
                    $result['limit_buy_value']  = $info['limit_buy_value'];

                    return $result;
                } else {
                    Error('保存失败');
                }

            }
            return $model;
        }

    }

    /**
     * 购物车全部保存
     * @return [type] [description]
     */
    public function save()
    {
        $post        = Yii::$app->request->post('cart_list');
        $id          = array_column($post, 'id');
        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        M('cart', 'Cart')::deleteAll(['id' => $id]); //批量插入前先删除之前数据
        $row = [];
        $col = [];
        foreach ($post as $v) {
            $value = [
                "goods_id"         => $v['goods_id'],
                "UID"              => $v['UID'],
                "goods_name"       => $v['goods_name'],
                "goods_image"      => $v['goods_image'],
                "goods_param"      => $v['goods_param'],
                "show_goods_param" => $v['show_goods_param'],
                "goods_number"     => $v['goods_number'],
                "created_time"     => $v['created_time'],
                "updated_time"     => $v['updated_time'],
                "deleted_time"     => $v['deleted_time'],
                "is_deleted"       => $v['is_deleted'],
            ];
            array_push($row, array_values($value));
            if (empty($col)) {
                $col = array_keys($value);
            }
        }
        $prefix     = Yii::$app->db->tablePrefix;
        $table_name = $prefix . 'cart';
        $res        = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
        if ($res) {
            $transaction->commit(); //事务执行
            return true;
        } else {
            $transaction->rollBack(); //事务回滚
            Error('操作失败');
        }

    }

    /**
     * 下单清空购物车
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    public static function cartClear($event)
    {
        //判断是否存在购物车ID,存在则清除对应购物车商品
        if (isset($event->order_goods[0]['id'])) {
            $id = array_column($event->order_goods, 'id');
            M()::deleteAll(['id' => $id]);
        }
    }

    /**
     * 删除购物车
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id = explode(',', $id);

        return M()::deleteAll(['id' => $id]);
    }

}
