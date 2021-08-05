<?php
$db = file_exists(__DIR__ . '/db.php') ? require(__DIR__ . '/db.php') : [
    'class'       => 'yii\db\Connection',
    'dsn'         => '',
    'username'    => '',
    'password'    => '',
    'charset'     => '',
    'tablePrefix' => '',
    'attributes'  => [
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ],
];
$params  = require __DIR__ . '/params.php';
$aliases = require __DIR__ . '/aliases.php';

$config = [
    'id'         => 'basic',
    'language'   => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'aliases'    => $aliases,
    'basePath'   => dirname(__DIR__),
    'components' => [
        'db'         => $db
    ],
    'params'     => $params,
];

return $config;
