<?php


namespace leadmall\api;


use framework\common\BasicController;

class CleanController extends BasicController
{
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    public function actionIndex()
    {
        @\Yii::$app->cache->flush();
        $this->clear([\Yii::$app->runtimePath . '/wechat-cache']);
        $this->clear([\Yii::$app->basePath . '/web/temp']);
        //请理assets资源文件夹
        $assetsPath = \Yii::$app->basePath . '/web/assets';
        $assets = scandir($assetsPath);
        foreach ($assets as $link) {
            if (is_link($assetsPath . '/' . $link)) {
                unlink($assetsPath . '/' . $link);
            }
        }
        return true;
    }

    /**
     * 请理文件
     * @param array $paths
     */
    private function clear($paths = [])
    {
        foreach ($paths as $path) {
            if (file_exists($path) && is_readable($path) && is_writable($path)) {
                @remove_dir($path);
            }
        }
    }
}