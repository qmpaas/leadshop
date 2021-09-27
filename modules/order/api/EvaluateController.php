<?php
/**
 * 评价控制器
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace order\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class EvaluateController extends BasicController
{
    public $modelClass = 'order\models\OrderEvaluate';

    public function actions()
    {
        $this->module->trigger('check_evaluate');
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
        $keyword  = Yii::$app->request->post('keyword', []);

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'evaluate.is_deleted'  => 0,
            'evaluate.merchant_id' => $merchant_id,
            'evaluate.AppID'       => $AppID,
        ];

        $search_key = $keyword['search_key'] ?? false;
        $search     = $keyword['search'] ?? '';

        //买家昵称
        if ($search_key == 'nickname' && $search) {
            $where = ['and', $where, ['like', 'user.nickname', $search]];
        }

        //商品名称
        if ($search_key == 'goods_name' && $search) {
            $where = ['and', $where, ['like', 'evaluate.goods_name', $search]];
        }

        //评论内容
        if ($search_key == 'content' && $search) {
            $where = ['and', $where, ['like', 'evaluate.content', $search]];
        }

        //评论等级
        $level = $keyword['level'] ?? false;
        if ($level) {
            if ($level == 'good') {
                $where = ['and', $where, ['>=', 'evaluate.star', 4]];
            } elseif ($level == 'general') {
                $where = ['and', $where, ['evaluate.star' => 3]];
            } else {
                $where = ['and', $where, ['<', 'evaluate.star', 3]];
            }

        }

        //状态
        $status = $keyword['status'] ?? false;
        if ($status > 0 || $status === 0) {
            $where = ['and', $where, ['evaluate.status' => $status]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'evaluate.created_time', $time_start]];
        }
        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'evaluate.created_time', $time_end]];
        }

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()
                    ->alias('evaluate')
                    ->joinWith([
                        'user as user',
                    ])
                    ->where($where)
                    ->groupBy(['evaluate.id'])
                    ->orderBy(['created_time' => SORT_DESC])
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            if (empty($value['user'])) {
                $value['user']['nickname'] = $value['ai_nickname'];
                $value['user']['avatar'] = $value['ai_avatar'];
            }
            $value['images'] = to_array($value['images']);
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

    /**
     * 重写修改方法
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        $post = Yii::$app->request->post();
        $id   = Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id = explode(',', $id);

        $where = ['id' => $id];

        $data = [];

        if (isset($post['status'])) {
            $data['status'] = $post['status'];
        }

        if (N('reply')) {
            $data['reply'] = $post['reply'];
        }

        $result = $this->modelClass::updateAll($data, $where);

        if ($result) {
            return $result;
        } else {
            Error('操作失败');
        }
    }

    public function actionDelete()
    {
        $post = Yii::$app->request->post();
        $id   = Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $id = explode(',', $id);

        $where = ['id' => $id];

        $data = [
            'is_deleted'   => 1,
            'deleted_time' => time(),
        ];

        $result = $this->modelClass::updateAll($data, $where);

        if ($result) {
            return $result;
        } else {
            Error('操作失败');
        }
    }

    public static function checkEvaluate()
    {
        $AppID      = Yii::$app->params['AppID'];
        $time       = time();
        $order_list = M('order', 'Order')::find()->where(['and', ['AppID' => $AppID, 'is_evaluate' => 0], ['<=', 'evaluate_time', $time]])->select('order_sn,UID,merchant_id,evaluate_time')->with('goods')->asArray()->all();
        $row        = [];
        $col        = [];
        foreach ($order_list as $order) {
            foreach ($order['goods'] as $order_goods) {
                $data = [
                    'star'             => 5,
                    'content'          => '系统默认好评',
                    'images'           => '[]',
                    'UID'              => $order['UID'],
                    'order_sn'         => $order_goods['order_sn'],
                    'goods_id'         => $order_goods['goods_id'],
                    'goods_name'       => $order_goods['goods_name'],
                    'goods_image'      => $order_goods['goods_image'],
                    'show_goods_param' => $order_goods['show_goods_param'],
                    'goods_param'      => $order_goods['goods_param'],
                    'AppID'            => $AppID,
                    'merchant_id'      => $order['merchant_id'],
                    'created_time'     => $order['evaluate_time'],
                ];
                array_push($row, array_values($data));
                if (empty($col)) {
                    $col = array_keys($data);
                }
            }
        }

        $prefix     = Yii::$app->db->tablePrefix;
        $table_name = $prefix . 'order_evaluate';
        $batch_res  = Yii::$app->db->createCommand()->batchInsert($table_name, $col, $row)->execute();
        M('order', 'Order')::updateAll(['is_evaluate' => 1], ['and', ['AppID' => $AppID, 'is_evaluate' => 0], ['<=', 'evaluate_time', $time]]);
    }

}
