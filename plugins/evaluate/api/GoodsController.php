<?php

namespace plugins\evaluate\api;

use basics\api\BasicsController as BasicsModules;
use goods\models\Goods;
use goods\models\GoodsParam;
use order\models\OrderEvaluate;
use plugins\evaluate\models\EvaluateGoods;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class GoodsController extends BasicsModules
{
    public function actionIndex()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 10;
        $keyword = \Yii::$app->request->get('keyword', false);
        $status = \Yii::$app->request->get('status', false);
        $behavior = \Yii::$app->request->get('behavior', '');
        if ($behavior == 'goods_list') {
            return EvaluateGoods::find()->select('goods_id')->where(['is_deleted' => 0])->column();
        } elseif ($behavior == 'params') {
            $goodsId =  \Yii::$app->request->get('goods_id', false);
            $param = GoodsParam::find()->where(['goods_id' => $goodsId, 'is_deleted' => 0])->asArray()->one();
            if (!$param) {
                Error('规格不存在');
            }
            $param['param_data'] = to_array($param['param_data']);
            return $param;
        }
        $query = EvaluateGoods::find()->alias('eg')->with(['repositoryEvaluates' => function ($query) {
            $query->select(['goods_id']);
        }, 'apiEvaluates' => function ($query) {
            $query->select(['goods_id']);
        }])->joinWith(['goods g' => function ($query) use ($status, $keyword){
            switch ($status) {
                case 'onsale': //上架中
                    $query->andWhere(['g.is_sale' => 1, 'g.is_recycle' => 0, 'g.status' => 0]);
                    break;
                case 'nosale': //下架中
                    $query->andWhere(['g.is_sale' => 0, 'g.is_recycle' => 0, 'g.status' => 0]);
                    break;
                case 'soldout': //售罄
                    $query->andWhere(['and', ['g.is_recycle' => 0, 'g.status' => 0], ['<=', 'g.stocks', 0]]);
                    break;
                default:
                    break;
            }
            if ($keyword !== false) {
                $query->andWhere(['like', 'g.name', $keyword]);
            }
        }])->where(['eg.is_deleted' => 0]);
        $sort = \Yii::$app->request->get('sort', 1);
        if ($sort == 1) {
            $query->orderBy('eg.created_time desc');
        } elseif ($sort == 2) {
            $query->orderBy('eg.created_time asc');
        }

        $data = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $newList = [];
        $list = $data->getModels();
        $goodsListArray = array_column($list, 'goods_id');
        $merchant_id = 1;
        $AppID       = \Yii::$app->params['AppID'];
        $where       = [
            'AppID'       => $AppID,
            'merchant_id' => $merchant_id,
            'is_deleted'  => 0,
        ];
        $goodsList = OrderEvaluate::find()->select(["COALESCE(count(goods_id), 0) as num", "goods_id"])
            ->where($where)->andWhere(['goods_id' => $goodsListArray])->groupBy('goods_id')->asArray()->all();
        $goodsList = array_column($goodsList, null, 'goods_id');
        if ($list) {
            foreach ($list as $item) {
                /**@var Goods $goods*/
                $goods = $item->goods;
                $newItem = ArrayHelper::toArray($item);
                $newItem['name'] = $goods->name;
                $newItem['slideshow'] = to_array($goods->slideshow);
                $newItem['stocks'] = $goods->stocks;
                $newItem['is_sale'] = $goods->is_sale;
                /**@var EvaluateGoods $item*/
                $newItem['repository_num'] = count($item->repositoryEvaluates);
                $newItem['api_num'] = count($item->apiEvaluates);
                $newItem['all_evaluate_count'] = $goodsList[$item['goods_id']]['num'] ?? 0;
                unset($newItem['is_deleted']);
                unset($newItem['deleted_time']);
                $newList[] = $newItem;
            }
        }
        $data->setModels($newList);
        return $data;
    }

    public function actionCreate()
    {
        $post = \Yii::$app->request->post('form');
        if (!is_array($post)) {
            Error('参数格式不正确');
        }
        $ids = array_column($post, 'goods_id');
        $exists = EvaluateGoods::find()->where(['goods_id' => $ids, 'is_deleted' => 0])->exists();
        if ($exists) {
            Error('存在重复商品,请检查');
        }
        $goodsList = [];
        $now = time();
        foreach ($post as $item) {
            $newItem = [];
            $newItem['goods_id'] = $item['goods_id'];
            $newItem['created_time'] = $now;
            $goodsList[] = $newItem;
        }
        \Yii::$app->db->createCommand()->batchInsert(
            EvaluateGoods::tableName(),
            ['goods_id', 'created_time'],
            $goodsList
        )->execute();
        return true;
    }

    public function actionUpdate()
    {

    }
}
