<?php
/**
 * 用户导出控制器
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace users\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class ExportController extends BasicController
{
    public $modelClass = 'users\models\UserExport';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    public function actionIndex()
    {
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 20;

        $AppID = Yii::$app->params['AppID'];
        $where = [
            'is_deleted' => 0,
            'AppID'      => $AppID,
        ];

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()->where($where)->orderBy(['created_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['conditions'] = to_array($value['conditions']);
            $value['user_data']  = to_array($value['user_data']);
        }
        $data->setModels($list);
        return $data;
    }

    public function actionCreate()
    {

        $keyword = Yii::$app->request->post('conditions', []); //查询条件
        $id_list = Yii::$app->request->post('id_list', []); //选择的商品
        $AppID   = Yii::$app->params['AppID'];

        if (empty($id_list)) {
            $where = ['user.AppID' => $AppID];

            //用户来源筛选
            $source = $keyword['source'] ?? false;
            if ($source) {
                $where = ['and', $where, ['user.source' => $source]];
            }

            //购买次数区间
            $buy_number_start = $keyword['buy_number_start'] ?? false;
            if ($buy_number_start > 0) {
                $where = ['and', $where, ['>=', 'statistical.buy_number', $buy_number_start]];
            }
            $buy_number_end = $keyword['buy_number_end'] ?? false;
            if ($buy_number_end > 0) {
                $where = ['and', $where, ['<=', 'statistical.buy_number', $buy_number_end]];
            }

            //注册时间区间
            $created_time_start = $keyword['created_time_start'] ?? false;
            if ($created_time_start > 0) {
                $where = ['and', $where, ['>=', 'user.created_time', $created_time_start]];
            } else {
                $user                 = M('users', 'User')::find()->where(['AppID' => $AppID])->orderBy(['created_time' => SORT_ASC])->one();
                $keyword['created_time_start'] = $user->created_time;
            }
            $created_time_end = $keyword['created_time_end'] ?? false;
            if ($created_time_end > 0) {
                $where = ['and', $where, ['<=', 'user.created_time', $created_time_end]];
            } else {
                $user                 = M('users', 'User')::find()->where(['AppID' => $AppID])->orderBy(['created_time' => SORT_DESC])->one();
                $keyword['created_time_end'] = $user->created_time;
            }

            //上次消费时间区间
            $last_buy_time_start = $keyword['last_buy_time_start'] ?? false;
            if ($last_buy_time_start > 0) {
                $where = ['and', $where, ['>=', 'statistical.last_buy_time', $last_buy_time_start]];
            }
            $last_buy_time_end = $keyword['last_buy_time_end'] ?? false;
            if ($last_buy_time_end > 0) {
                $where = ['and', $where, ['<=', 'statistical.last_buy_time', $last_buy_time_end]];
            }

            //最后访问时间区间
            $last_visit_time_start = $keyword['last_visit_time_start'] ?? false;
            if ($last_visit_time_start > 0) {
                $where = ['and', $where, ['>=', 'statistical.last_visit_time', $last_visit_time_start]];
            }
            $last_visit_time_end = $keyword['last_visit_time_end'] ?? false;
            if ($last_visit_time_end > 0) {
                $where = ['and', $where, ['<=', 'statistical.last_visit_time', $last_visit_time_end]];
            }
        } else {
            $where = ['user.id' => $id_list];
        }

        $data = M('users', 'User')::find()
            ->alias('user')
            ->joinWith([
                'statistical as statistical',
            ])
            ->where($where)
            ->groupBy(['user.id'])
            ->asArray()
            ->all();

        $tHeader     = [];
        $filterVal   = [];
        $filter_list = [['name' => 'ID', 'value' => 'id'], ['name' => '昵称', 'value' => 'nickname'], ['name' => '手机号', 'value' => 'mobile'], ['name' => '真实姓名', 'value' => 'realname'], ['name' => '头像', 'value' => 'avatar'], ['name' => '性别', 'value' => 'gender'], ['name' => '来源', 'value' => 'source'], ['name' => '状态', 'value' => 'status'], ['name' => '注册时间', 'value' => 'created_time'], ['name' => '消费次数', 'value' => 'buy_number'], ['name' => '消费金额', 'value' => 'buy_amount'], ['name' => '上次消费时间', 'value' => 'last_buy_time'], ['name' => '上次访问时间', 'value' => 'last_visit_time']];
        foreach ($filter_list as $v) {
            array_push($tHeader, $v['name']);
            array_push($filterVal, $v['value']);
        }

        $list = [];
        foreach ($data as $value) {
            $res = $this->listBuild($value, $filterVal);
            array_push($list, $res);
        }

        $user_data = [
            'tHeader' => $tHeader,
            'list'    => $list,
        ];

        $ins_data = [
            'conditions' => to_json($keyword),
            'user_data'  => to_json($user_data),
            'AppID'      => $AppID,
        ];
        $model = new $this->modelClass;
        $model->setAttributes($ins_data);
        if ($model->save()) {
            return $user_data;
        } else {
            Error('保存失败');
        }

    }

    /**
     * 导出字段筛选
     * @param  [type] $data      [description]
     * @param  [type] $filterVal [description]
     * @return [type]            [description]
     */
    public function listBuild($data, $filterVal)
    {
        $return_data = [];
        foreach ($filterVal as $key) {
            $value = '';
            switch ($key) {
                case 'id':
                    $value = $data['id'];
                    break;
                case 'nickname':
                    $value = $data['nickname'];
                    break;
                case 'mobile':
                    $value = $data['mobile'];
                    break;
                case 'realname':
                    $value = $data['realname'];
                    break;
                case 'avatar':
                    $value = $data['avatar'];
                    break;
                case 'gender':
                    $value = $data['gender'] === 0 ? '保密' : ($data['gender'] === 1 ? '男' : '女');
                    break;
                case 'source':
                    $value = $data['source'];
                    break;
                case 'status':
                    $value = $data['status'] == 1 ? '禁用' : '正常';
                    break;
                case 'created_time':
                    $value = $data['created_time'];
                    $value = $value ? date('Y-m-d H:i:s', $value) : '';
                    break;
                case 'buy_number':
                    $value = $data['statistical']['buy_number'];
                    break;
                case 'buy_amount':
                    $value = $data['statistical']['buy_amount'];
                    break;
                case 'last_buy_time':
                    $value = $data['statistical']['last_buy_time'];
                    $value = $value ? date('Y-m-d H:i:s', $value) : '';
                    break;
                case 'last_visit_time':
                    $value = $data['statistical']['last_visit_time'];
                    $value = $value ? date('Y-m-d H:i:s', $value) : '';
                    break;
            }

            array_push($return_data, $value);
        }

        return $return_data;

    }
}
