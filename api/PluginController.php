<?php
/**
 * 插件
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace leadmall\api;

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
    const __APITYPE__ = "api";

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

    /**
     * 创建方法
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $include = Yii::$app->request->get('include', '');
        if ($include != 'empty') {
            return parent::actionCreate();
        }
        $PluginModel = "system\models\Plugin";
        # code...
        $model = Yii::$app->request->get('model', '');
        $array = $this->get_config($model);
        if ($array) {
            if ($array['status'] === 0) {
                $sql_dir = Yii::$app->basePath . "/plugins/" . $model . "/install.sql";
                //判断SQL文件是否存在
                if (file_exists($sql_dir)) {
                    $sql_data = file_get_contents($sql_dir);
                    if ($sql_data) {
                        $sql_data = str_replace('prefix_', Yii::$app->db->tablePrefix, $sql_data);
                        //执行SQL语句
                        $connection = Yii::$app->db;
                        //执行数据码
                        $transaction = $connection->beginTransaction();
                        try {
                            $connection->createCommand($sql_data)->execute();
                            //设置安装状态
                            $array['status'] = 1;
                            $config_dir      = Yii::$app->basePath . "/plugins/" . $model . "/manifest.json";
                            return file_put_contents($config_dir, to_json($array));
                        } catch (\Exception $e) {
                            // 如果有一条查询失败，则会抛出异常
                            $transaction->rollBack();
                            Error("插件安装失败，请检查文件是否缺失");
                        }
                    }
                }
            } else {
                Error("插件已安装，请先卸载后再执行安装");
            }
        } else {
            Error("找不到manifest.json或解析失败，请检查插件是否正常安装");
        }
    }

    public function actionDelete($value = '')
    {
        $include = Yii::$app->request->get('include', '');
        if ($include != 'empty') {
            return parent::actionDelete();
        }
        $PluginModel = "system\models\Plugin";
        # code...
        $model = Yii::$app->request->get('model', '');
        $array = $this->get_config($model);
        # code...
        $model = Yii::$app->request->get('model', '');
        $array = $this->get_config($model);
        if ($array) {
            if ($array['status'] === 1) {
                try {
                    $sql_dir = Yii::$app->basePath . "/plugins/" . $model . "/install.sql";
                    //设置安装状态
                    $array['status'] = 0;
                    $config_dir      = Yii::$app->basePath . "/plugins/" . $model . "/manifest.json";
                    return file_put_contents($config_dir, to_json($array));
                } catch (\Exception $e) {
                    Error("插件卸载失败，请检查文件是否缺失");
                }
            } else {
                Error("插件尚未安装");
            }
        } else {
            Error("找不到manifest.json或解析失败，请检查插件是否正常安装");
        }
    }
}
