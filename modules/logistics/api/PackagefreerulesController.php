<?php
/**
 * 包邮规则管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace logistics\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class PackagefreerulesController extends BasicController
{
    public $modelClass = 'logistics\models\PackageFreeRules';

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

        unset($actions['index']);
        unset($actions['update']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * 重写获取列表
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
            $where = ['and', $where, ['like', 'name', $search]];
        }

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()->where($where)->orderBy(['created_time'=>SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['free_area'] = to_array($value['free_area']);
        }
        $data->setModels($list);
        return $data;
    }

    /**
     * 重写获取单个
     * @return [type] [description]
     */
    public function actionView()
    {
        $id    = Yii::$app->request->get('id', 0);
        $id    = intval($id);
        $where = [
            'id'         => $id,
            'is_deleted' => 0,
        ];

        $data = $this->modelClass::find()->where($where)->one();

        if (!empty($data)) {
            $data['free_area'] = to_array($data['free_area']);
            return $data;
        } else {
            Error('数据不存在');
        }

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
            default:
                return $this->update();
                break;
        }
    }

    /**
     * 编辑规则
     * @return [type] [description]
     */
    public function update()
    {
        $id   = Yii::$app->request->get('id', 0);
        $id   = intval($id);
        $post = Yii::$app->request->post();

        if (N('free_area')) {
            $post['free_area'] = to_json($post['free_area']);
        }

        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('规则不存在');
        }
        $model->setScenario('update');
        $model->setAttributes($post);
        if ($model->validate()) {
            if ($model->save()) {
                return true;
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    /**
     * 设置默认规则
     */
    public function setting()
    {
        $id          = Yii::$app->request->get('id', 0);
        $id          = intval($id);
        $merchant_id = 1;

        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('规则不存在');
        }

        $this->modelClass::updateAll(['status' => 0], ['and', ['merchant_id' => $merchant_id], ['<>', 'id', $id]]);

        $model->status = 1;
        if ($model->save()) {
            return true;
        } else {
            Error('操作失败');
        }

    }

    /**
     * 删除重写
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $get = Yii::$app->request->get();

        $id = intval($get['id']);

        //判断是否存在商品
        $check_goods = M('goods','Goods')::find()->where(['pfr_id'=>$id,'is_deleted' => 0])->exists();
        if ($check_goods) {
            Error('有商品在使用，不可删除');
        }

        $model = $this->modelClass::findOne($id);
        if ($model) {
            $model->deleted_time = time();
            $model->is_deleted   = 1;
            if ($model->save()) {
                return true;
            } else {
                Error('删除失败，请检查is_deleted字段是否存在');
            }
        } else {
            Error('删除失败，数据不存在');
        }
    }

    /**
     * 下拉栏数据获取
     * @return [type] [description]
     */
    public function actionOption()
    {
        $merchant_id = 1;
        $where       = ['is_deleted' => 0, 'merchant_id' => $merchant_id];
        return $this->modelClass::find()->where($where)->asArray()->select('id,name,type,status')->orderBy(['created_time'=>SORT_DESC])->all();
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
                if (!empty($post['free_area'])) {
                    $post['free_area'] = to_json($post['free_area']);
                }

                $post['merchant_id'] = 1;
                $post['AppID']       = Yii::$app->params['AppID'];
                Yii::$app->request->setBodyParams($post);
                break;
        }
    }

}
