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
        $behavior    = Yii::$app->request->post('behavior', false);
        switch ($behavior) {
            case 'copyright_information':
                $keyword = 'copyright_information';
                break;

            default:
                $keyword = 'web_setting';
                break;
        }
        return StoreSetting($keyword);
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
        return StoreSetting();
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
            $array       = to_array($json_string);
            foreach ($array as $json) {
                foreach ($waybill as $item) {
                    if ($json['code'] == $item) {
                        array_push($newList, $json);
                    }
                }
            }
            return $newList;
        }
        $data = StoreSetting($keyword, $content_key);
        if (!$content_key) {
            $new_data            = [];
            $new_data['keyword'] = $keyword;
            $new_data['content'] = $data;
            $data                = $new_data;
        }

        return $data;
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

        if ($post['keyword'] === 'commission_setting') {
            if ($post['content']['count_rules'] === 2) {
                $check = M('goods', 'Goods')::findOne(['is_deleted' => 0, 'is_promoter' => 1, 'max_profits' => null]);
                if ($check) {
                    return ['status' => 1];
                }
            }
        }

        if ($post['keyword'] === 'promoter_setting') {
            if ($post['content']['bind_type'] === 2) {
                if (isset($model->content)) {
                    $content = to_array($model->content);
                    if ($content['bind_type'] !== 2) {
                        $post['content']['protect_time'] = time();
                    }
                } else {
                    $post['content']['protect_time'] = time();
                }
            }
        }

        $post['content'] = url2str($post['content']);
        $model->content  = to_json($post['content']);
        if ($model->save()) {
            return ['status' => 0];
        } else {
            Error('保存失败');
        }
    }
}
