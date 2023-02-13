<?php
/**
 * @copyright ©2020 浙江禾成云计算有限公司
 * Created by PhpStorm.
 * User: Andy - 阿德
 * Date: 2021/1/27
 * Time: 9:43
 */

namespace users\models;


use yii\base\BaseObject;

class LoginUserInfo extends BaseObject
{
    public $nickname;
    public $username;
    public $avatar;
    public $gender;
    public $platform;

    /**
     * @var string $scope
     * auth_info 用户授权
     * auth_base 静默授权
     */
    public $scope = 'auth_base';
    public $openId = '';
    public $unionId = '';
    public $password = '';
}
