<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace basics\common;

use framework\common\BasicController;
use Yii;

/**
 * 后台用户管理器
 */
class BasicsController extends BasicController
{

    // const __APITYPE__ = "";

    /**
     * 处理接口白名单
     * @var array
     */
    public $whitelists = [];

    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * 获取插件信息
     * @return [type] [description]
     */
    public function get_plugin()
    {
        $include = Yii::$app->request->get('include', '');
        $model   = Yii::$app->request->get('model', '');
        if (!$include) {
            Error('未找到插件');
        }
        /**
         * 获取子类信息
         * get_class($this) 返回获得子类类名
         * @var [type]
         */
        $objClass = new \ReflectionClass(get_class($this));
        /**
         * 获取子类所有类常量
         * @var [type]
         */
        $arrConst = $objClass->getConstants();
        /**
         * 拼写类信息
         * @var [type]
         */
        $class = ['', 'plugins', $include, $arrConst['__APITYPE__'], ucfirst($model) . "Controller"];
        $class = implode('\\', $class);
        return (new $class($this->id, $this->module));
    }

    /**
     * 列表方法
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'index');
        $action = in_array('$action', ['index', 'view', 'delete', 'update', 'creacte']) ? 'index' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    /**
     * 详情方法
     * @return [type] [description]
     */
    public function actionView()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'view');
        $action = in_array('$action', ['index', 'view', 'delete', 'update', 'creacte']) ? 'view' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    /**
     * 删除方法
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'delete');
        $action = in_array('$action', ['index', 'view', 'delete', 'update', 'creacte']) ? 'delete' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    /**
     * 更新方法
     * @return [type] [description]
     */
    public function actionUpdate()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'update');
        $action = in_array('$action', ['index', 'view', 'delete', 'update', 'creacte']) ? 'update' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    /**
     * 创建方法
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $params = [];
        $action = Yii::$app->request->get('action', 'create');
        $action = in_array('$action', ['index', 'view', 'delete', 'update', 'creacte']) ? 'create' : $action;
        return $this->get_plugin()->runAction($action, $action);
    }

    /**
     * 执行模型
     * @param  [type] $model  [description]
     * @param  string $name   [description]
     * @param  string $action [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public function runModule($model, $name = '', $action = "index", $params = [])
    {
        $class      = ['', $model, self::__APITYPE__, ucfirst($name) . "Controller"];
        $class      = implode('\\', $class);
        $controller = (new $class($this->id, $this->module));
        if ($action == "index") {
            $_POST['_method']          = "get";
            $_SERVER['REQUEST_METHOD'] = "GET";
        }
        if ($action == "create") {
            $_POST['_method'] = "post";
        }
        if ($action == "view") {
            $_GET                      = $params;
            $_POST['_method']          = "get";
            $_SERVER['REQUEST_METHOD'] = "GET";
        }
        if ($action == "update") {
            if (empty($params)) {
                $params = $_GET;
            } else {
                $_GET = $params;
            }
            $_POST            = \Yii::$app->request->post();
            $_POST['_method'] = "put";
        }
        if ($action == "delete") {
            $_POST['_method'] = "delete";
        }
        Yii::$app->params['runModule'] = true;
        return $controller->runAction($action, $params);
    }

    /**
     * 执行插件目录
     * @param  [type] $model  [description]
     * @param  string $name   [description]
     * @param  string $action [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public function runPlugin($model, $name = '', $action = "index", $params = [])
    {
        $class      = ['', $model, self::__APITYPE__, ucfirst($name) . "Controller"];
        $class      = implode('\\', $class);
        $controller = (new $class($this->id, $this->module));
        if ($action == "index") {
            $_POST['_method']          = "get";
            $_SERVER['REQUEST_METHOD'] = "GET";
        }
        if ($action == "create") {
            $_POST            = Yii::$app->request->post();
            $_POST['_method'] = "post";
        }
        if ($action == "view") {
            if (empty($params)) {
                $params = $_GET;
            } else {
                $_GET = $params;
            }
            $_POST['_method']          = "get";
            $_SERVER['REQUEST_METHOD'] = "GET";
        }
        if ($action == "update") {
            if (empty($params)) {
                $params = $_GET;
            } else {
                $_GET = $params;
            }
            $_POST            = Yii::$app->request->post();
            $_POST['_method'] = "put";
        }
        if ($action == "delete") {
            $_POST['_method'] = "delete";
        }
        return $controller->runAction($action, $params);
    }
    /**
     * 插件专用功能
     * 获取清单
     * manifest() 标识所有清代所有数据
     * manifest(<key>) 表示获取谋一个清单值数据
     * manifest(<key1.key2>) 多维数组嵌套的清单数据值获取
     * manifest(<key1>,<val>) 表示修改某人清单数据值
     * manifest(<key1.key2>,<val>) 表示多维数组清单的值修改
     * @return [type] [description]
     */
    public function manifest($keyword = "", $value = "")
    {
        $object_name = explode("\\", get_class($this));
        if ($object_name[0] == 'plugins') {
            $plugin_name  = $object_name[1];
            $manifest_dir = Yii::$app->basePath . "/plugins/" . $plugin_name . "/manifest.json";
            if (file_exists($manifest_dir)) {
                $manifest_body  = file_get_contents($manifest_dir);
                $manifest_array = json_decode($manifest_body, true);
                if (json_last_error() == JSON_ERROR_NONE) {
                    if ($keyword) {
                        $keyword_array = explode(".", $keyword);
                        //判断如果参数存在，则需要修改
                        if ($value) {
                            $end_key = end($keyword_array);
                            $data    = &$manifest_array;
                            if (count($keyword_array)) {
                                foreach ($keyword_array as $key) {
                                    if ($key == $end_key) {
                                        $data[$key] = $value;
                                    } else {
                                        $data = &$data[$key];
                                    }
                                }
                            }
                            return file_put_contents($manifest_dir, to_json($manifest_array));
                        } else {
                            //标识获取参数值
                            $data = $manifest_array;
                            foreach ($keyword_array as $key) {
                                $data = $data[$key];
                            }
                            return $data;
                        }
                    } else {
                        return $manifest_array;
                    }
                } else {
                    Error("manifest.json解析失败，请检查插件是否正常安装");
                }
            } else {
                Error("找不到manifest.json，请检查插件是否正常安装");
            }
        }
        return false;
    }

    /**
     * 获取配置信息
     * @param  string $plugin_name [description]
     * @return [type]              [description]
     */
    public function get_config($plugin_name = '')
    {
        $manifest_dir = Yii::$app->basePath . "/plugins/" . $plugin_name . "/manifest.json";
        if (file_exists($manifest_dir)) {
            try {
                $manifest_body  = file_get_contents($manifest_dir);
                $manifest_array = json_decode($manifest_body, true);
                if (json_last_error() == JSON_ERROR_NONE) {
                    return $manifest_array;
                }
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }
}
