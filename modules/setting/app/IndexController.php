<?php
/**
 * 设置管理
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace setting\app;

use framework\common\BasicController;
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

    public function actionCreate()
    {
        return '占位方法';
    }

    public function actionUpdate()
    {
        return '占位方法';
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
        $data = StoreSetting();
        $new_data = [];
        foreach ($data as $value) {
            $new_data[$value['keyword']] = str2url($value['content']);
        }
        return $new_data;
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
            $json_string = file_get_contents(__DIR__.'/address.json');
            return to_array($json_string);
        } elseif ($keyword == 'expressjson') {
            $json_string = file_get_contents(__DIR__.'/express.json');
            return to_array($json_string);
        }
        $merchant_id = 1;
        $AppID       = Yii::$app->params['AppID'];
        $where       = [
            'keyword'     => $keyword,
            'merchant_id' => $merchant_id,
            'AppID'       => $AppID,
        ];

        $data = StoreSetting($keyword,$content_key);
        if (!$content_key) {
            $new_data = [];
            $new_data['keyword'] = $keyword;
            $new_data['content'] = $data;
            $data = $new_data;
        }

        return $data;
    }
}
