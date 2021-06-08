<?php
/**
 * @Author: qinuoyun
 * @Date:   2020-11-07 10:12:05
 * @Last Modified by:   wiki
 * @Last Modified time: 2021-03-15 19:09:22
 */
$components = [
    'components' => [
        'request'      => [
            'cookieValidationKey'  => 'Dei5M1Vbm4hupiy0JRNqCOrGu_9KfhKU',
            'enableCsrfValidation' => false,
            //处理提交接收数据为JSON
            'parsers'              => [
                'application/json' => 'yii\web\JsonParser',
                'text/json'        => 'yii\web\JsonParser',
            ],
        ],
        //加密授权类
        'jwt'          => [
            'class'             => \sizeg\jwt\Jwt::class,
            //加密的KEY
            'key'               => 'www_heshop_com',
            'jwtValidationData' => [
                'class'  => \sizeg\jwt\JwtValidationData::class,
                //允许超时的范围
                'leeway' => 20,
            ],
        ],
        'user'         => [
            'identityClass'   => 'system\models\Account',
            'enableAutoLogin' => true,
            'enableSession'   => false,
        ],
        'urlManager'   => [
            'enablePrettyUrl'     => true,
            'showScriptName'      => false,
            'enableStrictParsing' => true,
            'rules'               => [
                'GET <controller:[\w-]+>'                             => '<controller>/index',
                'GET <controller:[\w-]+>/<id:\d+>'                    => '<controller>/view',

                'POST <controller:[\w-]+>'                            => '<controller>/create',
                'PUT <controller:[\w-]+>'                             => '<controller>/update',
                'PUT <controller:[\w-]+>/<id:\d+>'                    => '<controller>/update',
                'PUT <controller:[\w-]+>/<id:(\d+,)*\d+$>'            => '<controller>/update',

                'DELETE <controller:[\w-]+>/<id:(\d+,)*\d+$>'         => '<controller>/delete',
                'OPTIONS <module>/<controller:\w+>'                   => '<controller>/options',
                'OPTIONS <module>/<controller:\w+>/<id:(\d+,)*\d+$>'  => '<controller>/options',

                'GET <controller:[\w-]+>/<action>'                    => '<controller>/<action>',
                'GET <controller:\w+>/<action>'                       => '<controller>/<action>',
                'POST <controller:[\w-]+>/<action>'                   => '<controller>/<action>',
            ],
        ],
        'errorHandler' => [
            'class' => 'framework\common\ErrorHandler',
        ],
        'payment'      => [
            'class' => \app\components\Payment::class,
        ],
        'sms'          => [
            'class' => \app\components\Sms::class,
        ],
        'express'      => [
            'class' => \app\components\Express::class,
        ],
        'cloud'        => [
            'class' => \app\components\cloud\Cloud::class,
        ],
        'subscribe'    => [
            'class' => \app\components\subscribe\Subscribe::class,
        ],
        'crontab'      => [
            'class' => \app\components\crontab\Crontab::class,
        ]
    ],
];

return $components;
