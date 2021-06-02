<?php
/**
 * 设置管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace fitment\api;

use fitment\models\Fitment;
use framework\common\BasicController;
use Yii;

class IndexController extends BasicController
{
    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions           = parent::actions();
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

    /**
     * 获取商城配置
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $AppID = Yii::$app->params['AppID'];
        $data   = M()::find()->where(['AppID' => $AppID])->asArray()->all();
        foreach ($data as &$value) {
            $value['content'] = to_array($value['content']);
        }
        return $data;
    }

    /**
     * 搜索商城配置
     * @return [type] [description]
     */
    public function actionSearch()
    {
        $keyword = Yii::$app->request->post('keyword',false);
        $AppID = Yii::$app->params['AppID'];

        $data = M()::find()->where(['AppID' => $AppID,'keyword'=>$keyword])->select('keyword,content')->asArray()->one();

        if ($data) {
            return str2url($data);
        } else {
            Error('设置不存在');
        }
    }

    /**
     * 重写修改方法
     * @return [type] [description]
     */
    public function actionCreate()
    {
        if (!N('keyword') || !N('content')) {
            Error('缺少参数');
        }
        $post = Yii::$app->request->post();
        if ($post['keyword'] == 'tabbar') {
            $arr = [];
            $content = to_array($post['content']);
            foreach ($content['data'] as $v) {
                if (in_array($v['text'], $arr)) {
                    Error('底部导航名称不能重复');
                } else {
                    array_push($arr, $v['text']);
                }
            }
        }
        $post = url2str($post);
        $AppID = Yii::$app->params['AppID'];
        $model = M()::find()->where(['AppID' => $AppID,'keyword'=>$post['keyword']])->one();

        if (empty($model)) {
            $model = M('fitment','Fitment',true);
            $model->keyword = $post['keyword'];
            $model->AppID = $AppID;
        }

        $model->content = $post['content'];
        if ($model->save()) {
            return true;
        } else {
            Error('保存失败');
        }
    }

}
