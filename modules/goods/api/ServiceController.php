<?php
/**
 * 商品服务管理
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */

namespace goods\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class ServiceController extends BasicController
{
    public $modelClass = 'goods\models\GoodsService';

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
     * 从写获取方法，获取全部分组
     * @return [type] [description]
     */
    public function actionIndex()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];

        $search = Yii::$app->request->get('search', false);
        if ($search) {
            $where = ['and', $where, ['like', 'title', $search]];
        }

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()->where($where)->orderBy(['sort' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();

        //处理服务下商品数
        $goods_list = M('goods', 'Goods')::find()->where(['is_deleted' => 0, 'merchant_id' => $merchant_id])->select('services')->asArray()->all();
        $id_list    = [];
        foreach ($goods_list as $v) {
            $services = $v['services'] ? to_array($v['services']) : [];
            $id_list  = array_merge($id_list, $services);
        }
        $id_count = array_count_values($id_list);
        foreach ($list as $key => &$value) {
            $value['goods_number'] = $id_count[$value['id']] ?? 0;
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
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
            'status'      => 1,
        ];
        return $this->modelClass::find()->where($where)->orderBy(['sort' => SORT_DESC])->all();
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
                $post = Yii::$app->request->post();

                $post['merchant_id'] = 1;
                $post['AppID']       = Yii::$app->params['AppID'];
                Yii::$app->request->setBodyParams($post);
                break;
        }
    }
}
