<?php

namespace plugins\evaluate\api;

use basics\api\BasicsController as BasicsModules;
use gallery\models\Gallery;
use order\models\OrderEvaluate;
use plugins\evaluate\models\collect\CollectFactory;
use plugins\evaluate\models\EvaluateGoods;
use plugins\evaluate\models\EvaluateRepository;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class EvaluateController extends BasicsModules
{
    public function actionCreate()
    {
        //获取操作
        $behavior = \Yii::$app->request->get('behavior');
        switch ($behavior) {
            case 'repository':
                return $this->repositorySpider();
                break;
            case 'api':
                return $this->apiSpider();
                break;
            default:
                Error('无此操作');
                break;
        }

    }

    /**
     * 版本库抓取
     * @return bool
     */
    private function repositorySpider()
    {
        $transaction = \Yii::$app->db->beginTransaction(); //启动数据库事务
        $id = \Yii::$app->request->post('id');
        /**@var EvaluateGoods $goods */
        $goods = EvaluateGoods::findOne($id);
        if (!$goods) {
            Error('请选择商品');
        }
        $repositoryId = \Yii::$app->request->post('repository_id');
        if (!$repositoryId) {
            Error('请选择评价库');
        }
        /**@var EvaluateRepository $repository */
        $repository = EvaluateRepository::find()->with(['evaluate' => function ($query) {
            $query->andWhere(['is_deleted' => 0]);
        }])->where(['id' => $repositoryId, 'is_deleted' => 0])->limit(1)->one();
        if (!$repository) {
            Error('评价库不存在');
        }
        $evaluateCount = count($repository->evaluate);
        if ($evaluateCount <= 0) {
            Error('评论库中没有评论');
        }
        $num = \Yii::$app->request->post('num');
        $count = $evaluateCount > $num ? $num : $evaluateCount;
        if (!$num) {
            Error('请输入评论数');
        }
        $begin = \Yii::$app->request->post('begin');
        $end = \Yii::$app->request->post('end');
        if (!$begin || !$end) {
            Error('请完善评论时间');
        }
        if ($begin >= $end) {
            Error('开始时间必须小于结束时间');
        }
        if ($end > time()) {
            Error('评论时间必须小于当前时间');
        }
        $status = \Yii::$app->request->post('status', 1);
        $groupId = \Yii::$app->request->post('gallery_group_id');
        $gallery = Gallery::find()->where(['group_id' => $groupId, 'type' => 1, 'is_deleted' => 0])->select(['url'])->column();
        if (!$gallery) {
            Error('此素材分组下无图片，请重新选择');
        }
        $gallery = str2url($gallery);
        $param = \Yii::$app->request->post('show_goods_param');
        if (!$param) {
            Error('请选择规格');
        }
        $evaluates = $repository->evaluate;
        $this->shuffleAssoc($evaluates);
        $nicknames = json_decode(file_get_contents(__DIR__ . '/../models/nicknames.json'), true);
        $evaluateList = [];
        for ($i = 1; $i <= $count; $i++) {
            $evaluate = array_shift($evaluates);
            $newItem = [];
            $newItem['UID'] = 0;
            $newItem['order_sn'] = 0;
            $newItem['goods_name'] = $goods->goods->name;
            $newItem['goods_image'] = (to_array($goods->goods->slideshow))[0];
            $newItem['goods_id'] = $goods->goods->id;
            $newItem['star'] = $evaluate->star;
            $newItem['content'] = $evaluate->content;
            $newItem['images'] = $evaluate->images;
            $newItem['AppID'] = \Yii::$app->params['AppID'];
            $newItem['merchant_id'] = 1;
            $newItem['status'] = $status;
            $newItem['show_goods_param'] = $param;
            $newItem['created_time'] = mt_rand($begin, $end);
            $newItem['ai_avatar'] = $gallery[mt_rand(0, count($gallery) - 1)];
            $newItem['ai_nickname'] = $nicknames[mt_rand(0, count($nicknames) - 1)];
            $newItem['ai_type'] = 1;
            $evaluateList[] = $newItem;
        }
        try {
            \Yii::$app->db->createCommand()->batchInsert(
                OrderEvaluate::tableName(),
                ['UID', 'order_sn', 'goods_name', 'goods_image', 'goods_id', 'star', 'content', 'images',
                    'AppID', 'merchant_id', 'status', 'show_goods_param', 'created_time', 'ai_avatar', 'ai_nickname', 'ai_type'],
                $evaluateList
            )->execute();
            $transaction->commit(); //事务执行
        } catch (\Exception $exception) {
            $transaction->rollBack();
            Error($exception->getMessage());
        }
        return count($evaluateList);
    }

    private function apiSpider()
    {
        $id = \Yii::$app->request->post('id');
        /**@var EvaluateGoods $goods */
        $goods = EvaluateGoods::findOne($id);
        if (!$goods) {
            Error('请选择商品');
        }
        //$link = 'https://item.jd.com/100011762575.html';
        $link = \Yii::$app->request->post('link');
        if (empty($link)) {
            Error('请输入商品链接');
        }
        if (substr_count($link, 'https://') > 1 || substr_count($link, 'http://') > 1) {
            Error('仅支持采集一条链接，请删减');
        }
        $num = \Yii::$app->request->post('num');
        if (!$num) {
            Error('请输入评论数');
        }
        $begin = \Yii::$app->request->post('begin');
        $end = \Yii::$app->request->post('end');
        if (!$begin || !$end) {
            Error('请完善评论时间');
        }
        if ($begin >= $end) {
            Error('开始时间必须小于结束时间');
        }
        $param = \Yii::$app->request->post('show_goods_param');
        if (!$param) {
            Error('请选择规格');
        }
        $groupId = \Yii::$app->request->post('gallery_group_id');
        $gallery = Gallery::find()->where(['group_id' => $groupId, 'type' => 1, 'is_deleted' => 0])->select(['url'])->column();
        if (!$gallery) {
            Error('此素材分组下无图片，请重新选择');
        }
        $gallery = str2url($gallery);
        $params = [
            'num' => $num,
            'begin' => $begin,
            'end' => $end,
            'status' => \Yii::$app->request->post('status', 1),
            'egoods' => $goods->goods,
            'show_goods_param' => $param,
            'type' => \Yii::$app->request->post('type', 1),
            'gallery' => $gallery
        ];
        return CollectFactory::create($link, $params);
    }

    /**
     * 打乱二维数组
     * @param $array
     * @return bool
     */
    private function shuffleAssoc(&$array)
    {
        $keys = array_keys($array);
        shuffle($keys);
        $new = [];
        foreach ($keys as $key) {
            $new[$key] = $array[$key];
        }
        $array = $new;
        return true;
    }

    public function actionIndex()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 10;
        $status = \Yii::$app->request->get('status', false);
        $type = \Yii::$app->request->get('type', false);
        $goodsId = \Yii::$app->request->get('goods_id', false);
        $query = OrderEvaluate::find()->alias('eg')->where(['AppID' => \Yii::$app->params['AppID'], 'UID' => 0, 'is_deleted' => 0]);
        if ($goodsId) {
            $query->andWhere(['goods_id' => $goodsId]);
        }
        switch ($status) {
            case 'top':
                $query->andWhere(['status' => 2]);
                break;
            case 'hidden':
                $query->andWhere(['status' => 0]);
                break;
            case 'display':
                $query->andWhere(['status' => 1]);
                break;
            default:
                break;
        }
        switch ($type) {
            case 'api': //api抓取
                $query->andWhere(['ai_type' => 2]);
                break;
            case 'repository': //评论库抓取
                $query->andWhere(['ai_type' => 1]);
                break;
            default:
                break;
        }
        $data = new ActiveDataProvider(
            [
                'query' => $query->orderBy(['created_time' => SORT_DESC, 'id' => SORT_DESC]),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $newList = [];
        $list = $data->getModels();
        if ($list) {
            foreach ($list as $item) {
                $newItem = ArrayHelper::toArray($item);
                $newItem['images'] = to_array($newItem['images']);
                unset($newItem['is_deleted']);
                unset($newItem['deleted_time']);
                $newList[] = $newItem;
            }
        }
        $newList = str2url($newList);
        $data->setModels($newList);
        return $data;
    }

    public function actionUpdate()
    {
        $transaction = \Yii::$app->db->beginTransaction(); //启动数据库事务
        $status = \Yii::$app->request->post('status');
        if ($status === null) {
            Error('请选择操作');
        }
        $ids = \Yii::$app->request->get('ids', false);
        if (!$ids) {
            Error('ID缺失');
        }
        $idList = explode(',', $ids);
        try {
            OrderEvaluate::updateAll(['status' => $status], ['id' => $idList]);
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
            Error($exception->getMessage());
        }
        return true;
    }

    public function actionDelete()
    {
        $ids = \Yii::$app->request->get('ids', false);
        if (!$ids) {
            Error('ID缺失');
        }
        $idList = explode(',', $ids);
        try {
            OrderEvaluate::updateAll(['is_deleted' => 1], ['id' => $idList]);
        } catch (\Exception $exception) {
            Error($exception->getMessage());
        }
        return true;
    }
}
