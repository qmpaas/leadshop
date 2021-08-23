<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\api;

use basics\common\BasicsController as BasicsModules;
use Yii;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=api/leadmall/plugin&include=task&model=task
 */
class TaskController extends BasicsModules
{
    public $modelClass = 'plugins\task\models\Task';

    /**
     * GET多条记录
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $keyword = Yii::$app->request->get("keyword", "");
        if ($keyword) {
            return $this->modelClass::find()->where(['keyword' => $keyword])->asArray()->one();
        } else {
            return $this->modelClass::find()->asArray()->all();
        }

    }

    /**
     * GET单条记录
     * @return [type] [description]
     */
    public function actionView()
    {
        return 233333;
    }

    /**
     * POST写入
     * @return [type] [description]
     */
    public function actionCreate()
    {

    }

    /**
     * 更新数据
     * @return [type] [description]
     */
    public function actionUpdate()
    {

    }

    /**
     * 删除数据
     * @return [type] [description]
     */
    public function actionDelete()
    {

    }
}
