# Yii2 JWT

![](https://travis-ci.org/sizeg/yii2-jwt.svg)

This extension provides the [JWT](https://github.com/lcobucci/jwt) integration for the [Yii framework 2.0](http://www.yiiframework.com) (requires PHP 5.6+).
It includes basic HTTP authentication support.

## Table of contents

1. [Installation](#installation)
1. [Dependencies](#dependencies)
1. [Basic usage](#basicusage)
   1. [Creating](#basicusage-creating)
   1. [Parsing from strings](#basicusage-parsing)
   1. [Validating](#basicusage-validating)
1. [Token signature](#tokensign)
   1. [Hmac](#tokensign-hmac)
   1. [RSA and ECDSA](#tokensign-rsa-ecdsa)
1. [Yii2 basic template example](#yii2basic-example)

<a name="installation"></a>
## Installation

Package is available on [Packagist](https://packagist.org/packages/sizeg/yii2-jwt),
you can install it using [Composer](http://getcomposer.org).

```shell
composer require sizeg/yii2-jwt
```

<a name="dependencies"></a>
## Dependencies

- PHP 5.6+
- OpenSSL Extension
- [lcobucci/jwt 3.3](https://github.com/lcobucci/jwt/tree/3.3)

<a name="basicusage"></a>
## Basic usage

Add `jwt` component to your configuration file,

```php
'components' => [
    'jwt' => [
      'class' => \sizeg\jwt\Jwt::class,
      'key'   => 'secret',
    ],
],
```

Configure the `authenticator` behavior as follows.

```php
namespace app\controllers;

class ExampleController extends \yii\rest\Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \sizeg\jwt\JwtHttpBearerAuth::class,
        ];

        return $behaviors;
    }
}
```

Also you can use it with `CompositeAuth` reffer to a [doc](http://www.yiiframework.com/doc-2.0/guide-rest-authentication.html).

<a name="basicusage-creating"></a>
### Creating

Some methods marked as deprecated and will soon backport things from lcobucci/jwt 4.x to create an upgrade path.

Just use the builder to create a new JWT/JWS tokens:

```php
$time = time();
$token = Yii::$app->jwt->getBuilder()
            ->issuedBy('http://example.com') // Configures the issuer (iss claim)
            ->permittedFor('http://example.org') // Configures the audience (aud claim)
            ->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($time + 60) // Configures the time that the token can be used (nbf claim)
            ->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', 1) // Configures a new claim, called "uid"
            ->getToken(); // Retrieves the generated token


$token->getHeaders(); // Retrieves the token headers
$token->getClaims(); // Retrieves the token claims

echo $token->getHeader('jti'); // will print "4f1g23a12aa"
echo $token->getClaim('iss'); // will print "http://example.com"
echo $token->getClaim('uid'); // will print "1"
echo $token; // The string representation of the object is a JWT string (pretty easy, right?)
```

<a name="basicusage-parsing"></a>
### Parsing from strings

Use the parser to create a new token from a JWT string (using the previous token as example):

```php
$token = Yii::$app->jwt->getParser()->parse((string) $token); // Parses from a string
$token->getHeaders(); // Retrieves the token header
$token->getClaims(); // Retrieves the token claims

echo $token->getHeader('jti'); // will print "4f1g23a12aa"
echo $token->getClaim('iss'); // will print "http://example.com"
echo $token->getClaim('uid'); // will print "1"
```

<a name="basicusage-validating"></a>
### Validating

We can easily validate if the token is valid (using the previous token as example):

```php
$data = Yii::$app->jwt->getValidationData(); // It will use the current time to validate (iat, nbf and exp)
$data->setIssuer('http://example.com');
$data->setAudience('http://example.org');
$data->setId('4f1g23a12aa');

var_dump($token->validate($data)); // false, because we created a token that cannot be used before of `time() + 60`

$data->setCurrentTime(time() + 61); // changing the validation time to future

var_dump($token->validate($data)); // true, because validation information is equals to data contained on the token

$data->setCurrentTime(time() + 4000); // changing the validation time to future

var_dump($token->validate($data)); // false, because token is expired since current time is greater than exp
```

We can also use the $leeway parameter to deal with clock skew (see notes below).
If token's claimed time is invalid but the difference between that and the validation time is less than $leeway,
then token is still considered valid
```php
'components' => [
    'jwt' => [
        'class' => \sizeg\jwt\Jwt:class,
        'key'   => 'secret',
        'jwtValidationData' => [
            'class' => \sizeg\jwt\JwtValidationData::class,
             // configure leeway 
            'leeway' => 20,
        ],
    ],
],
```

```php
$dataWithLeeway = Yii::$app->jwt->getValidationData();
$dataWithLeeway->setIssuer('http://example.com');
$dataWithLeeway->setAudience('http://example.org');
$dataWithLeeway->setId('4f1g23a12aa');

var_dump($token->validate($dataWithLeeway)); // false, because token can't be used before now() + 60, not within leeway

$dataWithLeeway->setCurrentTime($time + 61); // changing the validation time to future

var_dump($token->validate($dataWithLeeway)); // true, because current time plus leeway is between "nbf" and "exp" claims

$dataWithLeeway->setCurrentTime($time + 3610); // changing the validation time to future but within leeway

var_dump($token->validate($dataWithLeeway)); // true, because current time - 20 seconds leeway is less than exp

$dataWithLeeway->setCurrentTime($time + 4000); // changing the validation time to future outside of leeway

var_dump($token->validate($dataWithLeeway)); // false, because token is expired since current time is greater than exp
```

#### Important

* You have to configure `ValidationData` informing all claims you want to validate the token.
* If `ValidationData` contains claims that are not being used in token or token has claims that are not configured in `ValidationData` they will be ignored by `Token::validate()`.
* `exp`, `nbf` and `iat` claims are configured by default in `ValidationData::__construct()` with the current UNIX time (`time()`).
* The optional `$leeway` parameter of `ValidationData` will cause us to use that number of seconds of leeway when validating the time-based claims,
pretending we are further in the future for the "Issued At" (`iat`) and "Not Before" (`nbf`) claims and pretending we are further in the past
for the "Expiration Time" (`exp`) claim. This allows for situations where the clock of the issuing server has a different time than the clock
of the verifying server, as mentioned in section 4.1 of RFC 7519.

<a name="tokensign"></a>
## Token signature

We can use signatures to be able to verify if the token was not modified after its generation.
This extension implements Hmac, RSA and ECDSA signatures (using 256, 384 and 512).

### Important

Do not allow the string sent to the Parser to dictate which signature algorithm to use,
or else your application will be vulnerable to a [critical JWT security vulnerability](https://auth0.com/blog/2015/03/31/critical-vulnerabilities-in-json-web-token-libraries).

The examples below are safe because the choice in `Signer` is hard-coded and cannot be influenced by malicious users.

<a name="tokensign-hmac"></a>
### Hmac

Hmac signatures are really simple to be used:

```php
$jwt = Yii::$app->jwt;
$signer = $jwt->getSigner('HS256');
$key = $jwt->getKey();
$time = time();

$token = $jwt->getBuilder()
            ->issuedBy('http://example.com') // Configures the issuer (iss claim)
            ->permittedFor('http://example.org') // Configures the audience (aud claim)
            ->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($time + 60) // Configures the time that the token can be used (nbf claim)
            ->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', 1) // Configures a new claim, called "uid"
            ->getToken($signer, $key); // Retrieves the generated token

var_dump($token->verify($signer, 'testing 1')); // false, because the key is different
var_dump($token->verify($signer, 'testing')); // true, because the key is the same
```

<a name="tokensign-rsa-ecdsa"></a>
### RSA and ECDSA

RSA and ECDSA signatures are based on public and private keys so you have to generate using the private key and verify using the public key:

```php
$jwt = Yii::$app->jwt;
$signer = $jwt->getSigner('RS256'); // you can use 'ES256' if you're using ECDSA keys
$privateKey = $jwt->getKey('file://{path to your private key}');
$time = time();

$token = $jwt->getBuilder()
            ->issuedBy('http://example.com') // Configures the issuer (iss claim)
            ->permittedFor('http://example.org') // Configures the audience (aud claim)
            ->identifiedBy('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($time) // Configures the time that the token was issue (iat claim)
            ->canOnlyBeUsedAfter($time + 60) // Configures the time that the token can be used (nbf claim)
            ->expiresAt($time + 3600) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', 1) // Configures a new claim, called "uid"
            ->getToken($signer, $privateKey); // Retrieves the generated token
            
$publicKey = $jwt->getKey('file://{path to your public key}');

var_dump($token->verify($signer, $publicKey)); // true when the public key was generated by the private one =)
```

**It's important to say that if you're using RSA keys you shouldn't invoke ECDSA signers (and vice-versa), otherwise ```sign()``` and ```verify()``` will raise an exception!**

<a name="yii2basic-example"></a>
## Yii2 basic template example

### Basic scheme

1. Client send credentials. For example, login + password
2. Backend validate them
3. If credentials is valid client receive token
4. Client store token for the future requests

### Step-by-step usage example

1. Create Yii2 application

    In this example we will use [basic template](https://github.com/yiisoft/yii2-app-basic), but you can use [advanced template](https://github.com/yiisoft/yii2-app-advanced) in the same way.

    ```shell
    composer create-project --prefer-dist --stability=dev yiisoft/yii2-app-basic yii2-jwt-test
    ```

2. Install component

    ```shell
    composer require sizeg/yii2-jwt
    ```

3. Add to config/web.php into `components` section

    ```php
    $config = [
        'components' => [
            // other default components here..
            'jwt' => [
                'class' => \sizeg\jwt\Jwt::class,
                'key' => 'secret',
                // You have to configure ValidationData informing all claims you want to validate the token.
                'jwtValidationData' => \app\components\JwtValidationData::class,
            ],
        ],
    ];
    ```
    
4. Create JwtValidationData class. Where you have to configure ValidationData informing all claims you want to validate the token.
 
    ```php
    <?php
    
    namespace app\components;
    
    class JwtValidationData extends \sizeg\jwt\JwtValidationData
    {
     
        /**
         * @inheritdoc
         */
        public function init()
        {
            $this->validationData->setIssuer('http://example.com');
            $this->validationData->setAudience('http://example.org');
            $this->validationData->setId('4f1g23a12aa');
    
            parent::init();
        }
    }    
    ```

5. Change method `app\models\User::findIdentityByAccessToken()`

    ```php
        /**
         * {@inheritdoc}
         * @param \Lcobucci\JWT\Token $token
         */
        public static function findIdentityByAccessToken($token, $type = null)
        {
            foreach (self::$users as $user) {
                if ($user['id'] === (string) $token->getClaim('uid')) {
                    return new static($user);
                }
            }
    
            return null;
        }
    ```

6. Create controller

    ```php
    <?php
    
    namespace app\controllers;
    
    use sizeg\jwt\Jwt;
    use sizeg\jwt\JwtHttpBearerAuth;
    use Yii;
    use yii\rest\Controller;
    
    class RestController extends Controller
    {
        /**
         * @inheritdoc
         */
        public function behaviors()
        {
            $behaviors = parent::behaviors();
            $behaviors['authenticator'] = [
                'class' => JwtHttpBearerAuth::class,
                'optional' => [
                    'login',
                ],
            ];
    
            return $behaviors;
        }
    
        /**
         * @return \yii\web\Response
         */
        public function actionLogin()
        {
            /** @var Jwt $jwt */
            $jwt = Yii::$app->jwt;
            $signer = $jwt->getSigner('HS256');
            $key = $jwt->getKey();
            $time = time();
    
            // Previous implementation
            /*
            $token = $jwt->getBuilder()
                ->setIssuer('http://example.com')// Configures the issuer (iss claim)
                ->setAudience('http://example.org')// Configures the audience (aud claim)
                ->setId('4f1g23a12aa', true)// Configures the id (jti claim), replicating as a header item
                ->setIssuedAt(time())// Configures the time that the token was issue (iat claim)
                ->setExpiration(time() + 3600)// Configures the expiration time of the token (exp claim)
                ->set('uid', 100)// Configures a new claim, called "uid"
                ->sign($signer, $jwt->key)// creates a signature using [[Jwt::$key]]
                ->getToken(); // Retrieves the generated token
            */
    
            // Adoption for lcobucci/jwt ^4.0 version
            $token = $jwt->getBuilder()
                ->issuedBy('http://example.com')// Configures the issuer (iss claim)
                ->permittedFor('http://example.org')// Configures the audience (aud claim)
                ->identifiedBy('4f1g23a12aa', true)// Configures the id (jti claim), replicating as a header item
                ->issuedAt($time)// Configures the time that the token was issue (iat claim)
                ->expiresAt($time + 3600)// Configures the expiration time of the token (exp claim)
                ->withClaim('uid', 100)// Configures a new claim, called "uid"
                ->getToken($signer, $key); // Retrieves the generated token
    
            return $this->asJson([
                'token' => (string)$token,
            ]);
        }
    
        /**
         * @return \yii\web\Response
         */
        public function actionData()
        {
            return $this->asJson([
                'success' => true,
            ]);
        }
    }
    ```

7. Send simple login request to get token. Here we does not send any credentials to simplify example. As we specify in `authenticator` behavior action `login` as optional the `authenticator` skip auth check for that action.
![image](https://user-images.githubusercontent.com/4047591/54614758-c4d2e100-4a7e-11e9-9175-0f1742bf4047.png)

8. First of all we try to send request to rest/data without token and getting error `Unauthorized`
![image](https://user-images.githubusercontent.com/4047591/54615287-a3262980-4a7f-11e9-81a9-609f5cb443c7.png)

9. Then we retry request but already adding `Authorization` header with our token
![image](https://user-images.githubusercontent.com/4047591/54615245-8ee22c80-4a7f-11e9-9948-e3f801596c43.png)
