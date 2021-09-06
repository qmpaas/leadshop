<?php

namespace promoter\app;

use framework\common\BasicController;
use promoter\models\PromoterMaterial;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;


class MaterialController extends BasicController
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

    /**
     * 素材列表
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $get = \Yii::$app->request->get();
        $query = PromoterMaterial::find()
            ->alias('p')
            ->where(['p.AppID' => \Yii::$app->params['AppID'], 'p.is_deleted' => 0])
            ->joinWith(['goods g']);
        $type = $get['type'] ?? false;
        if ($type) {
            $query->andWhere(['p.type' => $type]);
        }
        $content = $get['content'] ?? false;
        if ($content) {
            $query->andWhere([
                 'or',
                 ['like', 'p.content', $content],
                 ['like', 'g.name', $content],
             ]);
        }
        $data = new ActiveDataProvider(
            [
                'query' => $query->orderBy(['p.created_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );
        $list = $data->getModels();
        //将所有返回内容中的本地地址代替字符串替换为域名
        $newList = str2url($list);
        foreach ($newList as &$item) {
            $item['pic_list'] = to_array($item['pic_list']);
            $item['video_list'] = to_array($item['video_list']);
        }
        $data->setModels($newList);
        return $data;
    }

    /**
     * 素材详情
     * @return array|array[]|object|object[]|string|string[]
     */
    public function actionView()
    {
        $id = \Yii::$app->request->get('id', 0);
        $material = PromoterMaterial::findOne(['id' => $id, 'is_deleted' => 0]);
        if (!$material) {
            Error('该素材不存在');
        }
        $material = ArrayHelper::toArray($material);
        $material['pic_list'] = to_array($material['pic_list']);
        return $material;
    }

    /**
     * 分享素材
     * @return array|array[]|object|object[]|string|string[]
     */
    public function actionCreate()
    {
        $id = \Yii::$app->request->get('id', 0);
        $material = PromoterMaterial::findOne(['id' => $id, 'is_deleted' => 0]);
        if (!$material) {
            Error('该素材不存在');
        }
        $material->share_count++;
        $material->save();
        $material = ArrayHelper::toArray($material);
        $material['pic_list'] = to_array($material['pic_list']);
        return $material;
    }
}
