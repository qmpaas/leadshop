<?php

/**
 * @Author: qinuoyun
 * @Date:   2020-09-09 15:12:15
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-04-23 14:18:47
 */

namespace framework;

class leadmall
{
    /**
     * 配置信息
     * @var array
     */
    public $config = array();

    static $storage = [];

    /**
     * 处理路由模式 1 = 伪静态模式 2 = 传统路由模式
     * @var integer
     */
    public $pattern = 2;

    public function __construct($config = '', $type = "app")
    {
        if ($config) {
            if ($type == "app") {
                //合并公共
                $config = \yii\helpers\ArrayHelper::merge(
                    require (__DIR__ . '/config/common.php'),
                    require (__DIR__ . '/config/components.php'),
                    $config
                );
                $root_base = dirname(__DIR__);
                //判断店铺文件是否存在
                if (!file_exists($root_base . '/stores/98c08c25f8136d590c.json')) {
                    $default = file_get_contents($root_base . '/stores/default.json');
                    if ($default) {
                        file_put_contents($root_base . '/stores/98c08c25f8136d590c.json', $default);
                    }
                }
                //读取路由
                $pathinfo = $this->getPathInfo();
                if ($pathinfo['type'] && in_array($pathinfo['type'], ['app', 'api']) && $pathinfo['name'] && $pathinfo['action']) {
                    //处理路由兼容性
                    $this->setRouter();
                    //改写路由规则
                    if ($this->pattern == 2) {
                        unset($config['components']['urlManager']);
                    } else {
                        $config['components']['urlManager'] = $this->getUrlManager($pathinfo['type']);
                    }
                    //合并自定义命名空间
                    $config['components']['user'] = $this->getEnableUser($pathinfo['type']);
                    //设置特殊字段
                    $config['id'] = $pathinfo['type'];
                    //设置店铺信息
                    $config['params'] = array_merge($config['params'], $this->storeInformation($pathinfo['type']));

                    // P($_GET);
                    // exit();
                } else {
                    //改写路由规则
                    if ($this->pattern == 2) {
                        unset($config['components']['urlManager']);
                    }
                    //设置特殊字段
                    $config['id'] = "web";
                }
            } else {

                $config = \yii\helpers\ArrayHelper::merge(
                    require (__DIR__ . '/config/common.php'),
                    $config
                );
            }

            if (YII_ENV_DEV) {
                // Yii2 Debug模块
                $config['bootstrap'][]      = 'debug';
                $config['modules']['debug'] = [
                    'class'      => 'yii\debug\Module',
                    'panels'     => [
//                        'queue' => \yii\queue\debug\Panel::class,
                    ],
                    // uncomment the following to add your IP if you are not connecting from localhost.
                    'allowedIPs' => isset($local['debugAllowedIPs']) ? $local['debugAllowedIPs'] : ['127.0.0.1', '::1', '*']
                ];

                // Yii2 gii模块（脚手架）
                $config['bootstrap'][] = 'gii';
                $config['modules']['gii'] = [
                    'class' => 'yii\gii\Module',
                    // uncomment the following to add your IP if you are not connecting from localhost.
                    'allowedIPs' => isset($local['giiAllowedIPs']) ? $local['giiAllowedIPs'] : ['127.0.0.1', '::1'],
                ];
            }
            $this->config = $config;
        }
    }

