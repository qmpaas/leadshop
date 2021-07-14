<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-07-08 18:05:14
 */
namespace framework\common;

use ReflectionClass;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class BasicController extends StoreController
{
    /**
     * {@inheritdoc}application/json
     */
    public function init()
    {

        if ($this->modelClass === null) {
            $tree    = get_parent_class(get_class($this));
            $info    = explode("\\", $tree);
            $info[1] = "models";
            if ($info[2] == "IndexController") {
                $info[2] = ucfirst($info[0]);
            } else {
                $info[2] = str_replace("Controller", "", $info[2]);
            }
            $this->modelClass = implode("\\", $info);
        }
        //处理店铺信息
        if (Yii::$app->id == "app") {
            //处理店铺信息
            //$this->getShopConfig();
        }
        //返回父级信息
        parent::init();
    }

    /**
     * 获取店铺配置信息
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function getShopConfig()
    {
        $AppType     = Yii::$app->params['AppType'];
        $json_string = file_get_contents(Yii::$app->basePath . '/apple/' . Yii::$app->params['ShopID'] . '.json');
        // 用参数true把JSON字符串强制转成PHP数组
        $data = json_decode($json_string, true);
        //此处需要新增店铺授权校验

        //此处动态设置应用配置信息
        Yii::$app->params[$AppType] = $data['applay'][$AppType];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //解决桥接问题
        if (isset(Yii::$app->params['runModule'])) {
            Yii::error(Yii::$app->params['runModule']);
            return $behaviors;
        }

        //插件部分全过滤
        if (get_class($this) == 'leadmall\app\PluginController') {
            return $behaviors;
        }
        if (get_class($this) == 'leadmall\api\PluginController') {
            return $behaviors;
        }
        if (get_class($this) == 'leadmall\api\PluginController') {
            return $behaviors;
        }
        //处理插件白名单问题
        $classArray = explode("\\", get_class($this));

        //Yii::info(to_json($classArray));

        if ($classArray[0] == 'plugins') {
            if ($classArray[2] == 'common') {
                return $behaviors;
            }
            /**
             * 如下备注用于测试
             */
            // if (get_class($this) == 'plugins\task\common') {
            //     P($this->whitelists);
            //     P(Yii::$app->controller->action->id);
            //     exit();
            // }
            if (in_array(Yii::$app->controller->action->id, $this->whitelists)) {
                return $behaviors;
            }
        }

        //获取白名单
        $accessWhitelist = Yii::$app->params['accessWhitelist'] ?? [];
        $optional        = "__qmpaas_white__";
        //判断是否在白名单
        if (in_array(Yii::$app->requestedRoute, $accessWhitelist)) {
            $whitelist = explode("/", Yii::$app->requestedRoute);
            $optional  = end($whitelist);
        }

        $behaviors['authenticator'] = [
            'class'       => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
            //框架已定义的不需要授权的路由数组
            'optional'    => [
                'login',
                'register',
                'options',
                $optional,
            ],
        ];
        return $behaviors;
    }

    /**
     * 前置处理，解决跨域问题
     * @param  [type] $action [description]
     * @return [type]         [description]
     */
    public function beforeAction($action)
    {
        $url = Yii::$app->request->origin;
        header("Access-Control-Allow-Origin: {$url}");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Expose-Headers: Authorization,token,QM-APP-TYPE,QM-APP-ID,QM-APP-SECRET,Content-Page,X-Pagination-Current-Page,X-Pagination-Page-Count,X-Pagination-Per-Page,X-Pagination-Total-Count');
        header("Access-Control-Allow-Headers: Authorization,token,QM-APP-TYPE,QM-APP-ID,QM-APP-SECRET,Content-Page,Content-Type,Accept,Origin,X-Pagination-Per-Page");
        //判断是否为预请求接口
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            Yii::$app->response->setStatusCode(204);
            Yii::$app->end(0);
        }
        return parent::beforeAction($action);
    }

    /**
     * 重写父类
     * @return [type] [description]
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

    public function actionOptions()
    {
        Yii::$app->response->setStatusCode(204);
        Yii::$app->end(0);
    }

    /**
     * 处理数据分页问题
     * Dustbin [n. 垃圾箱]
     * @return [type] [description]
     */
    public function actionDustbin()
    {
        $headers    = Yii::$app->getRequest()->getHeaders();
        $pageSize   = $headers->get('X-Pagination-Per-Page') ?? 20;
        $modelClass = $this->modelClass;
        return new ActiveDataProvider(
            [
                'query'      => $modelClass::find()->where(['is_deleted' => 1])->asArray(),
                'pagination' => ['pageSize' => intval($pageSize)],
            ]
        );
    }

    /**
     * 处理数据分页问题
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $headers    = Yii::$app->getRequest()->getHeaders();
        $pageSize   = $headers->get('X-Pagination-Per-Page') ?? 20;
        $modelClass = $this->modelClass;
        return new ActiveDataProvider(
            [
                'query'      => $modelClass::find()->where(['is_deleted' => 0])->asArray(),
                'pagination' => ['pageSize' => intval($pageSize)],
            ]
        );
    }

    /**
     * 处理单条数据
     * @return [type]        [description]
     */
    public function actionView()
    {
        $get = Yii::$app->request->get();

        $id = intval($get['id']);

        $model = $this->modelClass::findOne($id);

        if ($model) {
            if ($model->is_deleted) {
                throw new ForbiddenHttpException('数据不存在,或已被删除');
            } else {
                return $model->toArray();
            }
        } else {
            throw new ForbiddenHttpException('数据不存在');
        }
    }

    /**
     * 处理数据软删除
     * @return [type] [description]
     */
    public function actionDelete()
    {
        $get   = Yii::$app->request->get();
        $id    = intval($get['id']);
        $model = $this->modelClass::findOne($id);
        if ($model) {
            $model->deleted_time = time();
            $model->is_deleted   = 1;
            if ($model->save()) {
                return $model->is_deleted;
            } else {
                return $model;
                throw new ForbiddenHttpException('删除失败，请检查is_deleted字段是否存在');
            }
        } else {
            throw new ForbiddenHttpException('删除失败，数据不存在');
        }
    }

    /**
     * 处理数据真实移除
     * @return [type] [description]
     */
    public function actionRemove()
    {
        $get   = Yii::$app->request->get();
        $id    = intval($get['id']);
        $model = $this->modelClass::findOne($id);
        if ($model) {
            if ($model::findOne($id)->delete()) {
                return true;
            } else {
                throw new ForbiddenHttpException('删除失败，请检查is_deleted字段是否存在');
            }
        } else {
            throw new ForbiddenHttpException('删除失败，数据不存在');
        }
    }

    /**
     * 一个公开的更新监测接口
     * @param  string $value [description]
     * @return [type]        [description]
     */
    final public function __public_update_check__()
    {
        //获取当前模型类
        $moduleClass = get_parent_class(get_class($this));
        //根据类反查继承类
        $reflector = new \ReflectionClass($moduleClass);
        $classFile = $reflector->getFileName();
        return $classFile;
    }

    /**
     * 授权验证接口
     * @param string $value [description]
     */
    final public function Authorization()
    {

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
    public function plugins($plugin, $keyword = "", $value = "")
    {
        if ($plugin) {
            $manifest_dir = Yii::$app->basePath . "/plugins/" . $plugin . "/manifest.json";
            //如果第二个参数为数组，则执行方法
            if (is_array($keyword)) {
                $class = '\plugins\task\common';
                if (class_exists($class)) {
                    $class  = new $class($this->id, $this->module);
                    $action = array_shift($keyword);
                    $param  = empty($keyword) ? [] : array_shift($keyword);
                    //通过反射获取参数名称
                    $reflect    = new ReflectionClass($class);
                    $Parameters = $reflect->getMethod('action' . strtolower($action))->getParameters();
                    $params     = [];
                    //循环设置参数
                    foreach ($Parameters as $key => $value) {
                        if (is_array($param) && !empty($param)) {
                            $params[$value->name] = array_shift($param);
                        } else {
                            if (isset($param) && !empty($param)) {
                                $params[$value->name] = $param ?? "";
                            }

                        }
                    }
                    //执行插件公共方法-专门用于内调
                    return $class->runAction($action, $params);
                } else {
                    Error("{$plugin}插件公共方法不存在，请检查插件是否正常安装");
                }
            } else {
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
        }
        return false;
    }
}
