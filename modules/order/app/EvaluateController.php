<?php
/**
 * 评价控制器
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace order\app;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class EvaluateController extends BasicController
{
    public $modelClass = 'order\models\OrderEvaluate';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionView()
    {
        return '占位方法';
    }

    public function actionDelete()
    {
        return '占位方法';
    }

    public function actionCreate()
    {
        $order_sn = Yii::$app->request->post('order_sn', false);
        $list     = Yii::$app->request->post('evaluate_list', []);

        if (!$order_sn) {
            Error('缺少订单编号');
        }
        $model = M('order', 'Order')::find()->where(['order_sn' => $order_sn])->one();
        if ($model->status != 203) {
            Error('该订单不可评价');
        }
        if ($model->is_evaluate !== 0) {
            Error('不能重复评价');
        }
        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务
        foreach ($list as $value) {
            $res = $this->add($value);
            if (!$res) {
                $transaction->rollBack(); //事务回滚
                Error('提交失败');
            }
        }

        $model->is_evaluate = 1;
        if ($model->save()) {
            $transaction->commit(); //事务执行
            return true;
        } else {
            $transaction->rollBack(); //事务回滚
            Error('提交失败');
        }
    }

    /**
     * 订单评价
     * @return [type] [description]
     */
    public function add($value)
    {
        // $post        = Yii::$app->request->post();
        $UID         = Yii::$app->user->identity->id;
        $AppID       = Yii::$app->params['AppID'];
        $merchant_id = 1;

        $order_goods_model = M('order', 'OrderGoods')::findOne($value['order_goods_id']);

        if (empty($order_goods_model)) {
            Error('要评价的订单商品不存在');
        }

        $order_goods = $order_goods_model->toArray();
        if ($order_goods['is_evaluate'] === 1) {
            Error('不要重复评价');
        }

        $data = [
            'UID'              => $UID,
            'order_sn'         => $order_goods['order_sn'],
            'goods_id'         => $order_goods['goods_id'],
            'goods_name'       => $order_goods['goods_name'],
            'goods_image'      => $order_goods['goods_image'],
            'show_goods_param' => $order_goods['show_goods_param'],
            'goods_param'      => $order_goods['goods_param'],
            'AppID'            => $AppID,
            'merchant_id'      => $merchant_id,
        ];
        $value['images'] = $value['images'] ?? [];
        $value['images'] = to_json($value['images']);
        $data            = array_merge($value, $data);

        $model = new $this->modelClass;
        $model->setScenario('create');
        $model->setAttributes($data);
        if ($model->validate()) {
            if ($model->save()) {
                $order_goods_model->is_evaluate = 1;
                if ($order_goods_model->save()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function actionTabcount()
    {
        $goods_id = Yii::$app->request->post('goods_id', false);

        if (!$goods_id) {
            Error('商品ID不存在');
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
            'goods_id'    => $goods_id,
        ];

        $where = ['and', $where, ['>', 'status', 0]];

        $data_list = ['all' => 0, 'image' => 0, 'good' => 0, 'general' => 0, 'bad' => 0];
        foreach ($data_list as $key => &$value) {
            $w = null;
            switch ($key) {
                case 'image': //有图
                    $w = ['and', $where, ['and', ['NOT', ['images' => null]], ['NOT', ['images' => '']], ['NOT', ['images' => '[]']]]];
                    break;
                case 'good': //好评
                    $w = ['>=', 'star', 4];
                    break;
                case 'general': //中评
                    $w = ['star' => 3];
                    break;
                case 'bad': //差评
                    $w = ['<', 'star', 3];
                    break;

                default: //默认获取全部

                    break;
            }
            if ($w) {
                $w = ['and', $where, $w];
            } else {
                $w = $where;
            }

            $value = $this->modelClass::find()->where($w)->count();
        }

        return $data_list;
    }

    /**
     * 用户端获取评论列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $this->module->trigger('check_evaluate');
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $goods_id = Yii::$app->request->get('goods_id', false);

        if (!$goods_id) {
            Error('商品ID不存在');
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
            'goods_id'    => $goods_id,
        ];

        $where = ['and', $where, ['>', 'status', 0]];

        //评论等级
        $tab_key = Yii::$app->request->get('tab_key', false);
        switch ($tab_key) {
            case 'image': //有图
                $where = ['and', $where, ['and', ['NOT', ['images' => null]], ['NOT', ['images' => '']], ['NOT', ['images' => '[]']]]];
                break;
            case 'good': //好评
                $where = ['and', $where, ['>=', 'star', 4]];
                break;
            case 'general': //中评
                $where = ['and', $where, ['star' => 3]];
                break;
            case 'bad': //差评
                $where = ['and', $where, ['<', 'star', 3]];
                break;

            default: //默认获取全部

                break;
        }

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()
                    ->with([
                        'user',
                    ])
                    ->where($where)
                    ->groupBy(['id'])
                    ->orderBy(['status' => SORT_DESC, 'created_time' => SORT_DESC])
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['images'] = to_array($value['images']);
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

}
