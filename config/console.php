<?php
$db     = require __DIR__ . '/db.php';
$params = require __DIR__ . '/params.php';

$config = [
    'id'         => 'basic-console',
    'basePath'   => dirname(__DIR__),
    'components' => [
        'db'    => $db,
        'redis' => [
            'class'    => \yii\redis\Connection::class,
            'hostname' => '127.0.0.1',
            'port'     => 6379,
            'database' => 0,
        ],
    ],
    'params'     => $params,
];

return $config;
