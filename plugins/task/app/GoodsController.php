<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\app;

use basics\app\BasicsController as BasicsModules;
use yii\data\ActiveDataProvider;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=app/leadmall/plugin&include=task&model=task
 */
class GoodsController extends BasicsModules
{
    public $modelClass = 'plugins\task\models\TaskGoods';

    /**
     * 处理接口白名单
     * @var array
     * ->createCommand()->getRawSql(); 获取SQL语句
     * ->asArray() 如果不加asArray是无法显示关联数据的
     */
    public $whitelists = ['index'];

    public function actionIndex()
    {
        $where = $_POST['where'] ?? "g.id ASC";
        //处理执行关联数据查询
        //切记一定要加asArray()否则关联数据会查询不到
        //如果要做where条件查询一定要使用->alias('t')设置别名
        $query = $this->modelClass::find()->orderBy("g.id ASC")->alias('t')->where(['t.is_sale' => 1])->joinWith('goods')->asArray();
        //执行分页和排序处理
        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => 3,
            ],
        ]);
    }

    /**
     * 创建界面
     * @return [type] [description]
     */
    public function actionCreate()
    {
        return 1111;
    }

}
