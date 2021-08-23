<?php
/**
 * 插件模式
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace plugins\task\api;

use basics\api\BasicsController as BasicsModules;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * 执行插件
 * include =>需要加载的插件名称
 * http://www.qmpaas.com/index.php?q=api/leadmall/plugin&include=task&model=task
 */
class OrderController extends BasicsModules
{
    public $orderModel = 'order\models\Order';

    /**
     * GET多条记录
     * @return [type] [description]
     */
    public function actionIndex()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;
        //搜索关键字
        $keyword = Yii::$app->request->get('keyword', "[]");

        //数据转换
        if ($keyword) {
            $keyword = to_array($keyword);
        }

        $AppID = Yii::$app->params['AppID'];

        $where = ['user.AppID' => $AppID];

        $with = [
            'user as user',
            'goods as goods',
            'oauth as oauth',
        ];

        //关键词搜索
        $search = $keyword['search'] ?? false;
        if ($search) {
            $where = ['and', $where, ['or', ['like', 'user.nickname', $search], ['like', 'user.realname', $search], ['user.mobile' => $search], ['user.id' => $search]]];
        }

        //用户来源筛选
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['oauth.type' => $source]];
        }

        //积分区间
        $score_start = $keyword['score_start'] ?? -1;
        if ($score_start !== '' && $score_start >= 0) {
            $where = ['and', $where, ['>=', 'o.total_score', $score_start]];
        }
        $score_end = $keyword['score_end'] ?? -1;
        if ($score_start !== '' && $score_end >= 0) {
            $where = ['and', $where, ['<=', 'o.total_score', $score_end]];
        }

        if ($keyword['date'] && $keyword['date'][0]) {
            $keyword['time_start'] = strtotime($keyword['date'][0]);
            $keyword['time_end']   = strtotime($keyword['date'][1]);
        }

        //注册时间区间
        $created_time_start = $keyword['time_start'] ?? false;
        if ($created_time_start > 0) {
            $where = ['and', $where, ['>=', 'o.created_time', $created_time_start]];
        }
        $created_time_end = $keyword['time_end'] ?? false;
        if ($created_time_end > 0) {
            $where = ['and', $where, ['<=', 'o.created_time', $created_time_end]];
        }

        //处理积分订单
        //'o.status' => 'task'
        $where = ['and', $where, ['o.type' => 'task']];

        $where = ['and', $where, ['>=', 'o.status', '201']];

        //处理排序
        $sort    = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['o.created_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                $orderBy[$key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
            }
        }

        //处理返回结果集
        return new ActiveDataProvider(
            [
                'query'      => $this->orderModel::find()
                    ->from(['o' => $this->orderModel::tableName()])
                    ->joinWith($with)
                    ->where($where)
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );
    }

    /**
     * GET单条记录
     * @return [type] [description]
     */
    public function actionView()
    {

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
