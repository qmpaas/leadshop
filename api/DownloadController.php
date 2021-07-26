<?php

namespace leadmall\api;

use app\forms\ImageTools;
use fitment\models\Fitment;
use framework\common\BasicController;
use yii\web\Response;

class DownloadController extends BasicController
{
    use ImageTools;

    public function actionIndex()
    {
        $enableLive = \Yii::$app->request->get('enable_live', 0);
        $appSrcFile = \Yii::$app->basePath . '/applet/app.zip';
        $appSrcFile = str_replace('\\', '/', $appSrcFile);
        if (!file_exists($appSrcFile)) {
            throw new \Exception('app.zip文件不存在。');
        }
        $apiRoot = \Yii::$app->request->hostInfo
            . rtrim(\Yii::$app->request->baseUrl, '/');
        $apiRoot = str_replace('http://', 'https://', $apiRoot) . '/index.php';
        $AppID = \Yii::$app->params['AppID'];
        $siteInfoContent = <<<EOF
module.exports  = {
    "siteroot":"{$apiRoot}",
}
EOF;
        $zipArchive = new \ZipArchive();
        $zipArchive->open($appSrcFile);
        $zipArchive->addFromString('siteinfo.js', $siteInfoContent);
        $appJson = $zipArchive->getFromName('app.json');
        $appJson = json_decode($appJson, true);
        if (!is_array($appJson)) {
            Error('无法解析app.json的内容。');
        }
        if (empty($appJson['plugins'])) {
            unset($appJson['plugins']);
        }
        if (!empty($appJson['subPackages'])) { // 处理分包的插件
            foreach ($appJson['subPackages'] as &$package) {
                if ($package['root'] == 'pages/live') {
                    if (!$enableLive) {
                        unset($package['plugins']);
                    } else {
                        $package['plugins'] = [
                            'live-player-plugin' => [
                                'version' => '1.3.1',
                                'provider' => 'wx2b03c6e691cd7370'
                            ]
                        ];
                    }
                }
            }
        }

        $data = Fitment::find()->where(['AppID' => $AppID, 'keyword' => 'tabbar'])->select('keyword,content')->asArray()->one();
        if ($data) {
            $tabBar = str2url(to_array($data['content']));
        } else {
            $basePath = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/static/images/tabbar/';
            $navHomeNormal = $basePath . 'nav_home_normal.png';
            $navHomeSelected = $basePath . 'nav_home_selected.png';
            $navPersonalNormal = $basePath . 'nav_personal-center_normal.png';
            $navPersonalSelected = $basePath . 'nav_personal-center_selected.png';
            $navShoppingNormal = $basePath . 'nav_shopping-cart_normal.png';
            $navShoppingSelected = $basePath . 'nav_shopping-cart_selected.png';
            $navClassNormal = $basePath . 'nav_classification_normal.png';
            $navClassSelected = $basePath . 'nav_classification_selected.png';
            $tabBar = <<<EOF
"{\"tabbarStyle\":2,\"background_color\":\"#FFFFFF\",\"inactive_color\":\"#1A1818\",\"active_color\":\"#f5212d\",\"data\":[{\"text\":\"首页\",\"page\":\"setup\",\"iconPath\":\"{$navHomeNormal}\",\"selectedIconPath\":\"{$navHomeSelected}\",\"link\":{\"name\":\"店铺首页\",\"path\":\"/pages/index/index\",\"param\":{},\"index\":0,\"extend\":false}},{\"pagePath\":\"\",\"iconPath\":\"{$navClassNormal}\",\"selectedIconPath\":\"{$navClassSelected}\",\"text\":\"分类\",\"link\":{\"name\":\"全部商品\",\"path\":\"/pages/goods/list\",\"param\":{},\"index\":1,\"extend\":false}},{\"pagePath\":\"\",\"iconPath\":\"{$navShoppingNormal}\",\"selectedIconPath\":\"{$navShoppingSelected}\",\"text\":\"购物车\",\"link\":{\"name\":\"购物车\",\"path\":\"/pages/cart/index\",\"param\":{},\"index\":4,\"extend\":false}},{\"pagePath\":\"\",\"iconPath\":\"{$navPersonalNormal}\",\"selectedIconPath\":\"{$navPersonalSelected}\",\"text\":\"我\",\"link\":{\"name\":\"个人中心\",\"path\":\"/pages/user/index\",\"param\":{},\"index\":5,\"extend\":false}}]}"
EOF;
            $tabBar = to_array(to_array($tabBar));
        }
        $newBar = [
            "color" => $tabBar["inactive_color"],
            "selectedColor" => $tabBar["active_color"],
            "backgroundColor" => $tabBar["background_color"],
            "list" => [
            ],
        ];
        $temp = \Yii::$app->basePath . '/web/temp/';
        if (!is_dir($temp)) {
            mkdir($temp);
        }
        foreach ($tabBar["data"] as $k => $item) {
            if ($tabBar['tabbarStyle'] == 2) {
                $icon = 'icon_' . md5($k) . '.' . $this->getImageExtension($item["iconPath"]);
                $iconPath = $temp . $icon;
                $this->downloadFile($item["iconPath"], $iconPath);
                $this->zoomImage($iconPath, $iconPath);
                $zipArchive->addFile($iconPath, 'static/images/icon/' . $icon);
                $selectIcon = 'select_icon_' . md5($k) . '.' . $this->getImageExtension($item["selectedIconPath"]);
                $selectIconPath = $temp . $selectIcon;
                $this->downloadFile($item["selectedIconPath"], $selectIconPath);
                $this->zoomImage($selectIconPath, $selectIconPath);
                $zipArchive->addFile($selectIconPath, 'static/images/icon/' . $selectIcon);
                $newItem["iconPath"] = '/static/images/icon/' . $icon;
                $newItem["selectedIconPath"] = '/static/images/icon/' . $selectIcon;
            }
            $newItem["pagePath"] = trim($item["link"]["path"], '/');
            $newItem["text"] = $item["text"];
            $newBar["list"][] = $newItem;
        }
        $appJson['tabBar'] = $newBar;
        $appJson = json_encode($appJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $zipArchive->addFromString('app.json', $appJson);
        $zipArchive->close();
        \Yii::$app->response->format = Response::FORMAT_RAW;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($appSrcFile));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($appSrcFile));
        ob_clean();
        flush();
        readfile($appSrcFile);
        exit;
    }
}
