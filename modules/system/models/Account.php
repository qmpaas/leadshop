<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */
namespace system\models;

use framework\common\AccessToken;
use framework\common\CommonModels;
use Yii;

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
        $token       = AccessToken::accessToken($token);
        $id          = $token->getClaim('id');
        $data        = static::findOne($id);
        $data->uid   = $id;
        $data->token = (string) $token;
        return $data;
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
