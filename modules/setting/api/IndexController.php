<?php
/**
 * 设置管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace setting\api;

use framework\common\BasicController;
use setting\models\Waybill;
use Yii;

class IndexController extends BasicController
{
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

    public function actionView()
    {
        return '占位方法';
    }

    public function actionUpdate()
    {
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $behavior     = Yii::$app->request->post('behavior', false);
        switch ($behavior) {
            case 'copyright_information':
                $keyword = 'copyright_information';
                break;

            default:
                $keyword = 'web_setting';
                break;
        }
        $where       = [
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
            'keyword'=>$keyword
        ];
        $data = M()::find()->where($where)->select('keyword,content')->asArray()->one();
        return str2url(to_array($data['content']));
    }

    public function actionDelete()
    {
        return '占位方法';
    }

    /**
     * 获取设置列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];
        $data = M()::find()->where($where)->select('keyword,content')->asArray()->all();
        foreach ($data as &$value) {
            $value['content'] = to_array($value['content']);
        }
        return str2url($data);
    }

    /**
     * 设置搜索
     * @return [type] [description]
     */
    public function actionSearch()
    {
        $keyword     = Yii::$app->request->post('keyword', false);
        $content_key = Yii::$app->request->post('content_key', false);
        if ($keyword == 'addressjson') {
            $json_string = file_get_contents(__DIR__ . '/../app/address.json');
            return to_array($json_string);
        } elseif ($keyword == 'expressjson') {
            $json_string = file_get_contents(__DIR__ . '/../app/express.json');
            return to_array($json_string);
        } elseif ($keyword == 'waybilljson') {
          $newList = [];
          $waybill = Waybill::find()->where(['AppID' => Yii::$app->params['AppID'], 'is_deleted' => 0])
            ->groupBy(['code'])->select('code')->column();
          $json_string = file_get_contents(__DIR__ . '/../app/express.json');
          $array = to_array($json_string);
          foreach ($array as $json) {
            foreach ($waybill as $item) {
              if ($json['code'] == $item) {
                array_push($newList, $json);
              }
            }
          }
          return $newList;
        }
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'keyword'     => $keyword,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];

        $data = M()::find()->where($where)->select('keyword,content')->asArray()->one();

        if ($data) {
            $data['content'] = to_array($data['content']);
            if ($content_key) {
                if (isset($data['content'][$content_key])) {
                    return str2url($data['content'][$content_key]);
                } else {
                    Error('内容不存在');
                }

            }
            return str2url($data);
        } else {
            return null;
        }
    }

    /**
     * 保存设置
     * @return [type] [description]
     */
    public function actionCreate()
    {

        if (!N('keyword') || !N('content')) {
            Error('缺少参数');
        }
        $post        = Yii::$app->request->post();
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];

        $model = M()::find()->where(['merchant_id' => $merchant_id, 'AppID' => $AppID, 'keyword' => $post['keyword']])->one();

        if (empty($model)) {
            $model              = M('setting', 'Setting', true);
            $model->keyword     = $post['keyword'];
            $model->merchant_id = $merchant_id;
            $model->AppID       = $AppID;
        }

        $post['content'] = url2str($post['content']);
        $model->content  = to_json($post['content']);
        if ($model->save()) {
            return true;
        } else {
            Error('保存失败');
        }
    }
}
