<?php

namespace collect\api;

use collect\models\CollectLog;
use framework\common\BasicController;
use goods\models\GoodsGroup;
use yii\data\ActiveDataProvider;

class IndexController extends BasicController
{
    public $modelClass = 'collect\models\CollectLog';

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
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $get = \Yii::$app->request->get();
        $query = CollectLog::find()
            ->select(['cl.type', 'cl.name', 'cl.cover', 'cl.link', 'cl.status', 'cl.goods_id', 'cl.created_time', 'cl.id', 'cl.group_text', 'cl.group'])
            ->with(['goods'])
            ->alias('cl');
        $name = $get['name'] ?? false;
        if ($name !== false) {
            $query->andWhere(['like', 'cl.name', $name]);
        }
        $status = $get['status'] ?? false;
        if ($status !== false) {
            if ($status == 1) {
                $query->andWhere(['cl.status' => 1]);
            } else {
                $query->andWhere(['!=', 'cl.status', 1]);
            }
        }
        $begin = $get['begin_time'] ?? false;
        $end   = $get['end_time'] ?? false;
        if ($begin) {
            $query->andWhere(['>=', 'cl.created_time', $begin]);
        }
        if ($end) {
            $query->andWhere(['<=', 'cl.created_time', $end]);
        }
        $type = $get['type'] ?? false;
        if ($type) {
            $query->andWhere(['type' => $type]);
        }
        $data = new ActiveDataProvider(
            [
                'query'      => $query->andWhere(['is_deleted' => 0])->orderBy(['cl.created_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );
        $list = $data->getModels();
        $list = str2url($list);
        foreach ($list as &$item) {
            $item['group_text'] = json_decode($item['group_text'], true);
        }
        unset($item);
        $data->setModels($list);
        return $data;
    }
}