    /**
     * 重置路由地址
     */
    public function setRouter()
    {
        $url        = explode("/", trim($this->getRouterUrl(), "/"));
        $type       = (isset($url[0]) && $url[0]) ? $url[0] : null;
        $name       = (isset($url[1]) && $url[1]) ? $url[1] : null;
        $controller = (isset($url[2]) && $url[2]) ? $url[2] : null;
        $parameter  = (isset($url[3]) && $url[3]) ? $url[3] : null;
        //进行路由信息改写
        if ($_SERVER['REQUEST_METHOD'] == strtoupper("get")) {
            if ($parameter) {
                $action     = "view";
                $_GET['id'] = $parameter;
            } else {
                $action = "index";
            }
        }
        //设置方法
        if ($_SERVER['REQUEST_METHOD'] == strtoupper("post")) {
            $action = "create";
        }
        if ($_SERVER['REQUEST_METHOD'] == strtoupper("put")) {
            if ($parameter) {
                $_GET['id'] = $parameter;
            }
            $action = "update";
        }
        if ($_SERVER['REQUEST_METHOD'] == strtoupper("delete")) {
            if ($parameter) {
                $_GET['id'] = $parameter;
            }
            $action = "delete";
        }
        $array     = [$name, $type, $controller, $action];
        $_GET['r'] = implode('/', $array);
    }

    /**
     * 获取店铺信息
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function storeInformation($type = 'api')
    {
        $params = array(
            "AppID"     => $this->getHttpHeader("QM-APP-ID"),
            "AppSecret" => $this->getHttpHeader("QM-APP-SECRET"),
        );
        if (!empty($params['AppID'])) {
            $file = __DIR__ . "/../stores/{$params['AppID']}.json";
            if (!file_exists($file)) {
                Error('店铺不存在');
            }
            $params = array_merge($params, json_decode(file_get_contents($file), true));
        }
        //来源
        $params["AppType"] = $this->getHttpHeader("QM-APP-TYPE");
        return $params;
    }

    /**
     * 获取用户验证开关
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getEnableUser($type = '')
    {
        if ($type == "api") {
            return [
                'identityClass'   => 'system\models\Account',
                'enableAutoLogin' => true,
                'enableSession'   => false,
            ];
        }
        if ($type == "app") {
            return [
                'identityClass'   => 'users\models\User',
                'enableAutoLogin' => true,
                'enableSession'   => false,
            ];
        }
    }

    /**
     * 获取路由规则协议
     * @param  string $type [description]
     * @return [type]       [description]
     */
    public function getUrlManager($type = '')
    {
        $manager = [
            'enablePrettyUrl'     => true,
            'showScriptName'      => false,
            'enableStrictParsing' => true,
            'rules'               => [
                //模块化API路由
                'GET ' . $type . '/<module>/<controller:\w+>/'                                    => '<module>/' . $type . '/<controller>/index',
                'GET ' . $type . '/<module>/<controller:\w+>/<id:\d+>'                            => '<module>/' . $type . '/<controller>/view',
                'POST ' . $type . '/<module>/<controller:[\w-]+>'                                 => '<module>/' . $type . '/<controller>/create',
                'POST ' . $type . '/<module>/<controller:[\w-]+>/<appid:(\w+)*>/<apptype:(\w+)*>' => '<module>/' . $type . '/<controller>/create',
                'PUT ' . $type . '/<module>/<controller:[\w-]+>'                                  => '<module>/' . $type . '/<controller>/update',
                'PUT ' . $type . '/<module>/<controller:[\w-]+>/<id:\d+>'                         => '<module>/' . $type . '/<controller>/update',
                'PUT ' . $type . '/<module>/<controller:[\w-]+>/<id:(\d+,)*\d+$>'                 => '<module>/' . $type . '/<controller>/update',
                'DELETE ' . $type . '/<module>/<controller:[\w-]+>/<id:(\d+,)*\d+$>'              => '<module>/' . $type . '/<controller>/delete',
                'OPTIONS ' . $type . '/<module>/<controller:\w+>'                                 => '<module>/' . $type . '/<controller>/options',
                'OPTIONS ' . $type . '/<module>/<controller:\w+>/<id:(\d+,)*\d+$>'                => '<module>/' . $type . '/<controller>/options',
                $type . '/<module>/<controller:\w+>/<action:\w+>/<id:(\d+,)*\d+$>'                => '<module>/' . $type . '/<controller>/<action>',
                $type . '/<module>/<controller:\w+>/<action:\w+>'                                 => '<module>/' . $type . '/<controller>/<action>',
                'OPTIONS ' . $type . '/<module>/<controller>/<action>'                            => '<module>/' . $type . '/<controller>/<action>',
            ],
        ];
        return $manager;
    }

