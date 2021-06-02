<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-04-26 15:39:46
 */
namespace framework\common;

use Yii;
use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{

    public $sourcePath = '@app/views';
    public $css        = [];
    public $js         = [];
    public $industry   = "";
    public $alias      = "";
    public $type       = "";

    public $publishOptions = [];

    /**
     * Registers the asset manager being used by this view object.
     * @return \yii\web\AssetManager the asset manager. Defaults to the "assetManager" application component.
     */
    public function getAssetManager()
    {
        $AssetManager = \Yii::$app->getAssetManager();

        if ($this->type == 'uni') {
            $this->sourcePath           = '@app/views/wechat';
            $AssetManager->hashCallback = function ($value = '') {
                return "wechat";
            };
        }

        if ($this->type == 'vue') {
            $this->sourcePath           = '@app/views/admin';
            $AssetManager->hashCallback = function ($value = '') {
                return "admin";
            };
        }

        if ($this->type == 'ins') {
            $this->sourcePath           = '@app/views/install';
            $AssetManager->hashCallback = function ($value = '') {
                return "install";
            };
        }
        return $AssetManager;
    }

    public function init()
    {
        if ($this->type == 'uni') {
            $this->getUniInit('wechat');
        }

        if ($this->type == 'vue') {
            $this->getVueInit('admin');
        }

        if ($this->type == 'ins') {
            $this->getInsInit('install');
        }

        //返回父级信息
        parent::init();
    }

    public function getUniInit()
    {
        $tab_css = Yii::$app->basePath . '/views/' . $this->industry . '/static';
        $dir_css = Yii::$app->basePath . '/views/' . $this->industry . '/static/css';
        $dir_js  = Yii::$app->basePath . '/views/' . $this->industry . '/static/js';

        //读取对应的数据信息
        $list_tab = read_all_dir($tab_css, Yii::$app->basePath . "/views/" . $this->industry . "/");
        $list_css = read_all_dir($dir_css, Yii::$app->basePath . "/views/" . $this->industry . "/");
        $list_js  = read_all_dir($dir_js, Yii::$app->basePath . "/views/" . $this->industry . "/");

        //处理重叠CSS
        $list_css['file'] = array_merge($list_tab['file'], $list_css['file']);

        //返回要执行的资源列表
        $this->css = $list_css['file'];
        $this->js  = $list_js['file'];

        $this->publishOptions = [
            'only' => [
                "{$this->industry}/css/*",
                "{$this->industry}/fonts/*",
                "{$this->industry}/js/*",
            ],
        ];

    }

    public function getVueInit()
    {
        $dir_css = Yii::$app->basePath . '/views/' . $this->industry . '/css';
        $dir_js  = Yii::$app->basePath . '/views/' . $this->industry . '/js';
        //读取对应的数据信息
        $list_css = read_all_dir($dir_css, Yii::$app->basePath . "/views/" . $this->industry . "/");
        $list_js  = read_all_dir($dir_js, Yii::$app->basePath . "/views/" . $this->industry . "/");

        //返回要执行的资源列表
        $this->css = $list_css['file'];
        $this->js  = $list_js['file'];

        $this->publishOptions = [
            'only' => [
                "{$this->industry}/css/*",
                "{$this->industry}/fonts/*",
                "{$this->industry}/js/*",
            ],
        ];
    }

    public function getInsInit()
    {
        $dir_css = Yii::$app->basePath . '/views/' . $this->industry . '/css';
        $dir_js  = Yii::$app->basePath . '/views/' . $this->industry . '/js';
        //读取对应的数据信息
        $list_css = read_all_dir($dir_css, Yii::$app->basePath . "/views/" . $this->industry . "/");
        $list_js  = read_all_dir($dir_js, Yii::$app->basePath . "/views/" . $this->industry . "/");

        //返回要执行的资源列表
        $this->css = $list_css['file'];
        $this->js  = $list_js['file'];

        $this->publishOptions = [
            'only' => [
                "{$this->industry}/css/*",
                "{$this->industry}/fonts/*",
                "{$this->industry}/js/*",
            ],
        ];
    }
}
