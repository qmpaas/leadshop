<?php
/**
 * @link https://www.leadshop.vip/
 * @copyright Copyright ©2020-2021 浙江禾成云计算有限公司
 */

namespace users\models;

use framework\common\CommonModels;
use framework\common\GenerateIdentify;
use sizeg\jwt\Jwt;
use Yii;
use yii\web\UnauthorizedHttpException;
use \framework\common\TokenHttpException;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property string $id 用户ID
 * @property string $nickname 昵称
 * @property int $mobile 手机号
 * @property string $realname 用户姓名
 * @property string $avatar 头像
 * @property int $gender 性别 0未知 1男 2女
 * @property int $status 帐号状态 0正常  1禁用
 * @property string $AppID 应用ID
 * @property int $is_deleted 是否删除
 * @property int $created_time 创建时间
 * @property int $updated_time 更新时间
 * @property int $deleted_time 删除事件
 * @property Oauth $oauth 删除事件
 */
class User extends CommonModels implements \yii\web\IdentityInterface
{
    use GenerateIdentify;

    const id           = ['bigkey' => 20, 'unique', 'comment' => 'ID'];
    const nickname     = ['varchar' => 50, 'notNull', 'comment' => '昵称'];
    const mobile       = ['bigint' => 11, 'comment' => '手机号'];
    const realname     = ['varchar' => 50, 'comment' => '真实姓名'];
    const birthday     = ['varchar' => 50, 'comment' => '生日'];
    const area         = ['varchar' => 255, 'comment' => '地区'];
    const wechat       = ['varchar' => 50, 'comment' => '微信号'];
    const avatar       = ['varchar' => 255, 'comment' => '头像'];
    const gender       = ['tinyint' => 1, 'default' => 0, 'comment' => '性别 0未知 1男 2女'];
    const status       = ['tinyint' => 1, 'notNull', 'default' => 0, 'comment' => '帐号状态 0正常  1禁用'];
    const AppID        = ['varchar' => 32, 'notNull', 'comment' => '应用ID'];
    const created_time = ['bigint' => 10, 'comment' => '创建时间'];
    const updated_time = ['bigint' => 10, 'comment' => '修改时间'];
    const deleted_time = ['bigint' => 10, 'comment' => '删除时间'];
    const is_deleted   = ['tinyint' => 1, 'default' => 0, 'comment' => '删除状态'];

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
            [['mobile'], 'safe', 'message' => '{attribute}不能为空'],
            [['AppID', 'avatar', 'realname', 'nickname'], 'string', 'message' => '{attribute}必须是字符串'],
            [['gender'], 'integer', 'message' => '{attribute}必须是整数'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
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

    /**
     * 场景处理
     * @return [type] [description]
     */
    public function scenarios()
    {
        $scenarios            = parent::scenarios();
        $scenarios['setting'] = ['mobile', 'realname', 'wechat', 'birthday', 'area', 'gender'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile'   => '手机号',
            'nickname' => '昵称',
            'avatar'   => '头像',
            'gender'   => '性别',
            'birthday' => '生日',
            'area'     => '地区',
            'wechat'   => '微信号',
        ];
    }

    /**
     * 用户行为信息
     * @return [type] [description]
     */
    public function getStatistical()
    {
        return $this->hasOne('users\models\UserStatistical', ['UID' => 'id']);
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        $jwt->key = (new User)->getIdentify();
        $token  = $jwt->getParser()->parse((string) $token);
        $data   = $jwt->getValidationData();
        $AppID  = Yii::$app->params['AppID'] ? Yii::$app->params['AppID'] : '';
        $host   = Yii::$app->request->hostInfo;
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $data->setIssuer($host);
        $data->setAudience($origin);
        $data->setId($AppID);
        $data->setCurrentTime(time());
        if ($token->validate($data) && $jwt->verifyToken($token)) {
            $id = $token->getClaim('id');
            if ($id) {
                $data = static::find()->where(['id' => $id])->with(['oauth'])->one();
                if (!$data) {
                    throw new UnauthorizedHttpException('请先登录');
                }
                return $data;
            } else {
                return null;
            }
        } else {
            if ($token->getClaim('jti') !== $AppID) {
                throw new TokenHttpException('Leadshop应用ID验证错误', 419);
            } else {
                $data->setCurrentTime(time() - 1036800);
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
        return $this->id;
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

    public function getOauth()
    {
        return $this->hasOne(Oauth::className(), ['UID' => 'id']);
    }

    public function getScore()
    {
        $Score = 'plugins\task\models\TaskScore';
        return $this->hasOne($Score::className(), ['UID' => 'id']);
    }

    public function getTaskuser()
    {
        $TaskUser = 'plugins\task\models\TaskUser';
        return $this->hasOne($TaskUser::className(), ['UID' => 'id']);
    }

    public function getLabellog()
    {
        return $this->hasMany(LabelLog::className(), ['UID' => 'id'])->select('id,UID,label_id');
    }

    public function getCoupon()
    {
        return $this->hasMany('coupon\models\UserCoupon', ['UID' => 'id']);
    }
}