    /**
     * 获取应用名称
     * @return [type] [description]
     */
    public function getPathInfo()
    {
        $url    = explode("/", trim($this->getRouterUrl(), "/"));
        $type   = (isset($url[0]) && $url[0]) ? $url[0] : null;
        $name   = (isset($url[1]) && $url[1]) ? $url[1] : null;
        $action = (isset($url[2]) && $url[2]) ? $url[2] : null;
        return [
            "type"   => $type,
            "name"   => $name,
            "action" => $action,
        ];
    }

    /**
     * 获取上传露露
     */
    public function getRouterUrl()
    {
        //开始读取URL地址
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            // check this first so IIS will catch
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REDIRECT_URL'])) {
            // Check if using mod_rewrite
            $requestUri = $_SERVER['REDIRECT_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            // IIS 5.0, PHP as CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        if (@$_GET['q']) {
            return "/" . ltrim($_GET['q'], "/");
        } else {
            return $requestUri;
        }
    }

    /**
     * 兼容模式
     * @return [type] [description]
     */
    public function getRequestUri()
    {
        //处理
        $this->pattern = 1;
        //开始读取URL地址
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            // check this first so IIS will catch
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REDIRECT_URL'])) {
            // Check if using mod_rewrite
            $requestUri = $_SERVER['REDIRECT_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            // IIS 5.0, PHP as CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        if (@$_GET['r']) {
            $this->pattern = 2;
            if (strpos($_GET['r'], "/") === 0) {
                $_GET['r'] = substr($_GET['r'], 1);
            }
            return $_GET['r'];
        } else {
            return $requestUri;
        }
    }

    /**
     * 获取命名列表
     * @param  string $name [description]
     * @return [type]       [description]
     */
    public function getAliases($name = '')
    {
        $modules = [
            "demo",
            "basics",
            "goods",
            "users",
            "system",
        ];
        $array = array();
        foreach ($modules as $key => $value) {
            $array['@' . $value . "/api"]    = '@app/modules/' . $value . '/api';
            $array['@' . $value . "/app"]    = '@app/modules/' . $value . '/app';
            $array['@' . $value . "/models"] = '@app/modules/' . $value . '/models';
        }
        return $array;
    }

    /**
     * 获取头部信息
     * @param  string $headerKey [description]
     * @return [type]            [description]
     */
    public function getHttpHeader($headerKey = '')
    {
        $headerKey = strtoupper($headerKey);
        $headerKey = str_replace('-', '_', $headerKey);
        $headerKey = 'HTTP_' . $headerKey;
        return isset($_SERVER[$headerKey]) ? $_SERVER[$headerKey] : '';
    }

    /**
     * 获取不同的类
     */
    public static function Model($name = null, $model = null, $is_new = false)
    {
        $class = [$name, 'models', $model];
        if ($name == null) {
            $array  = debug_backtrace();
            $object = $array[2]['class'];
            if (strpos($object, "app") === 0) {
                if (isset($array[2]['class']) && $array[2]['class']) {
                    $tree     = get_parent_class($object);
                    $info     = explode("\\", $tree);
                    $class[0] = $info[0];
                    if ($model == null) {
                        $class[2] = ucfirst($info[0]);
                    } else {
                        $class[2] = $model;
                    }
                }
            } else {
                $info     = explode("\\", $object);
                $class[0] = $info[0];
                if ($model == null) {
                    $class[2] = ucfirst($info[0]);
                } else {
                    $class[2] = $model;
                }
            }
        }
        //重组
        $modelClass = implode("\\", $class);
        if ($is_new) {
            return new $modelClass();
        } else {
            return $modelClass;
        }
    }
}
