<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\app;

use basics\app\BasicsController as BasicsModules;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=app/leadmall/plugin&include=task&model=task
 */
class LogController extends BasicsModules
{
    public $UserModel  = 'plugins\task\models\TaskUser';
    public $LogModel   = 'plugins\task\models\TaskLog';
    public $ModelScore = 'plugins\task\models\TaskScore';

    /**
     * 小程序获取明细
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $UID     = Yii::$app->user->identity->id;
        $year    = Yii::$app->request->get("year", date('Y'));
        $month   = Yii::$app->request->get("month", date('M'));
        $type    = Yii::$app->request->get("type");
        $keyword = Yii::$app->request->get("keyword");
        $number  = Yii::$app->request->get("number", '');
        $status  = Yii::$app->request->get("status", 0);

        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        if ($keyword) {
            $where = ["UID" => $UID, "extend" => $keyword];
            if ($number) {
                $where = ['and', $where, ["number" => $number]];
            }
            if ($status) {
                $where = ['and', $where, ["status" => $status]];
            }
            return $this->LogModel::find()->where($where)->asArray()->all();
        } else {
            //获取年月日
            $BeginDate = date('Y-m-01', strtotime($year . "-" . $month . "-01"));
            $EndDate   = date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));

            //获取类型 收入 支出 还是全部
            $typeIn = ['add', 'del'];

            if ($type == 1) {
                $typeIn = ['add'];
            }

            if ($type == 2) {
                $typeIn = ['del'];
            }

            return new ActiveDataProvider(
                [
                    'query'      => $this->ModelScore::find()
                        ->joinWith('task')
                        ->from(['s' => $this->ModelScore::tableName()])
                        ->where(['s.status' => 1, 'UID' => $UID])
                        ->andWhere(['>=', 's.start_time', strtotime($BeginDate)])
                        ->andWhere(['<=', 's.start_time', strtotime($EndDate)])
                        ->andWhere(['in', 's.type', $typeIn])
                        ->orderBy("start_time DESC")
                        ->asArray(),
                    'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
                ]
            );
        }

    }

    /**
     * POST写入
     * @return [type] [description]
     */
    public function actionCreate()
    {
        //获取提交过来的参数
        $post = Yii::$app->request->post();
        //判断是否存在
        $returned = $TaskModel::find()->where(array('keyword' => $post['keyword']))->one();
        //判断是否存在数据
        if ($returned) {
            //通过任务的类型，设置不同的任务
            switch (intval($returned['type'])) {
                case 1:
                    # code...
                    break;
                case 2:
                    # code...
                    break;
                case 3:
                    # code...
                    break;
                case 4:
                    # code...
                    break;
            }
        } else {
            return false;
        }
    }
}
