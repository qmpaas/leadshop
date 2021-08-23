<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace system\api;

use framework\common\BasicController;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

/**
 * 后台用户管理器
 */
class MenusController extends BasicController
{
    public $modelClass = 'system\models\Menus';
    /**
     * 用于返回所有菜单信息
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $get        = Yii::$app->request->get();
        $modelClass = $this->modelClass;
        if (isset($get['type'])) {
            $model = $modelClass::find()->where(['is_deleted' => 0, 'name' => $get['type']])->one();
            return $modelClass::find()
                ->where(['like', 'path', $model['path'] . '-' . $model['id']])
                ->andWhere(['is_deleted' => 0])
                ->all();
        } else {
            return $modelClass::find()->where(['is_deleted' => 0])->all();
        }
    }

    /**
     * 处理数据搜索问题
     * @return [type] [description]
     */
    public function actionTree()
    {
        $modelClass = $this->modelClass;
        $model      = $modelClass::find()->select(['id', 'title', 'concat(path,"-",id) as path'])->asArray()->orderBy('path,id ASC')->all();
        foreach ($model as $key => $value) {
            $number               = count(explode('-', $value['path'])) - 1;
            $title                = str_repeat("|-", $number) . $value['title'];
            $model[$key]['title'] = $title;
        }
        return $model;
    }

    /**
     * 处理数据分页问题
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $get        = Yii::$app->request->get();
        $modelClass = $this->modelClass;
        $id         = intval($get['id']);
        $model      = $modelClass::findOne($id);
        if ($model) {
            $subset = $modelClass::find()->where(['parent_id' => $id])->all();
            if ($subset) {
                throw new ServerErrorHttpException('检测有子集存在，无法删除，请先删除子集');
            } else {
                $model->deleted_time = time();
                $model->is_deleted   = 1;
                if ($model->save()) {
                    Yii::$app->getResponse()->setStatusCode(204);
                    return $model->is_deleted;
                } else {
                    throw new ForbiddenHttpException('删除失败，请检查is_deleted字段是否存在');
                }
            }

        } else {
            throw new ForbiddenHttpException('删除失败，数据不存在');
        }
    }

    /**
     * 处理数据分页问题
     * @return [type] [description]
     */
    public function actionGather()
    {
        $get        = Yii::$app->request->get();
        $modelClass = $this->modelClass;
        return $modelClass::find()->where(['is_deleted' => 0])->indexBy("id")->all();
    }

    /**
     * 处理数据搜索问题
     * @return [type] [description]
     */
    public function actionSearch()
    {
        $modelClass = $this->modelClass;
        $data       = \Yii::$app->request->post();
        return $modelClass::find()->where(['like', 'title', $data['keyword']])->all();
    }

}
