<?php

namespace promoter\app;

use framework\common\BasicController;
use promoter\models\Promoter;
use promoter\models\PromoterZone;
use promoter\models\PromoterZoneUpvote;
use setting\models\Setting;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

class ZoneController extends BasicController
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

    public function actionCreate()
    {
        $behavior = \Yii::$app->request->get('behavior', 'zone');
        switch ($behavior) {
            case 'zone':
                return $this->editZone();
            case 'vote':
                return $this->vote();
        }

    }

    public function actionUpdate()
    {
        return $this->editZone();
    }

    private function editZone()
    {
        $promoter = Promoter::findOne(['UID' => \Yii::$app->user->id]);
        if (!$promoter) {
            Error('你不是分销商');
        }
        $id = \Yii::$app->request->get('id');
        if ($id) {
            $model = PromoterZone::find()->where(['id' => $id, 'UID' => \Yii::$app->user->id])->limit(1)->one();
            if (!$model) {
                Error('该动态不存在');
            }
        } else {
            $model = new PromoterZone();
        }
        $post = \Yii::$app->request->post();
        $model->attributes = $post;
        $model->AppID = \Yii::$app->params['AppID'];
        $model->is_admin = 0;
        $model->merchant_id = 1;
        $model->UID = \Yii::$app->user->id;
        if (isset($post['pic_list'])) {
            $model->pic_list = to_json($post['pic_list']);
        }
        if (isset($post['video_list'])) {
            $model->video_list = to_json($post['video_list']);
        }
        if (isset($post['link'])) {
            $model->link = to_json($post['link']);
        }
        if (!$model->save()) {
            Error($model->getErrorMsg());
        } else {
            return $model->id;
        }
    }

    private function vote()
    {
        $post = \Yii::$app->request->post();
        $zoneId = $post['zone_id'] ?? false;
        if (!$zoneId) {
            Error('请选择动态');
        }
        /**@var PromoterZoneUpvote $vote*/
        $vote = PromoterZoneUpvote::find()
            ->where(['zone_id' => $zoneId, 'UID' => \Yii::$app->user->id])
            ->one();
        if ($vote) {
            $vote->delete();
            return [
                'msg' => '取消点赞'
            ];
        }
        $vote = new PromoterZoneUpvote();
        $vote->zone_id = $zoneId;
        $vote->UID = \Yii::$app->user->id;
        $vote->AppID = \Yii::$app->params['AppID'];
        $vote->merchant_id = 1;
        if (!$vote->save()) {
            Error('点赞失败' . $vote->getErrorMsg());
        }
        return [
            'msg' => '点赞成功'
        ];
    }

    /**
     * 空间动态列表
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        //获取头部信息
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $query = PromoterZone::find()
            ->where(['AppID' => \Yii::$app->params['AppID'], 'is_deleted' => 0])
            ->with(['upvote' => function ($query) {
                $query->select(['UID', 'zone_id'])->with(['user' => function ($query) {
                    $query->select(['nickname', 'id', 'avatar']);
                }]);
            }, 'user' => function ($query) {
                $query->select(['nickname', 'id', 'avatar']);
            }]);
        $get = \Yii::$app->request->get();
        $type = $get['type'] ?? false;
        if ($type) {
            $query->andWhere(['type' => $type]);
        }
        $content = $get['content'] ?? false;
        if ($content) {
            $query->andWhere(['like', 'content', $content]);
        }
        $uid = $get['UID'] ?? false;
        if (!$uid) {
            Error('用户id不能为空');
        }
        $query->andWhere([
            'or',
            ['UID' => $uid],
            ['is_admin' => 1]
        ]);
        $data = new ActiveDataProvider(
            [
                'query' => $query->orderBy(['created_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );

        $logo = 'https://qmxq.oss-cn-hangzhou.aliyuncs.com/home.png';
        $res = Setting::findOne(['AppID' => \Yii::$app->params['AppID'], 'keyword' => 'setting_collection']);
        if ($res) {
            $info = to_array($res['content']);
            $logo = $info['store_setting']['logo'] ?? 'https://qmxq.oss-cn-hangzhou.aliyuncs.com/home.png';
        }
        $list = $data->getModels();
        $list = str2url($list);
        foreach ($list as $key => &$value) {
            $value['is_vote'] = 0;
            if (!\Yii::$app->user->isGuest && in_array(\Yii::$app->user->id, array_column($value['upvote'], "UID" ))) {
                $value['is_vote'] = 1;
            }
            $value['pic_list'] = to_array($value['pic_list']);
            $value['video_list'] = to_array($value['video_list']);
            $value['link'] = to_array($value['link']);
            $value['upvote_count'] = count($value['upvote']);
            $value['can_edit'] = $value['UID'] == \Yii::$app->user->id;
            $value['can_delete'] = $value['UID'] == \Yii::$app->user->id;
            if ($value['is_admin']) {
                $name = '小店';
                $storeSetting = StoreSetting('setting_collection', 'store_setting');
                if ($storeSetting) {
                    $name = $storeSetting['name'];
                }
                $value['user']['nickname'] = $name;
                $value['user']['avatar'] = str2url($logo);
            }
        }
        $data->setModels($list);
        return $data;
    }

    /**
     * 空间动态详情
     * @return array|array[]|object|object[]|string|string[]
     */
    public function actionView()
    {
        $id = \Yii::$app->request->get('id', 0);
        $material = PromoterZone::findOne(['id' => $id, 'is_deleted' => 0]);
        if (!$material) {
            Error('该动态不存在');
        }
        $material = ArrayHelper::toArray($material);
        $material['pic_list'] = to_array($material['pic_list']);
        return $material;
    }

    public function actionDelete()
    {
        $get   = \Yii::$app->request->get();
        $id    = intval($get['id']);
        $model = PromoterZone::find()->where(['id' => $id, 'UID' => \Yii::$app->user->id])->limit(1)->one();
        if ($model) {
            $model->deleted_time = time();
            $model->is_deleted   = 1;
            if ($model->save()) {
                return true;
            } else {
                throw new ForbiddenHttpException('删除失败，请检查is_deleted字段是否存在');
            }
        } else {
            throw new ForbiddenHttpException('删除失败，数据不存在');
        }
    }

}
