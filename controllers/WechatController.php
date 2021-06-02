<?php

namespace app\controllers;

use app\forms\ImageTools;
use fitment\models\Fitment;
use framework\common\AppAsset;
use Yii;
use yii\web\Controller;

class WechatController extends Controller
{
    use ImageTools;
    public $layout = false;

    /**
     * Displays homepage.
     * @return string
     */
    public function actionIndex()
    {
        $industry = "wechat";
        $alias    = 'wechat';

        $AppAsset = $this->compiling($industry, $alias, 'uni');

        $param = ["AppAsset" => $AppAsset, 'industry' => $industry, 'alias' => $alias];

        $data = Fitment::find()->where(['AppID' => '98c08c25f8136d590c', 'keyword' => 'tabbar'])->select('keyword,content')->asArray()->one();
        if ($data) {
            $tabBar = str2url(to_array($data['content']));

            $newBar = [
                "color"           => $tabBar["inactive_color"],
                "selectedColor"   => $tabBar["active_color"],
                "backgroundColor" => $tabBar["background_color"],
                "list"            => [
                ],
            ];
            $temp = Yii::$app->basePath . '/web/static/images/icon';
            if (!is_dir($temp)) {
                make_dir($temp);
            }
            foreach ($tabBar["data"] as $k => $item) {
                if ($tabBar['tabbarStyle'] == 2) {
                    $icon     = 'icon_' . md5($k) . '.' . $this->getImageExtension($item["iconPath"]);
                    $iconPath = $temp . $icon;
                    $this->downloadFile($item["iconPath"], $iconPath);
                    $this->zoomImage($iconPath, $iconPath);
                    copy($iconPath, Yii::$app->basePath . '/web/static/images/icon/' . $icon);
                    $selectIcon     = 'select_icon_' . md5($k) . '.' . $this->getImageExtension($item["selectedIconPath"]);
                    $selectIconPath = $temp . $selectIcon;
                    $this->downloadFile($item["selectedIconPath"], $selectIconPath);
                    $this->zoomImage($selectIconPath, $selectIconPath);
                    copy($selectIconPath, Yii::$app->basePath . '/web/static/images/icon/' . $selectIcon);
                    $newItem["iconPath"]         = '/static/images/icon/' . $icon;
                    $newItem["selectedIconPath"] = '/static/images/icon/' . $selectIcon;
                }
                $newItem["pagePath"] = $item["link"]["path"];
                $newItem["text"]     = $item["text"];
                $newBar["list"][]    = $newItem;
            }
            $param['tabBar'] = $newBar;
        }
        $param['title'] = "Leadshop";
        return $this->render('index', $param);
    }

    /**
     * 处理编译
     * @param  string $name  [description]
     * @param  string $alias [description]
     * @return [type]        [description]
     */
    public function compiling($name = '', $alias = '', $type = 'uni')
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
