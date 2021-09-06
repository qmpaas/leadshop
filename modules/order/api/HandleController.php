<?php
/**
 * 订单控制器
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace order\api;

use framework\common\BasicController;
use setting\models\Waybill;
use Yii;
use yii\data\ActiveDataProvider;

class HandleController extends BasicController
{

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['update']);
        return $actions;
    }

    /**
     * 批量发货记录
     */
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

        $get = Yii::$app->request->get();

        //时间区间
        $time_start = $get['time_start'] ?? false;
        if ($time_start > 0) {
            $where = ['and', $where, ['>=', 'created_time', $time_start]];
        }
        $time_end = $get['time_end'] ?? false;
        if ($time_end > 0) {
            $where = ['and', $where, ['<=', 'created_time', $time_end]];
        }

        $data = new ActiveDataProvider(
            [
                'query'      => M('order', 'OrderBatchHandle')::find()->where($where)->select('id,handle_sn,order_number,success_number,created_time')->orderBy(['created_time' => SORT_DESC])->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        return $data;
    }

    /**
     * 批量发货详情
     */
    public function actionView()
    {
        $id = Yii::$app->request->get('id', 0);
        $id = intval($id);

        $result = M('order', 'OrderBatchHandle')::find()->where(['id' => $id])->select('error_data')->asArray()->one();

        if (empty($result)) {
            Error('内容不存在');
        }

        $result['error_data'] = to_array($result['error_data']);

        return $result;
    }

    /**
     * 不允许删除加的占位方法
     */
    public function actionDelete()
    {
        Error('违规操作');
    }

    /**
     * 批量发货
     */
    public function actionCreate()
    {
        $behavior = Yii::$app->request->get('behavior', 'deliver');

        if ($behavior == 'waybill') {
            $list      = Yii::$app->request->post('list');
            $waybillId = Yii::$app->request->post('waybill_id');
        } else {
            $list = Yii::$app->request->post();
        }

        if (!is_array($list)) {
            Error('数据格式错误');
        }

        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];

        $received_time = 0;
        $trade_setting = StoreSetting('setting_collection', 'trade_setting');
        if ($trade_setting) {
            if ($trade_setting['received_time']) {
                $received_time = (float) $trade_setting['received_time'] * 24 * 60 * 60 + time();
            }
        }
        $send_time = time();

        $order_number   = 0; //订单数
        $success_number = 0; //成功数
        $error_data     = []; //失败列表
        $transaction    = Yii::$app->db->beginTransaction(); //启动数据库事务
        foreach ($list as $v) {
            $order_number++;
            $v[0]  = trim($v[0]); //去除订单编号两边可能存在的空字符
            $model = M('order', 'Order')::find()->where(['order_sn' => $v[0], 'merchant_id' => $merchant_id, 'AppID' => $AppID])->one();
            if (empty($model)) {
                array_push($v, '该单号订单不存在');
                array_push($error_data, $v);
                continue;
            }

            //只有待发货商品才能发货
            if ($model->status !== 201) {
                array_push($v, '该订单不是待发货状态');
                array_push($error_data, $v);
                continue;
            }

            $after_check = M('order', 'OrderAfter')::find()->where(['order_sn' => $v[0]])->exists();
            if ($after_check) {
                array_push($v, '该订单处于售后中');
                array_push($error_data, $v);
                continue;
            }

            $model->received_time = $received_time;
            $model->send_time     = $send_time;
            $model->status        = 202;

            if ($behavior == 'waybill') {
                try {
                    /**@var Waybill $waybill*/
                    $waybill = Waybill::find()->where(['id' => $waybillId])->one();
                    if (!$waybill) {
                        Error('发货地址不存在');
                    }
                    $data = (new \app\components\WaybillPrint())->query([
                        'order_sn'   => $v[0],
                        'waybill_id' => $waybillId,
                    ]);
                    $json         = file_get_contents(__DIR__ . '/../../setting/app/express.json');
                    $jsonArray    = array_column(to_array($json), null, 'code');
                    $companyName  = $jsonArray[$waybill->code]['name'] ?? '';
                    $freight_data = [
                        'order_sn'          => $v[0],
                        'type'              => 3,
                        'logistics_company' => $companyName,
                        'freight_sn'        => $data['freight_sn'],
                    ];
                } catch (\Exception $e) {
                    array_push($v, '获取电子面单失败' . $e->getMessage());
                    array_push($error_data, $v);
                    continue;
                }
            } else {
                $freight_data = [
                    'order_sn'          => $v[0],
                    'type'              => $v[2] ? 1 : 2,
                    'logistics_company' => $v[1],
                    'freight_sn'        => $v[2],
                ];
            }
            $freight_model = M('order', 'OrderFreight', true);
            $freight_model->setAttributes($freight_data);
            if ($freight_model->validate()) {
                if ($freight_model->save() && $model->save()) {
                    $success_number++;
                } else {
                    array_push($v, '发货失败');
                    array_push($error_data, $v);
                    continue;
                }
            } else {
                array_push($v, '物流信息不完整');
                array_push($error_data, $v);
                continue;
            }

        }

        $handle_model = M('order', 'OrderBatchHandle', true);
        $handle_sn    = date('YmdHis');
        $handle_data  = [
            'handle_sn'      => $handle_sn,
            'order_number'   => $order_number,
            'success_number' => $success_number,
            'error_data'     => to_json($error_data),
            'merchant_id'    => $merchant_id,
            'AppID'          => $AppID,
        ];

        $handle_model->setAttributes($handle_data);
        if ($handle_model->validate()) {
            if ($handle_model->save()) {
                $transaction->commit(); //事务执行
                return true;
            } else {
                $transaction->rollBack(); //事务回滚
                Error('发货失败');
            }
        } else {
            $transaction->rollBack(); //事务回滚
            return $handle_model;
            Error('发货失败');
        }

    }
}
