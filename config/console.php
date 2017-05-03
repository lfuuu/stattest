<?php
use yii\helpers\ArrayHelper;

Yii::setAlias('@app', dirname(__DIR__));
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$db = require(__DIR__ . '/db_stat.php');
if (file_exists($file = __DIR__ . '/db_stat.local.php')) {
    $db = ArrayHelper::merge($db, require($file));
}

$dbPg = require(__DIR__ . '/db_pgsql.php');
if (file_exists($file = __DIR__ . '/db_pgsql.local.php')) {
    $dbPg = ArrayHelper::merge($dbPg, require($file));
}

$dbPgSlave = require(__DIR__ . '/db_pg_slave.php');
if (file_exists($file = __DIR__ . '/db_pg_slave.local.php')) {
    $dbPgSlave = ArrayHelper::merge($dbPgSlave, require($file));
}

$dbPgNfDump = require(__DIR__ . '/db_pgsql_nfdump.php');
if (file_exists($file = __DIR__ . '/db_pgsql_nfdump.local.php')) {
    $dbPgNfDump = ArrayHelper::merge($dbPgNfDump, require($file));
}

$dbAts = require(__DIR__ . '/db_ats.php');
if (file_exists($file = __DIR__ . '/db_ats.local.php')) {
    $dbAts = ArrayHelper::merge($dbAts, require($file));
}

$dbAts2 = require(__DIR__ . '/db_ats2.php');
if (file_exists($file = __DIR__ . '/db_ats2.local.php')) {
    $dbAts2 = ArrayHelper::merge($dbAts2, require($file));
}

$dbPgAts = require(__DIR__ . '/db_pg_ats.php');
if (file_exists($file = __DIR__ . '/db_pg_ats.local.php')) {
    $dbPgAts = ArrayHelper::merge($dbPgAts, require($file));
}

$dbPgNnp = require(__DIR__ . '/db_pg_nnp.php');
if (file_exists($file = __DIR__ . '/db_pg_nnp.local.php')) {
    $dbPgNnp = ArrayHelper::merge($dbPgNnp, require($file));
}

$log = require(__DIR__ . '/log.php');
if (file_exists($file = __DIR__ . '/log.local.php')) {
    $log = ArrayHelper::merge($log, require($file));
}

$params = require(__DIR__ . '/params.php');
if (file_exists($file = __DIR__ . '/params.local.php')) {
    $params = ArrayHelper::merge($params, require($file));
}

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'language' => 'ru-RU',
    'timeZone' => 'UTC',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'dirMode' => 0777
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'enableAutoLogin' => false,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'baseUrl' => false,
        ],
        'log' => $log,
        'db' => $db,
        'dbAts' => $dbAts,
        'dbAts2' => $dbAts2,
        'dbPgAts' => $dbPgAts,
        'dbPgNnp' => $dbPgNnp,
        'dbPgNfDump' => $dbPgNfDump,
        'dbPg' => $dbPg,
        'dbPgSlave' => $dbPgSlave,
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    // 'sourceLanguage' => 'ru-RU'
                ],
            ],
        ],
    ],
    'modules' => [
        'glpi' => [
            'class' => 'app\modules\glpi\Module',
        ],
        'nnp' => [
            'class' => 'app\modules\nnp\Module',
        ],
        'uu' => [
            'class' => 'app\modules\uu\Module',
        ],
        'socket' => [
            'class' => 'app\modules\socket\Module',
        ],
        'webhook' => [
            'class' => 'app\modules\webhook\Module',
        ],
        'notifier' => [
            'class' => 'app\modules\notifier\Module',
        ],
    ],
    'params' => $params,
];
