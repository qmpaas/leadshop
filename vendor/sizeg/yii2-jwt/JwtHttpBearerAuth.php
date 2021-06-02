<?php

namespace sizeg\jwt;

use yii\di\Instance;
use yii\filters\auth\AuthMethod;

/**
 * JwtHttpBearerAuth is an action filter that supports the authentication method based on JSON Web Token.
 *
 * You may use JwtHttpBearerAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'bearerAuth' => [
 *             'class' => \sizeg\jwt\JwtHttpBearerAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Dmitriy Demin <sizemail@gmail.com>
 * @since 1.0.0-a
 */
class JwtHttpBearerAuth extends AuthMethod
{

    /**
     * @var Jwt|string|array the [[Jwt]] object or the application component ID of the [[Jwt]].
     */
    public $jwt = 'jwt';

    /**
     * @var string A "realm" attribute MAY be included to indicate the scope
     * of protection in the manner described in HTTP/1.1 [RFC2617].  The "realm"
     * attribute MUST NOT appear more than once.
     */
    public $realm = 'api';

    /**
     * @var string Authorization header schema, default 'Bearer'
     */
    public $schema = 'Bearer';

    /**
     * @var callable a PHP callable that will authenticate the user with the JWT payload information
     *
     * ```php
     * function ($token, $authMethod) {
     *    return \app\models\User::findOne($token->getClaim('id'));
     * }
     * ```
     *
     * If this property is not set, the username information will be considered as an access token
     * while the password information will be ignored. The [[\yii\web\User::loginByAccessToken()]]
     * method will be called to authenticate and login the user.
     */
    public $auth;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->jwt = Instance::ensure($this->jwt, Jwt::className());
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get('Authorization');
        if ($authHeader !== null && preg_match('/^' . $this->schema . '\s+(.*?)$/', $authHeader, $matches)) {
            $token = $this->loadToken($matches[1]);
            if ($token === null) {
                return null;
            }

            if ($this->auth) {
                $identity = call_user_func($this->auth, $token, get_class($this));
            } else {
                $identity = $user->loginByAccessToken($token, get_class($this));
            }

            return $identity;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function challenge($response)
    {
        $response->getHeaders()->set(
            'WWW-Authenticate',
            "{$this->schema} realm=\"{$this->realm}\", error=\"invalid_token\", error_description=\"The access token invalid or expired\""
        );
    }

    /**
     * Parses the JWT and returns a token class
     * @param string $token JWT
     * @return Token|null
     */
    public function loadToken($token)
    {
        return $this->jwt->loadToken($token);
    }
}
