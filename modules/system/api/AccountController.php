<?php
/**
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace system\api;

use framework\common\BasicController;
use Yii;
use \framework\common\TokenHttpException;

/**
 * 后台用户管理器
 */
class AccountController extends BasicController
{

    /**
     * 后台登录
     * @return [type] [description]
     */
    public function actionLogin()
    {
        $post = Yii::$app->request->post();
        $data = $this->modelClass::find()->where(['mobile' => $post['mobile'], 'password' => MD5($post['password'])])->one();
        if ($data) {
            //如果数据存在，则转数组
            $data = $data->toArray();
            //删除用户密码字段
            unset($data['password']);
            //获取Token数据
            $token         = $this->getToken($data['id']);
            $data['token'] = $token;
            return $data;
        } else {
            Error('用户不存在或密码错误');
        }
    }

    /**
     * 后台登录
     * @return [type] [description]
     */
    public function actionLogout()
    {
        $post = Yii::$app->request->post();
        $data = $this->modelClass::find()->where(['mobile' => $post['mobile'], 'password' => $post['password']])->one();
        if ($data) {
            $token         = $this->getToken($data['id']);
            $data['token'] = $token;
            return $data;
        } else {
            return null;
        }
    }

    /**
     * 用户注册
     * @return [type] [description]
     */
    public function actionRegister()
    {
        //调用模型
        $model    = new $this->modelClass();
        $postData = Yii::$app->request->post();
        //加载数据
        $model->load($postData);
        $model->attributes = $postData;
        //密码Hash加密处理
        $model->password = MD5($postData['password']);
        //密码Hash加密处理
        $model->password_compare = MD5($postData['password_compare']);
        //执行数据保存
        $result = $model->save();
        return $model;
        if ($result) {
            return $model->attributes['id'];
        }
    }

    /**
     *
     * @return [type] [description]
     */
    public function actionReset()
    {
        //调用模型
        $model    = new $this->modelClass();
        $postData = Yii::$app->request->post();
        $token    = $postData['token'] ? $postData['token'] : "";
        $token    = Yii::$app->jwt->getParser()->parse((string) $token);
        $data     = Yii::$app->jwt->getValidationData();
        $AppID    = Yii::$app->params['AppID'] ? Yii::$app->params['AppID'] : '';
        $host     = Yii::$app->request->hostInfo;
        $origin   = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $data->setIssuer($host);
        $data->setAudience($origin);
        $data->setId($AppID);
        $data->setCurrentTime(time());
        if ($token->validate($data)) {
            $id = $token->getClaim('id');
            if ($id) {
                $data          = $model::findOne($id)->toArray();
                $data['token'] = (string) $token;
                return $data;
            } else {
                return null;
            }
        } else {
            if ($token->getClaim('jti') !== $AppID) {
                throw new TokenHttpException('Leadshop应用ID验证错误', 419);
            } else {
                $data->setCurrentTime(time() - 26500);
                if ($token->validate($data)) {
                    $id = $token->getClaim('id');
                    if ($id) {
                        $data          = $model::findOne($id)->toArray();
                        $data['token'] = (string) $this->getToken($id);
                        return $data;
                    } else {
                        return null;
                    }
                } else {
                    throw new TokenHttpException('Token validation timeout', 419);
                }
            }
        }
    }

