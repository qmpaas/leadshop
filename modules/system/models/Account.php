<?php
/**
 * @link http://www.heshop.com/
 * @copyright Copyright (c) 2020 HeShop Software LLC
 * @license http://www.heshop.com/license/
 */
namespace system\models;

use framework\common\CommonModels;
use sizeg\jwt\Jwt;
use Yii;
use \framework\common\TokenHttpException;

class Account extends CommonModels implements \yii\web\IdentityInterface
{
    /**
     * 此处是字段为额外字段，不进行数据提交
     * @var [type]
     */
    public $uid;
    public $authKey;
    public $token;
    public $password_compare;

    /**
     * 实现数据验证
     * 需要数据写入，必须在rules添加对应规则
     * 在控制中执行[模型]->attributes = $postData;
     * 否则会导致验证不生效，并且写入数据为空
     * @return [type] [description]
     */
    public function rules()
    {
        return [
            ['mobile', 'unique', 'message' => '{attribute}已被使用', 'on' => ['register']],
            //任何场景都需要验证
            //['username', 'unique', 'message' => '{attribute}已经存在'],
            [['mobile', 'password', 'password_compare'], 'required', 'message' => '{attribute}不能为空', 'on' => ['register']],
            // ['username', 'unique', 'targetClass' => self::className(), 'message' => '此用户名已经被使用', 'on' => 'save'],
            //再次确认密码和密码对比
            ['password_compare', 'compare', 'compareAttribute' => 'password', 'message' => '两次密码不一致', 'on' => ['register']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    /**
     * 增加额外属性
     * @return [type] [description]
     */
    public function attributes()
    {
        $attributes = parent::attributes();
        return $attributes;
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Token验证处理
     * @param  [type] $token [description]
     * @param  [type] $type  [description]
     * @return [type]        [description]
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $token  = Yii::$app->jwt->getParser()->parse((string) $token);
        $data   = Yii::$app->jwt->getValidationData();
        $AppID  = Yii::$app->params['AppID'] ? Yii::$app->params['AppID'] : '';
        $host   = Yii::$app->request->hostInfo;
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $data->setIssuer($host);
        $data->setAudience($origin);
        $data->setId($AppID);
        $data->setCurrentTime(time());
        if ($token->validate($data)) {
            $id = $token->getClaim('id');
            if ($id) {
                $data        = static::findOne($id);
                $data->uid   = $id;
                $data->token = (string) $token;
                return $data;
            } else {
                return null;
            }
        } else {
            if ($token->getClaim('jti') !== $AppID) {
                throw new TokenHttpException('Leadshop应用ID验证错误', 419);
            } else {
                $data->setCurrentTime(time() - 21500);
                if ($token->validate($data)) {
                    throw new TokenHttpException('Token validation timeout', 420);
                } else {
                    throw new TokenHttpException('Token validation timeout', 419);
                }
            }
        }
    }

    public function getId()
    {
        return $this->uid;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile'           => '手机号',
            'password'         => '密码',
            'password_compare' => '确认密码',
        ];
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function view()
    {
        return ['token'];
    }

    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }
}
