<?php

namespace app\controllers;

use app\forms\ImageTools;
use framework\common\AppAsset;
use setting\models\Setting;
use Yii;
use yii\web\Controller;

class AdminController extends Controller
{
    use ImageTools;
    public $layout = false;

    /**
     * Displays homepage.
     * @return string
     */
    public function actionIndex()
    {
        $industry = "admin";
        $alias    = 'admin';

        $AppAsset = $this->compiling($industry, $alias, 'vue');

        $param = ["AppAsset" => $AppAsset, 'industry' => $industry, 'alias' => $alias];

        $name = '小店';
        $res  = Setting::findOne(['AppID' => '98c08c25f8136d590c', 'keyword' => 'setting_collection']);
        if ($res) {
            $info = to_array($res['content']);
            $name = $info['store_setting']['name'] ?? '小店';
        }
        $param['title'] = $name . '--后台管理';
        return $this->render('index', $param);
    }

    /**
     * 处理编译
     * @param  string $name  [description]
     * @param  string $alias [description]
     * @return [type]        [description]
     */
    public function compiling($name = '', $alias = '', $type = 'vue')
    {
        $view     = \Yii::$app->view;
        $config   = array('industry' => $name, 'alias' => $alias, 'type' => $type);
        $AppAsset = new AppAsset($config);
        $am       = $AppAsset->getAssetManager();
        $AppAsset->publish($am);
        $AppAsset->registerAssetFiles($view);
        return $AppAsset;
    }
}