    /**
     * 用户菜单
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function actionMenus()
    {
        //获取角色模型
        $rolesModel = 'app\modules\roles\models\Roles';
        //获取角色模型
        $menusModel = 'app\modules\menus\models\Menus';
        //获取用户对应角色
        $roles      = json_decode(Yii::$app->user->identity->roles, true);
        $rolesArray = $rolesModel::find()->where(['is_deleted' => 0])->where(['in', 'id', $roles])->all();
        $visits     = [];
        foreach ($rolesArray as $key => $value) {
            $visitArray = json_decode($value->visits, true);
            //数组合并
            $visits = array_merge($visits, $visitArray);
        }
        //数组去重
        $visits = array_unique($visits);
        //查询菜单
        $menusArray = $menusModel::find()
        //设置菜单别名，缩短代码量
            ->alias('a')
            ->select([
                "a.id as id",
                "a.parent_id as parent_id",
                "a.modul_id as modul_id",
                "a.tag as tag",
                "a.name as name",
                "a.title as title",
                "a.is_hidden as is_hidden",
                "a.icon as icon",
                "a.type as type",
                "a.path as path",
                "a.page as page",
                "b.name as modul_name",
            ])
            ->where(['a.tag' => 'modules'])
            ->andWhere(['a.is_deleted' => 0])
            ->andWhere(['in', 'a.id', $visits])
            ->joinWith('model as b')
            ->all();
        return $menusArray;
    }

    /**
     * 用户菜单
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function actionApply()
    {
        $post = Yii::$app->request->post();
        //获取角色模型
        $rolesModel = 'app\modules\roles\models\Roles';
        //获取角色模型
        $menusModel = 'app\modules\menus\models\Menus';
        //查询获取当前菜单的所有子菜单
        $menu = $menusModel::find()->where(['is_deleted' => 0, 'name' => $post['name']])->one();

        if (!$menu) {
            Error('获取的菜单节点不存在');
        }

        //获取用户对应角色
        $roles      = json_decode(Yii::$app->user->identity->roles, true);
        $rolesArray = $rolesModel::find()->where(['is_deleted' => 0])->where(['in', 'id', $roles])->all();
        $visits     = [];
        foreach ($rolesArray as $key => $value) {
            $visitArray = json_decode($value->visits, true);
            //数组合并
            $visits = array_merge($visits, $visitArray);
        }
        //数组去重
        $visits = array_unique($visits);
        //查询菜单
        $menusArray = $menusModel::find()
        //设置菜单别名，缩短代码量
            ->alias('a')
            ->select([
                "a.id as id",
                "a.parent_id as parent_id",
                "a.modul_id as modul_id",
                "a.tag as tag",
                "a.name as name",
                "a.title as title",
                "a.is_hidden as is_hidden",
                "a.icon as icon",
                "a.type as type",
                "a.path as path",
                "a.page as page",
                "b.name as modul_name",
            ])
            ->where(['like', 'a.path', $menu['path'] . '-' . $menu['id']])
            ->andWhere(['a.is_deleted' => 0])
            ->andWhere(['in', 'a.id', $visits])
            ->joinWith('model as b')
            ->all();
        return $menusArray;
    }

    /**
     * 获取Token信息
     * 超时时间:21600
     * @param  string $id [description]
     * @return [type]      [description]
     */
    public function getToken($id = '')
    {
        /** @var Jwt $jwt */
        $jwt    = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key    = $jwt->getKey();
        $time   = time();
        $host   = Yii::$app->request->hostInfo;
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        // Adoption for lcobucci/jwt ^4.0 version
        $token = $jwt->getBuilder()
            ->issuedBy($host) // Configures the issuer (iss claim)
            ->permittedFor($origin) // Configures the audience (aud claim)
            ->identifiedBy(Yii::$app->params['AppID'] ? Yii::$app->params['AppID'] : '', true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->expiresAt($time + 21600) // Configures the expiration time of the token (exp claim)
            ->withClaim('id', $id) // Configures a new claim, called "id"
            ->getToken($signer, $key); // Retrieves the generated token
        return (string) $token;
    }

    public function changePwd()
    {
        $host = Yii::$app->request->hostInfo;
        if ($host == "http://demo.leadshop.vip") {
            return true;
        }
        if ($host == "https://demo.leadshop.vip") {
            return true;
        }
        $pass     = Yii::$app->request->post('old_password');
        $newPass1 = Yii::$app->request->post('new_password1');
        $newPass2 = Yii::$app->request->post('new_password2');
        if (!$pass || !$newPass1 || !$newPass2) {
            Error('请填写表单');
        }
        if ($newPass1 != $newPass2) {
            Error('两次新密码不一致');
        }
        $admin = $this->modelClass::find()->where(['mobile' => Yii::$app->user->identity->mobile, 'password' => MD5($pass)])->one();
        if (!$admin) {
            Error('旧密码错误');
        }
        $admin->password = md5($newPass2);
        if ($admin->save()) {
            return true;
        }
        return false;
    }
}
