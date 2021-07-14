<?php
/**
 * 插件模式
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
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
class UserController extends BasicsModules
{
    public $modelScore = 'plugins\task\models\TaskScore';

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
        $with  = [
            'user as user',
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
        if ($score_start !== '') {
            $where = ['and', $where, ['>=', 's.number', $score_start]];
        }
        $score_end = $keyword['score_end'] ?? -1;
        if ($score_end !== '') {
            $where = ['and', $where, ['<=', 's.number', $score_end]];
        }

        if ($keyword['date'] && $keyword['date'][0]) {
            $keyword['time_start'] = strtotime($keyword['date'][0]);
            $keyword['time_end']   = strtotime($keyword['date'][1]);
        }

        //注册时间区间
        $created_time_start = $keyword['time_start'] ?? false;
        if ($created_time_start > 0) {
            $where = ['and', $where, ['>=', 's.start_time', $created_time_start]];
        }
        $created_time_end = $keyword['time_end'] ?? false;
        if ($created_time_end > 0) {
            $where = ['and', $where, ['<=', 's.start_time', $created_time_end]];
        }

        //处理排序
        $sort    = isset($keyword['sort']) && is_array($keyword['sort']) ? $keyword['sort'] : [];
        $orderBy = [];
        if (empty($sort)) {
            $orderBy = ['s.start_time' => SORT_DESC];
        } else {
            foreach ($sort as $key => $value) {
                $orderBy[$key] = $value === 'ASC' ? SORT_ASC : SORT_DESC;
            }
        }

        //处理返回结果集
        return new ActiveDataProvider(
            [
                'query'      => $this->modelScore::find()
                    ->from(['s' => $this->modelScore::tableName()])
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
        return 233333;
    }

    /**
     * POST写入
     * @return [type] [description]
     */
    public function actionCreate()
    {
        //读取用户信息
        $UID = Yii::$app->request->post('UID');
        //读取用户信息
        $user_list = Yii::$app->request->post('user_list');
        //设置分数值
        $number = Yii::$app->request->post('number', 0);
        //判断是批量还是单个
        $type = Yii::$app->request->post('type', 1);
        if ($number === 0) {
            return false;
        }
        //处理单个用户-此处预留循环写法用于插件视频教程录制
        //后续会改批量操作
        if ($type == 1) {
            if ($number > 0) {
                return $this->plugins("task", ["scoreadd", [$number, $UID, 0, 'add', '后台手动充值']]);
            } else {
                return $this->plugins("task", ["scoreadd", [$number, $UID, 0, 'del', '后台手动扣减']]);
            }
        }
        if ($type == 2) {
            foreach ($user_list as $key => $value) {
                if ($number > 0) {
                    $this->plugins("task", ["scoreadd", [$number, $value, 0, 'add', '后台手动充值']]);
                } else {
                    $this->plugins("task", ["scoreadd", [$number, $value, 0, 'del', '后台手动扣减']]);
                }
            }
        }
        return true;
    }

    /**
     * 更新数据
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        return 123123123;
    }

    /**
     * 删除数据
     * @return [type] [description]
     */
    public function actionDelete()
    {

    }
}
