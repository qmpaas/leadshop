<?php
/**
 * 订单导出控制器
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace order\api;

use framework\common\BasicController;
use Yii;
use yii\data\ActiveDataProvider;

class ExportController extends BasicController
{
    public $modelClass = 'order\models\OrderExport';

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

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'is_deleted'  => 0,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
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
            $value['parameter']  = to_array($value['parameter']);
            $value['order_data'] = to_array($value['order_data']);
        }
        $data->setModels($list);
        return $data;
    }

    public function actionCreate()
    {

        $keyword = Yii::$app->request->post('conditions', []); //查询条件
        $filter  = Yii::$app->request->post('parameter', ['order' => [], 'goods' => []]); //筛选的字段

        //处理获取商品类型
        $tab_key = $keyword['tab_key'] ?? 'all';
        switch ($tab_key) {
            case 'unpaid': //待付款
                $where = ['order.status' => 100, 'order.is_deleted' => 0];
                break;
            case 'unsent': //待发货
                $where = ['order.status' => 201, 'order.is_deleted' => 0];
                break;
            case 'unreceived': //待收货
                $where = ['order.status' => 202, 'order.is_deleted' => 0];
                break;
            case 'received': //已收货
                $where = ['order.status' => 203, 'order.is_deleted' => 0];
                break;
            case 'finished': //已完成
                $where = ['order.status' => 204, 'order.is_deleted' => 0];
                break;
            case 'closed': //已关闭
                $where = ['order.status' => [101, 102, 103], 'order.is_deleted' => 0];
                break;
            case 'recycle': //回收站
                $where = ['order.is_deleted' => 1];
                break;

            default: //默认获取全部
                $where = ['order.is_deleted' => 0];
                break;
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = ['and', $where, ['merchant_id' => $merchant_id, 'AppID' => $AppID]];

        //订单来源筛选
        $source = $keyword['source'] ?? false;
        if ($source) {
            $where = ['and', $where, ['order.source' => $source]];
        }

        //支付方式
        $pay_type = $keyword['pay_type'] ?? false;
        if ($pay_type) {
            $where = ['and', $where, ['order.pay_type' => $pay_type]];
        }

        //时间区间
        $time_start = $keyword['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'order.created_time', $time_start]];
        } else {
            $order                 = M('order', 'Order')::find()->where(['AppID' => $AppID])->orderBy(['created_time' => SORT_ASC])->one();
            $keyword['time_start'] = $order->created_time;
        }

        $time_end = $keyword['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'order.created_time', $time_end]];
        } else {
            $order               = M('order', 'Order')::find()->where(['AppID' => $AppID])->orderBy(['created_time' => SORT_DESC])->one();
            $keyword['time_end'] = $order->created_time;
        }

        $search_key = $keyword['search_key'] ?? false;
        $search     = $keyword['search'] ?? '';

        //订单编号
        if ($search_key == 'order_sn' && $search) {
            $where = ['and', $where, ['like', 'order.order_sn', $search]];
        }

        //买家昵称
        if ($search_key == 'buyer_nickname' && $search) {
            $sn_list = M('order', 'Order')::find()
                ->alias('order')
                ->joinWith([
                    'user as user',
                ])
                ->where(['like', 'user.nickname', $search])
                ->select('order_sn')
                ->asArray()
                ->all();
            $sn_list = array_column($sn_list, 'order_sn');
            $where   = ['and', $where, ['goods.order_sn' => $sn_list]];
        }

        //买家手机
        if ($search_key == 'buyer_mobile' && $search) {
            $sn_list = M('order', 'Order')::find()
                ->alias('order')
                ->joinWith([
                    'user as user',
                ])
                ->where(['like', 'user.mobile', $search])
                ->select('order_sn')
                ->asArray()
                ->all();
            $sn_list = array_column($sn_list, 'order_sn');
            $where   = ['and', $where, ['goods.order_sn' => $sn_list]];
        }

        //收货人名称
        if ($search_key == 'consignee_name' && $search) {
            $where = ['and', $where, ['like', 'buyer.name', $search]];
        }

        //收货人电话
        if ($search_key == 'consignee_mobile' && $search) {
            $where = ['and', $where, ['like', 'buyer.mobile', $search]];
        }

        //商品名称
        if ($search_key == 'goods_name' && $search) {
            $where = ['and', $where, ['like', 'goods.goods_name', $search]];
        }
        //商品货号
        if ($search_key == 'goods_sn' && $search) {
            $where = ['and', $where, ['like', 'goods.goods_sn', $search]];
        }

        $data = M('order', 'OrderGoods')::find()
            ->alias('goods')
            ->joinWith([
                'buyer as buyer',
                'order as order'=>function($query){
                    $query->with('user');
                },
                'freight as freight',
            ])
            ->where($where)
            ->groupBy(['goods.id'])
            ->asArray()
            ->all();

        $tHeader     = [];
        $filterVal   = [];
        $filter_list = array_merge($filter['order'], $filter['goods']);
        foreach ($filter_list as $v) {
            array_push($tHeader, $v['name']);
            array_push($filterVal, $v['value']);
        }

        $list = [];
        if (empty($filterVal)) {
            Error('未选择导出字段');
        }
        foreach ($data as $value) {
            $res = $this->listBuild($value, $filterVal);
            array_push($list, $res);
        }

        $order_data = [
            'tHeader' => $tHeader,
            'list'    => $list,
        ];

        $ins_data = [
            'conditions'  => to_json($keyword),
            'parameter'   => to_json($filter),
            'order_data'  => to_json($order_data),
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];
        $model = new $this->modelClass;
        $model->setAttributes($ins_data);
        if ($model->save()) {
            return $order_data;
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
                case 'source':
                    $value = $data['order']['source'];
                    break;
                case 'pay_type':
                    $value = $data['order']['pay_type'] == 1 ? '微信' : '支付宝';
                    break;
                case 'order_sn':
                    $value = $data['order']['order_sn'];
                    break;
                case 'created_time':
                    $value = $data['order']['created_time'];
                    $value = $value ? date('Y-m-d H:i:s', $value) : '';
                    break;
                case 'status':
                    switch ($data['order']['status']) {
                        case 100:
                            $value = '待付款';
                            break;
                        case 101:
                            $value = '已取消';
                            break;
                        case 102:
                            $value = '已取消';
                            break;
                        case 103:
                            $value = '已取消';
                            break;
                        case 201:
                            $value = '已付款';
                            break;
                        case 202:
                            $value = '已发货';
                            break;
                        case 203:
                            $value = '已收货';
                            break;
                        case 204:
                            $value = '已完成';
                            break;

                    }
                    break;
                case 'pay_time':
                    $value = $data['order']['pay_time'];
                    $value = $value ? date('Y-m-d H:i:s', $value) : '';
                    break;
                case 'received_time':
                    $value = $data['order']['received_time'];
                    $value = $value ? date('Y-m-d H:i:s', $value) : '';
                    break;
                case 'finish_time':
                    $value = $data['order']['finish_time'];
                    $value = $value ? date('Y-m-d H:i:s', $value) : '';
                    break;
                case 'send_time':
                    $value = $data['order']['send_time'];
                    $value = $value ? date('Y-m-d H:i:s', $value) : '';
                    break;
                case 'buyer':
                    $buyer_info = $data['order']['user'];
                    $value      = $buyer_info['mobile'] ?? '';
                    break;
                case 'consignee_name':
                    $consignee_info = $data['buyer'];
                    $value          = $consignee_info['name'];
                    break;
                case 'consignee_mobile':
                    $consignee_info = $data['buyer'];
                    $value          = $consignee_info['mobile'];
                    break;
                case 'consignee_address':
                    $consignee_info = $data['buyer'];
                    $value          = $consignee_info['province'] . $consignee_info['city'] . $consignee_info['district'] . $consignee_info['address'];
                    break;
                case 'pay_amount':
                    $value = $data['order']['pay_amount'];
                    break;
                case 'freight_amount':
                    $value = $data['order']['freight_amount'];
                    break;
                case 'logistics_company':
                    $value = $data['freight']['logistics_company'] ?? '';
                    break;
                case 'freight_sn':
                    $value = $data['freight']['freight_sn'] ?? '';
                    break;
                case 'user_note':
                    $value = $data['buyer']['note'];
                    break;
                case 'merchant_note':
                    $value = $data['order']['note'];
                    break;
                case 'goods_name':
                    $value = $data['goods_name'];
                    break;
                case 'goods_param':
                    $value = $data['goods_param'];
                    break;
                case 'goods_number':
                    $value = $data['goods_number'];
                    break;
                case 'goods_sn':
                    $value = $data['goods_sn'];
                    break;
                case 'goods_price':
                    $value = $data['goods_price'];
                    break;
                case 'goods_pay_amount':
                    $value = $data['pay_amount'];
                    break;
                case 'goods_cost_price':
                    $value = $data['goods_cost_price'];
                    break;

            }

            array_push($return_data, $value);
        }

        return $return_data;

    }
}
