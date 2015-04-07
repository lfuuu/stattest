<?php
use \yii\helpers\ArrayHelper;

Yii::setAlias('@app', dirname(__DIR__));
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$db = ArrayHelper::merge(
    require(__DIR__ . '/db_stat.php'),
    require(__DIR__ . '/db_stat.local.php')
);

$dbPg = ArrayHelper::merge(
    require(__DIR__ . '/db_pgsql.php'),
    require(__DIR__ . '/db_pgsql.local.php')
);

$log = ArrayHelper::merge(
    require(__DIR__ . '/log.php'),
    include(__DIR__ . '/log.local.php')
);

$params = require(__DIR__ . '/params.php');

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'timeZone' => 'UTC',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'enableAutoLogin' => false,
        ],
        'log' => $log,
        'db' => $db,
        'dbPg' => $dbPg,
    ],
    'params' => $params,
];
