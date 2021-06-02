<?php
/**
 * 设置管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace fitment\app;

use app\forms\video\Video;
use framework\common\BasicController;
use Yii;

class PagesController extends BasicController
{
    public $modelClass = 'fitment\models\Page';
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
     * 获取设置列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $AppID = Yii::$app->params['AppID'];

        $data = $this->modelClass::find()->where(['is_deleted' => 0, 'status' => 1, 'AppID' => $AppID])->asArray()->one();
        if (empty($data)) {
            Error('首页不存在');
        }
        $data            = str2url($data);
        $data['content'] = to_array($data['content']);
        foreach ($data['content'] as &$v) {
            if ($v && $v['name'] == 'video' && $v['content']['source'] == 1 && $v['content']['video']['url']) {
                $v['content']['video']['url'] = Video::getUrl($v['content']['video']['url']);
            }
        }
        $data['content'] = to_json($data['content']);
        return $data;
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
        $data            = str2url($data);
        $data['content'] = to_array($data['content']);
        foreach ($data['content'] as &$v) {
            if ($v && $v['name'] == 'video' && $v['content']['source'] == 1 && $v['content']['video']['url']) {
                $v['content']['video']['url'] = Video::getUrl($v['content']['video']['url']);
            }
        }
        $data['content'] = to_json($data['content']);
        return $data;
    }

}
