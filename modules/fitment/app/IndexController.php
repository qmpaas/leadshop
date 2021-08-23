<?php
/**
 * 设置管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace fitment\app;

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
        return str2url($data);
    }

    /**
     * 搜索商城配置
     * @return [type] [description]
     */
    public function actionCreate()
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

}
