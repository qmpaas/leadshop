<?php

namespace leadmall\api;

use basics\api\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

class CloudController extends BasicsModules implements Map
{
    public $modelClass = 'setting\models\Setting';

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
        $headers = \Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 5;
        $page     = \Yii::$app->request->get('page', 1);
        return [
            'version' => \Yii::$app->cloud->update->getVersionData([
              'page'  => $page,
              'limit' => $pageSize,
            ]),
            'auth' =>  \Yii::$app->cloud->auth->getAuthData(),
        ];
    }

    public function actionCreate($value = '')
    {
        $res = $this->modelClass::find()->where(['keyword' => 'mysql_version'])->one();
        if (!$res) {
            $article              = new $this->modelClass();
            $article->keyword     = 'mysql_version';
            $article->merchant_id = 1;
            $article->content     = app_version();
            $article->save();
        }
        if (file_exists(Yii::$app->basePath . "/install.lock")) {
            return @file_get_contents(Yii::$app->basePath . "/install.lock");
        } else {
            Error("锁文件不存在");
        }
    }
}
