<?php
/**
 * 退货地址管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace setting\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class AddressController extends BasicController
{
    public $modelClass = 'setting\models\Address';

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

    /**
     *
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $merchant_id = 1;
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 10;
        $where    = ['is_deleted' => 0, 'merchant_id' => $merchant_id];
        return new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()->where($where)->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );
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
        ];
        return $this->modelClass::find()->where($where)->orderBy(['id'=>SORT_DESC])->all();
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
     * 编辑地址
     * @return [type] [description]
     */
    public function update()
    {
        $id   = Yii::$app->request->get('id', 0);
        $post = Yii::$app->request->post();

        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('地址不存在');
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
     * 设置默认地址
     */
    public function setting()
    {
        $id          = Yii::$app->request->get('id', 0);
        $merchant_id = 1;

        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('地址不存在');
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
