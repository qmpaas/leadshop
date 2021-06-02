<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-01-05 10:17:03
 */
namespace framework\common;

use yii\web\AssetBundle;

class InstallAsset extends AssetBundle
{

    public $sourcePath     = '@app/install';
    public $css            = [];
    public $js             = [];
    public $industry       = "";
    public $publishOptions = [];

    /**
     * Registers the asset manager being used by this view object.
     * @return \yii\web\AssetManager the asset manager. Defaults to the "assetManager" application component.
     */
    public function getAssetManager()
    {
        return \Yii::$app->getAssetManager();
    }

    public function init()
    {
        //获取要拼接的行业
        $industry = $this->industry;
        $dir_css  = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/install/css';
        $dir_js   = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/install/js';
        //读取对应的数据信息
        $list_css = read_all_dir($dir_css, \Yii::$app->basePath . '/install/');
        $list_js  = read_all_dir($dir_js, \Yii::$app->basePath . '/install/');
        //返回要执行的资源列表
        $this->css            = $list_css['file'] ?? [];
        $this->js             = $list_js['file'] ?? [];
        $this->publishOptions = [
            'only' => [
                'css/*',
                'fonts/*',
                'js/*',
            ],
        ];
        //返回父级信息
        parent::init();
    }

}
