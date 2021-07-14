<?php
/**
 * 售后订单控制器
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace order\app;

use framework\common\BasicController;
use setting\models\Setting;
use Yii;
use yii\data\ActiveDataProvider;

class AfterController extends BasicController
{
    public $modelClass = 'order\models\OrderAfter';
    /**
     * 重写父类
     * @return [type] [description]
     */
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

        //订单分组
        $UID   = Yii::$app->user->identity->id;
        $where = ['after.UID' => $UID, 'after.is_deleted' => 0];

        $orderBy = ['after.created_time' => SORT_DESC];

        $data = new ActiveDataProvider(
            [
                'query'      => $this->modelClass::find()
                    ->alias('after')
                    ->joinWith([
                        'buyer as buyer',
                        'goods as goods',
                    ])
                    ->where($where)
                    ->groupBy(['after.id'])
                    ->orderBy($orderBy)
                    ->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

        $list = $data->getModels();
        foreach ($list as $key => &$value) {
            $value['images'] = to_array($value['images']);
        }
        //将所有返回内容中的本地地址代替字符串替换为域名
        $list = str2url($list);
        $data->setModels($list);
        return $data;
    }

    /**
     * 后台订单列表
     * @return [type] [description]
     */
    public function actionSearch()
    {

    }

    /**
     * 订单详情
     * @return [type] [description]
     */
    public function actionView()
    {
        $id       = Yii::$app->request->get('id', false);
        $behavior = Yii::$app->request->get('behavior', false);
        if ($behavior === 'order_goods') {
            $where = ['order_goods_id' => $id, 'is_deleted' => 0];
        } else {
            $where = ['id' => $id, 'is_deleted' => 0];
        }

        $result = $this->modelClass::find()
            ->where($where)
            ->with([
                'buyer',
                'goods',
            ])
            ->asArray()
            ->one();
        if ($result) {
            $result['images']                = to_array($result['images']);
            $result['return_address']        = to_array($result['return_address']);
            $result['user_freight_info']     = to_array($result['user_freight_info']);
            $result['merchant_freight_info'] = to_array($result['merchant_freight_info']);
            return str2url($result);
        } else {
            Error('售后不存在');
        }

    }

    /**
     * 添加售后订单
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $order_goods_id = Yii::$app->request->post('order_goods_id', false);

        $o_g_info = M('order', 'OrderGoods')::findOne($order_goods_id);
        if (empty($o_g_info)) {
            Error('订单不存在');
        }

        $order_sn = $o_g_info->order_sn;

        $order_info = M('order', 'Order')::find()->where(['order_sn' => $order_sn])->one();

        if ($order_info->status !== 201 && $order_info->status !== 202 && $order_info->status !== 203) {
            Error('当前状态不支持售后');
        }

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务

        $merchant_id      = 1;
        $AppID            = Yii::$app->params['AppID'];
        $source           = Yii::$app->params['AppType'];
        $UID              = Yii::$app->user->identity->id;
        $post             = Yii::$app->request->post();
        $post             = url2str($post);
        $post['images']   = to_json($post['images']);
        $post['order_sn'] = $order_sn;
        $post['UID']      = $UID;
        $post['AppID']    = $AppID;
        $post['source']   = $source;

        ///判断是否要创建积分售后
        if ($this->plugins("task", "status") && $order_info->type = "task") {
            $post['return_score_type'] = $this->plugins("task", "config.integral_return");
        }
        // $post['return_score_type'] = 1;

        $post['merchant_id'] = $merchant_id;

        //判断是否是第一次提交被拒绝,是则修改提交信息,不是则创建一条记录
        $model = $this->modelClass::find()->where(['order_goods_id' => $order_goods_id])->one();
        if ($model) {
            if ($model->status !== 101) {
                Error('非法操作');
            }
            $post['status'] = 102;
            $process        = to_array($model->process);
            array_unshift($process, ['label' => '买家', 'content' => '再次申请售后 ' . date('Y-m-d H:i:s', time())]);
        } else {
            $process = [
                [
                    "label"   => "买家",
                    "content" => "申请售后 " . date('Y-m-d H:i:s', time()),
                ],
            ];
            $post['after_sn'] = get_sn('asn');
            $model            = new $this->modelClass;
        }
        $post['process'] = to_json($process);

        $model->setScenario('create');
        $model->setAttributes($post);
        if ($model->validate()) {

            if ($order_info->status === 201 && $post['type'] !== 0) {
                Error('该订单只支持退款');
            }

            if ($o_g_info->goods_number < $post['return_number']) {
                Error('数量超限制');
            }

            if ($model->save()) {
                //修改订单售后状态
                // $order_info->after_sales = 1;
                // $order_info->finish_time = '';
                // $order_res               = $order_info->save();
                //修改订单商品售后状态
                $o_g_info->after_sales = 1;
                $o_g_res               = $o_g_info->save();
                if ($o_g_res) {
                    $transaction->commit(); //事务执行
                    $status = $model->status ? $model->status : 100;

                    $setting = Setting::findOne(['AppID' => Yii::$app->params['AppID'], 'merchant_id' => 1, 'keyword' => 'sms_setting', 'is_deleted' => 0]);
                    if ($setting && $setting['content']) {
                        $mobiles                  = json_decode($setting['content'], true);
                        $this->module->event->sms = [
                            'type'   => 'order_refund',
                            'mobile' => $mobiles['mobile_list'] ?? [],
                            'params' => [],
                        ];
                        $this->module->trigger('send_sms');
                    }

                    return ['status' => $status];
                } else {
                    $transaction->rollBack(); //事务回滚
                    Error('提交失败');
                }
            } else {
                Error('提交失败');
            }

        }

        return $model;
    }

    /**
     * 编辑中转
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        //获取操作
        $behavior = Yii::$app->request->get('behavior', '');

        switch ($behavior) {
            case 'cancel': //取消售后
                return $this->cancel();
                break;
            case 'salesexchange': //换货
                return $this->salesexchange();
                break;
            case 'received': //换货确认收货
                return $this->received();
                break;
            default:
                Error('未定义操作');
                break;
        }
    }

    /**
     * 换货
     * @return [type] [description]
     */
    public function salesexchange()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('售后订单不存在');
        }

        if ($model->status !== 131 && $model->status !== 121) {
            Error('非法操作');
        }

        $time              = date('Y-m-d H:i:s', time());
        $user_freight_info = Yii::$app->request->post('user_freight_info', []);
        if (empty($user_freight_info['logistics_company']) || empty($user_freight_info['freight_sn'])) {
            Error('请填写物流公司和单号');
        }
        $user_freight_info['time'] = $time;
        $user_freight_info         = url2str($user_freight_info);
        $model->user_freight_info  = to_json($user_freight_info);
        $model->status             = $model->status === 121 ? 122 : 132; //根据退货还是换货  改变不同状态
        $model->salesexchange_time = time();

        $process = to_array($model->process);
        array_unshift($process, ['label' => '买家', 'content' => '换货 ' . $time]);
        $model->process = to_json($process);

        if ($model->save()) {
            return ['status' => $model->status];
        } else {
            Error('操作失败');
        }
    }

    /**
     * 换货确认收货
     * @return [type] [description]
     */
    public function received()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('售后订单不存在');
        }

        if ($model->status !== 133) {
            Error('非法操作');
        }

        $model->status = 200;
        $process       = to_array($model->process);
        array_unshift($process, ['label' => '买家', 'content' => '已收货 ' . date('Y-m-d H:i:s', time())]);
        $model->process = to_json($process);

        if ($model->save()) {
            return ['status' => $model->status];
        } else {
            Error('操作失败');
        }
    }

    /**
     * 取消售后
     * @return [type] [description]
     */
    public function cancel()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('售后订单不存在');
        }

        //可取消的所属状态
        if (!in_array($model->status, [100, 101, 102, 111, 121, 131])) {
            Error('此售后订单不可取消');
        }

        $transaction = Yii::$app->db->beginTransaction(); //启动数据库事务

        // $after_count = $this->modelClass::find()->where(['order_sn' => $model->order_sn])->count();
        // //当订单将不存在售后时  状态改为正常
        // if ($after_count > 1) {
        //     $res1 = true;
        // } else {
        //     $res1 = M('order', 'Order')::updateAll(['after_sales' => 0], ['order_sn' => $model->order_sn]);
        // }
        $res2 = M('order', 'OrderGoods')::updateAll(['after_sales' => 0], ['id' => $model->order_goods_id]);
        $res  = $model->delete();
        if ($res && $res2) {
            $transaction->commit();
            return true;
        } else {
            $transaction->rollBack();
            Error('操作失败');
        }
    }

    /**
     * 删除售后
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $id    = Yii::$app->request->get('id', false);
        $model = $this->modelClass::findOne($id);
        if (empty($model)) {
            Error('售后订单不存在');
        }

        if ($model->status < 200) {
            Error('非法操作');
        }

        $model->deleted_time = time();
        $model->is_deleted   = 1;
        if ($model->save()) {
            return true;
        } else {
            Error('操作失败');
        }
    }
}
