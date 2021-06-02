<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-08-20 13:46:09
 * @Last Modified by:   qinuoyun
 * @Last Modified time: 2021-05-11 17:07:44
 */
namespace framework\common;

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
        if (isset(Yii::$app->params['runModule'])) {
            Yii::error(Yii::$app->params['runModule']);
            return $behaviors;
        }

        //获取白名单
        $accessWhitelist = Yii::$app->params['accessWhitelist'] ?? [];
        $optional        = "__qmpaas_white__";
        //判断是否在白名单
        if (in_array(Yii::$app->requestedRoute, $accessWhitelist)) {
            $whitelist = explode("/", Yii::$app->requestedRoute);
            $optional  = end($whitelist);
        }
        if (isset(Yii::$app->params['runModule'])) {
            Yii::error(Yii::$app->params['runModule']);
        }

        if ($optional === '__qmpaas_white__') {
            if (WE7_URL) {
                $users      = \system\models\Account::find()->one();
                $controller = (new \system\api\AccountController($this->id, $this->module));
                $token      = $controller->getToken($users->id);
                Yii::$app->request->getHeaders()->set("Authorization", "Bearer " . $token);
            } else {
                //解决微擎兼容问题
                if (!Yii::$app->request->getHeaders()->get('Authorization') && Yii::$app->request->getHeaders()->get('token')) {
                    $token = Yii::$app->request->getHeaders()->get('token');
                    Yii::$app->request->getHeaders()->set("Authorization", $token);
                }
            }
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
        header('Access-Control-Expose-Headers: Authorization,token,QM-APP-TYPE,QM-APP-ID,QM-APP-SECRET,X-Pagination-Current-Page,X-Pagination-Page-Count,X-Pagination-Per-Page,X-Pagination-Total-Count');
        header("Access-Control-Allow-Headers: Authorization,token,QM-APP-TYPE,QM-APP-ID,QM-APP-SECRET,Content-Type,Accept,Origin,X-Pagination-Per-Page");
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
}
