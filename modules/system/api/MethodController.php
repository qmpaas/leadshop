<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace system\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * 后台用户管理器
 */
class MethodController extends BasicController
{

    /**
     * 处理数据分页问题
     * @return [type] [description]
     */
    public function actionList()
    {
        $headers    = Yii::$app->getRequest()->getHeaders();
        $pageSize   = $headers->get('X-Pagination-Per-Page') ?? 20;
        $modelClass = $this->modelClass;
        $post       = Yii::$app->request->post();
        return new ActiveDataProvider(
            [
                'query'      => $modelClass::find()->where(['is_deleted' => 0, 'modul_id' => $post['modul_id']])->asArray(),
                'pagination' => ['pageSize' => intval($pageSize), 'validatePage' => false],
            ]
        );
    }

    /**
     * 处理数据搜索问题
     * @return [type] [description]
     */
    public function actionOption()
    {
        $post       = Yii::$app->request->post();
        $modelClass = $this->modelClass;
        $where      = array("is_deleted" => 0);
        if ($post['modul_id']) {
            $where['modul_id'] = $post['modul_id'];
        }
        return $modelClass::find()->where($where)->indexBy("id")->all();
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
        if ($operation == 'create') {
            $post       = Yii::$app->request->post();
            $modelClass = 'app\modules\modul\models\Modul';
            //查询获取模型名称
            $data = $modelClass::find()->where(['is_deleted' => 0, 'id' => $post['modul_id']])->one();
            //改写模型名称信息
            $post['name'] = $data['name'] . "/" . $post['controller'] . "/" . $post['action'];
            Yii::$app->request->setBodyParams($post);
        }
    }
}
