<?php
/**
 * 插件
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace leadmall\app;

use basics\common\BasicsController as BasicsModules;
use leadmall\Map;
use Yii;

/**
 * 执行插件
 * include =>需要加载的插件名称
 *
 */
class PluginController extends BasicsModules implements Map
{

    /**
     * 处理接口白名单
     * @var array
     */
    const __APITYPE__ = "app";

    /**
     * 获取方法
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $include = Yii::$app->request->get('include', '');
        if ($include != 'empty') {
            return parent::actionIndex();
        }
        $PluginModel = "system\models\Plugin";
        //$sql_array  = $PluginModel::find()->all();
        $path         = Yii::$app->basePath . "/plugins";
        $path_array   = readDirList($path);
        $config_array = [];
        //循环获取数据
        foreach ($path_array as $key => $value) {
            $array = $this->get_config($value);
            if ($array) {
                $config_array[$value] = $array;
            }
        }
        return $config_array;
    }

}
