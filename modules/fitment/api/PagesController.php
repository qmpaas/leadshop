<?php
/**
 * 设置管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace fitment\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class PagesController extends BasicController
{
    public $modelClass = 'fitment\models\Page';
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions           = parent::actions();
        $actions['create'] = [
            'class'       => 'yii\rest\CreateAction',
            'modelClass'  => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'scenario'    => 'create',
        ];

        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'firstPage':
                return $this->firstPage();
                break;
            default:
                return $this->list();
                break;
        }
    }

    public function firstPage()
    {
        $AppID = Yii::$app->params['AppID'];
        $data  = $this->modelClass::find()->where(['is_deleted' => 0, 'status' => 1, 'AppID' => $AppID])->asArray()->one();
        if (empty($data)) {
            Error('首页不存在');
        }
        return str2url($data);
    }

    /**
     * 获取设置列表
     * @return [type] [description]
     */
    function list() {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        $AppID    = Yii::$app->params['AppID'];

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()->where(['is_deleted' => 0, 'AppID' => $AppID])->select('id,title,name,goods_number,status,visit_number,created_time')->orderBy(['status' => SORT_DESC, 'created_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['visitor_number'] = 0;
        }
        $data->setModels($list);
        return $data;
    }

    /**
     * 下拉栏数据获取
     * @return [type] [description]
     */
    public function actionOption()
    {
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted' => 0,
            'AppID'      => $AppID,
        ];
        return $this->modelClass::find()->where($where)->select('id,title,name,status')->all();
    }

    /**
     * 获取微页面详情
     * @return [type] [description]
     */
    public function actionView()
    {
        $id   = Yii::$app->request->get('id', 0);
        $data = $this->modelClass::find()->where(['is_deleted' => 0, 'id' => $id])->asArray()->one();
        if (empty($data)) {
            Error('微页面不存在');
        }
        return str2url($data);
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
            case 'setting':
                return $this->setting();
                break;
            case 'check_title':
                return $this->check_title();
                break;
            default:
                return $this->update();
                break;
        }
    }

    /**
     * 编辑模板
     * @return [type] [description]
     */
    public function update()
    {
        $id    = Yii::$app->request->get('id', 0);
        $id    = intval($id);
        $AppID = Yii::$app->params['AppID'];

        $post = Yii::$app->request->post();
        $post = url2str($post);

        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('页面不存在');
        }
        if (N('content')) {
            $post['content'] = $this->buildContent($post['content']);
        }
        $check = $this->modelClass::find()->where(['and', ['<>', 'id', $id], ['AppID' => $AppID, 'is_deleted' => 0, 'title' => $post['title']]])->one();
        if ($check) {
            Error('标题已存在');
        }

        $model->setScenario('update');
        $model->setAttributes($post);
        if ($model->validate()) {
            $res = $model->save();
            if ($res) {
                return $model;
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    /**
     * 设置默认模板
     */
    public function setting()
    {
        $id    = Yii::$app->request->get('id', 0);
        $id    = intval($id);
        $AppID = Yii::$app->params['AppID'];

        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('页面不存在');
        }

        $this->modelClass::updateAll(['status' => 0], ['and', ['AppID' => $AppID], ['<>', 'id', $id]]);

        $model->status = 1;
        if ($model->save()) {
            return true;
        } else {
            Error('操作失败');
        }

    }

    public function check_title()
    {
        $id    = Yii::$app->request->get('id', 0);
        $id    = intval($id);
        $post  = Yii::$app->request->post();
        $AppID = Yii::$app->params['AppID'];
        $check = $this->modelClass::find()->where(['and', ['<>', 'id', $id], ['AppID' => $AppID, 'is_deleted' => 0, 'title' => $post['title']]])->one();
        if ($check) {
            return true;
        } else {
            return false;
        }
    }

    public function buildContent($value)
    {
        return $value;
    }

    /**
     * 数据前置检查器
     * @param  [type]  $operation    [description]
     * @param  array   $params       [description]
     * @param  boolean $allowCaching [description]
     * @return [type]                [description]
     */
    public function checkAccess($operation, $params = array(), $allowCaching = true)
    {
        switch ($operation) {
            case 'create':
                $post  = Yii::$app->request->post();
                $AppID = Yii::$app->params['AppID'];
                $check = $this->modelClass::find()->where(['AppID' => $AppID, 'is_deleted' => 0, 'title' => $post['title']])->one();
                if ($check) {
                    Error('标题已存在');
                }
                $post['content'] = $this->buildContent($post['content']);
                $post['AppID']   = $AppID;
                $post            = url2str($post);
                Yii::$app->request->setBodyParams($post);
                break;
        }
    }
}
