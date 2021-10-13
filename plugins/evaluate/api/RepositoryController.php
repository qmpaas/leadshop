<?php

namespace plugins\evaluate\api;

use basics\api\BasicsController as BasicsModules;
use plugins\evaluate\models\Evaluate;
use plugins\evaluate\models\EvaluateRepository;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class RepositoryController
 * @package plugins\evaluate\api
 */
class RepositoryController extends BasicsModules
{
    /**
     * 评论库列表
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 10;
        $repositoryId = \Yii::$app->request->get('repository_id', 0);
        if ($repositoryId) {
            $query = Evaluate::find()->where(['repository_id' => $repositoryId, 'is_deleted' => 0]);
        } else {
            $query = EvaluateRepository::find()->with(['evaluate' => function ($query) {
                $query->andWhere(['is_deleted' => 0]);
            }])->where(['is_deleted' => 0]);
            $name = \Yii::$app->request->get('name', false);
            if ($name) {
                $query->andWhere(['like', 'name', $name]);
            }
            $sort = \Yii::$app->request->get('sort', 1);
            if ($sort == 1) {
                $query->orderBy('created_time desc');
            } elseif ($sort == 2) {
                $query->orderBy('created_time asc');
            }
        }
        $data = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $newList = [];
        $list = $data->getModels();
        if ($list) {
            foreach ($list as $item) {
                $newItem = ArrayHelper::toArray($item);
                if ($item instanceof EvaluateRepository) {
                    $newItem['count'] = count($item->evaluate);
                } else {
                    $newItem['images'] = to_array($item->images);
                }
                unset($newItem['is_deleted']);
                unset($newItem['deleted_time']);
                $newList[] = $newItem;
            }
        }
        $newList = str2url($newList);
        $data->setModels($newList);
        return $data;
    }

    public function actionCreate()
    {
        //获取操作
        $behavior = \Yii::$app->request->get('behavior', 'repository');
        switch ($behavior) {
            case 'repository':
                return $this->saveRepository();
                break;
            case 'evaluate':
                return $this->saveEvaluate();
                break;
            default:
                Error('无此操作');
                break;
        }
    }

    /**
     * 新增评论库
     * @return bool
     */
    private function saveRepository()
    {
        $repository = new EvaluateRepository();
        $name = \Yii::$app->request->post('name');
        $exits = EvaluateRepository::findOne(['name' => $name, 'is_deleted' => 0]);
        if ($exits) {
            Error('存在重名评论库');
        }
        $repository->name = $name;
        if (!$repository->save()) {
            Error($repository->getErrorMsg());
        }
        return true;
    }

    /**
     * 新增评论
     * @return bool
     * @throws \yii\db\Exception
     */
    private function saveEvaluate()
    {
        $post = \Yii::$app->request->post('form');
        if (!is_array($post)) {
            Error('参数格式不正确');
        }
        $id = $post[0]['repository_id'];
        $repository = EvaluateRepository::findOne($id);
        if (!$repository) {
            Error('该评论库不存在');
        }
        $evaluateList = [];
        $now = time();
        foreach ($post as $item) {
            $newItem = [];
            $newItem['repository_id'] = $item['repository_id'];
            $newItem['content'] = $item['content'];
            $newItem['star'] = $item['star'];
            $newItem['images'] = to_json($item['images']);
            $newItem['created_time'] = $now;
            $evaluateList[] = $newItem;
        }
        \Yii::$app->db->createCommand()->batchInsert(
            Evaluate::tableName(),
            ['repository_id', 'content', 'star', 'images', 'created_time'],
            $evaluateList
        )->execute();
        return true;
    }

    public function actionUpdate()
    {
        //获取操作
        $behavior = \Yii::$app->request->get('behavior', 'repository');
        switch ($behavior) {
            case 'repository':
                return $this->updateRepository();
                break;
            case 'evaluate':
                return $this->updateEvaluate();
                break;
            default:
                Error('无此操作');
                break;
        }
    }

    /**
     * 更新评论库
     * @return bool
     */
    private function updateRepository()
    {
        $repository = EvaluateRepository::findOne(\Yii::$app->request->post('id', 0));
        if (!$repository) {
            Error('评论库不存在');
        }
        $name = \Yii::$app->request->post('name');
        $exits = EvaluateRepository::find()->where(['!=', 'id', $repository->id])
            ->andWhere(['name' => $name, 'is_deleted' => 0])
            ->limit(1)
            ->one();
        if ($exits) {
            Error('存在重名评论库');
        }
        $repository->name = $name;
        if (!$repository->save()) {
            Error($repository->getErrorMsg());
        }
        return true;
    }

    /**
     * 更新评论
     * @return bool
     */
    public function updateEvaluate()
    {
        $evaluate = Evaluate::findOne(\Yii::$app->request->post('id', 0));
        if (!$evaluate) {
            Error('该评论不存在');
        }
        $evaluate->content = \Yii::$app->request->post('content');
        $evaluate->star = \Yii::$app->request->post('star');
        $evaluate->images = to_json(\Yii::$app->request->post('images', []));
        if (!$evaluate->save()) {
            Error($evaluate->getErrorMsg());
        }
        return true;
    }

    public function actionDelete()
    {
        $id   = \Yii::$app->request->get('id', false);
        if (!$id) {
            Error('ID缺失');
        }
        $model = null;
        $behavior = \Yii::$app->request->get('behavior', 'repository');
        if ($behavior == 'repository') {
            $model = EvaluateRepository::findOne(['id' => $id, 'is_deleted' => 0]);
            if (!$model) {
                Error('评论库不存在');
            }
            $model->is_deleted = 1;
        } elseif ($behavior == 'evaluate') {
            $model = Evaluate::findOne(['id' => $id, 'is_deleted' => 0]);
            if (!$model) {
                Error('评论不存在');
            }
            $model->is_deleted = 1;
        } else {
            Error('未知的行为');
        }
        if ($model->save()) {
            return true;
        } else {
            Error('操作失败');
        }
    }
}
