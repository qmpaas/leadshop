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

class TemplateController extends BasicController
{
    public $modelClass = 'fitment\models\Template';
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
        $actions['update'] = [
            'class'       => 'yii\rest\UpdateAction',
            'modelClass'  => $this->modelClass,
            'checkAccess' => [$this, 'checkAccess'],
            'scenario'    => 'update',
        ];
        return $actions;
    }

    /**
     * 获取设置列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()->where(['is_deleted' => 0])->select('id,name,image,writer,created_time,content')->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['content'] = str2url($value['content']);
        }
        $data->setModels($list);
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
            Error('模板不存在');
        }
        return str2url($data);
    }

    /**
     * 内容处理
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
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
                $post            = Yii::$app->request->post();
                $post['content'] = $this->buildContent($post['content']);
                $post = url2str($post);
                Yii::$app->request->setBodyParams($post);
                break;
            case 'update':
                $post = Yii::$app->request->post();
                if (N('content')) {
                    $post['content'] = $this->buildContent($post['content']);
                }
                $post = url2str($post);
                Yii::$app->request->setBodyParams($post);
                break;
        }
    }
}
