<?php
$common = [
    'aliases'    => [
        '@bower'    => '@vendor/bower-asset',
        '@npm'      => '@vendor/npm-asset',
        '@leadmall' => '@app',
    ],
    'bootstrap'  => ['log'],
    'modules'    => [
        "leadmall" => [
            'class' => 'leadmall\Module',
        ],
    ],
    'components' => [
        'cache'        => [
            'class' => 'yii\caching\FileCache',
        ],
        'assetManager' => [
            'linkAssets' => true,
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => ['_GET', '_POST', '_FILES']
                ],
            ],
        ],
    ],
];

return $common;
