<?php
/**
 * 退货地址管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace users\app;

use framework\common\BasicController;
use Yii;

class AddressController extends BasicController
{
    public $modelClass = 'users\models\Address';

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
     * 从写获取方法，获取全部分组
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $type = Yii::$app->request->get('behavior', false);
        if ($type == 'default') {
            return $this->defaultAddress();
        }

        $UID = Yii::$app->user->identity->id;

        return $this->modelClass::find()->where(['is_deleted' => 0, 'UID' => $UID])->select('id,name,mobile,province,city,district,address,status,created_time')->orderBy(['created_time' => SORT_DESC])->asArray()->all();

    }

    public function defaultAddress()
    {
        $UID = Yii::$app->user->identity->id;
        return $this->modelClass::find()->where(['is_deleted' => 0, 'UID' => $UID, 'status' => 1])->select('id,name,mobile,province,city,district,address,status')->asArray()->one();
    }

    /**
     * 重写修改方法
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        $UID  = Yii::$app->user->identity->id;

        $count = $this->modelClass::find()->where(['is_deleted' => 0, 'UID' => $UID])->select('id')->count();
        if ($count >= 20) {
            Error('最多添加20个地址');
        }

        $model = new $this->modelClass;
        if ($post['status'] == 1) {
            $this->modelClass::updateAll(['status' => 0], ['UID' => $UID]);
        }
        $post['UID'] = $UID;
        $model->setScenario('create');
        $model->setAttributes($post);
        if ($model->validate()) {
            if ($model->save()) {
                return $model->attributes['id'];
            } else {
                Error('保存失败');
            }

        }
        return $model;
    }

    /**
     * 重写修改方法
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        $id   = Yii::$app->request->get('id', 0);
        $post = Yii::$app->request->post();
        $UID  = Yii::$app->user->identity->id;

        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('模板不存在');
        }

        if ($post['status'] == 1) {
            $this->modelClass::updateAll(['status' => 0], ['and', ['UID' => $UID], ['<>', 'id', $id]]);
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
     * 数据前置检查器
     * @param  [type]  $operation    [description]
     * @param  array   $params       [description]
     * @param  boolean $allowCaching [description]
     * @return [type]                [description]
     */
    // public function checkAccess($operation, $params = array(), $allowCaching = true)
    // {
    //     switch ($operation) {
    //         case 'create':
    //             $post        = Yii::$app->request->post();
    //             $UID         = Yii::$app->user->identity->id;
    //             $post['UID'] = $UID;
    //             Yii::$app->request->setBodyParams($post);
    //             break;
    //     }
    // }
}
