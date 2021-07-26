<?php

namespace setting\api;

use framework\common\BasicController;
use setting\models\Waybill;
use Yii;
use yii\data\ActiveDataProvider;

class WaybillController extends BasicController
{
    public $modelClass = 'setting\models\Waybill';

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

    /**
     *
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $merchant_id = 1;
        //获取头部信息
        $headers = Yii::$app->getRequest()->getHeaders();
        //获取分页信息
        $pageSize = $headers->get('X-Pagination-Per-Page') ?? 10;
        $behavior = Yii::$app->request->get('behavior', 'list');
        $where = ['AppID' => Yii::$app->params['AppID'], 'is_deleted' => 0, 'merchant_id' => $merchant_id];
        if ($behavior == 'option') {
            $code = Yii::$app->request->get('code');
            if (!$code) {
                Error('请选择快递公司');
            }
            $where['code'] = $code;
        }
      $data =  new ActiveDataProvider(
            [
                'query' => $this->modelClass::find()->where($where)->asArray(),
                'pagination' => ['pageSize' => $pageSize, 'validatePage' => false],
            ]
        );

      $list = $data->getModels();
      $json = file_get_contents(__DIR__.'/../app/express.json');
      $jsonArray = array_column(to_array($json), null, 'code');
      foreach ($list as &$v) {
        $v['company_name'] = $jsonArray[$v['code']]['name'] ?? '';
      }
      $data->setModels($list);

      return $data;
    }

    public function actionView()
    {
        $id = \Yii::$app->request->get('id', 0);
        $waybill = Waybill::findOne(['id' => $id, 'AppID' => Yii::$app->params['AppID'], 'is_deleted' => 0]);
        if (!$waybill) {
            Error('数据不存在');
        }
        return $waybill;
    }

    public function actionCreate()
    {
        $behavior = Yii::$app->request->get('behavior', 'create');
        switch ($behavior) {
            case 'create':
                return $this->create();
            case 'select':
                return $this->select();
            default:
                Error('未定义操作');
                break;
        }

    }

    private function create()
    {
        $AppID = Yii::$app->params['AppID'];
        $waybill = new Waybill();
        $waybill->attributes = \Yii::$app->request->post();
        $waybill->AppID = $AppID;
        $waybill->merchant_id = 1;
        if (!$waybill->save()) {
            Error($waybill->getErrorMsg());
        }
        return true;
    }

    private function select()
    {
        $orderSn = Yii::$app->request->post('order_sn', false);
        $waybillId = Yii::$app->request->post('waybill_id', false);
        if (!$orderSn || !$waybillId) {
            Error('参数不正确');
        }
        return (new \app\components\WaybillPrint())->query([
            'order_sn' => $orderSn,
            'waybill_id' => $waybillId
        ]);
    }

    public function actionUpdate()
    {
        $id = \Yii::$app->request->get('id', false);
        $waybill = Waybill::findOne(['id' => $id, 'AppID' => Yii::$app->params['AppID'], 'is_deleted' => 0]);
        if (!$waybill) {
            Error('数据不存在');
        }
        $waybill->attributes = \Yii::$app->request->post();
        if (!$waybill->save()) {
            Error($waybill->getErrorMsg());
        }
        return true;
    }
}
