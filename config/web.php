<?php

use yii\helpers\ArrayHelper;
use yii\web\Response;

Yii::setAlias('@app', dirname(__DIR__));

$session = require(__DIR__ . '/session.php');
if (file_exists($file = __DIR__ . '/session.local.php')) {
    $session = ArrayHelper::merge($session, require($file));
}

$cacheRedis = require(__DIR__ . '/cache_redis.php');
if (file_exists($file = __DIR__ . '/cache_redis.local.php')) {
    $cacheRedis = ArrayHelper::merge($cacheRedis, require($file));
}

$dbConf = require(__DIR__ . '/db_stat.php');
if (file_exists($file = __DIR__ . '/db_stat.local.php')) {
    $dbConf = ArrayHelper::merge($dbConf, require($file));
}

$dbSms = require(__DIR__ . '/db_sms.php');
if (file_exists($file = __DIR__ . '/db_sms.local.php')) {
    $dbSms = ArrayHelper::merge($dbSms, require($file));
}

$dbPg = require(__DIR__ . '/db_pgsql.php');
if (file_exists($file = __DIR__ . '/db_pgsql.local.php')) {
    $dbPg = ArrayHelper::merge($dbPg, require($file));
}

$dbPgSlave = require(__DIR__ . '/db_pg_slave.php');
if (file_exists($file = __DIR__ . '/db_pg_slave.local.php')) {
    $dbPgSlave = ArrayHelper::merge($dbPgSlave, require($file));
}

$dbPgSlaveCache = require(__DIR__ . '/db_pg_slave_cache.php');
if (file_exists($file = __DIR__ . '/db_pg_slave_cache.local.php')) {
    $dbPgSlaveCache = ArrayHelper::merge($dbPgSlaveCache, require($file));
}

$dbPgCache = require(__DIR__ . '/db_pg_cache.php');
if (file_exists($file = __DIR__ . '/db_pg_cache.local.php')) {
    $dbPgCache = ArrayHelper::merge($dbPgCache, require($file));
}

$dbPgNfDump = require(__DIR__ . '/db_pgsql_nfdump.php');
if (file_exists($file = __DIR__ . '/db_pgsql_nfdump.local.php')) {
    $dbPgNfDump = ArrayHelper::merge($dbPgNfDump, require($file));
}

$dbPgCallLegs = require(__DIR__ . '/db_pg_calllegs.php');
if (file_exists($file = __DIR__ . '/db_pg_calllegs.local.php')) {
    $dbPgCallLegs = ArrayHelper::merge($dbPgCallLegs, require($file));
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

$dbPgNnp2 = require(__DIR__ . '/db_pg_nnp2.php');
if (file_exists($file = __DIR__ . '/db_pg_nnp2.local.php')) {
    $dbPgNnp2 = ArrayHelper::merge($dbPgNnp2, require($file));
}

$dbPgCallTracking = require(__DIR__ . '/db_pg_call_tracking.php');
if (file_exists($file = __DIR__ . '/db_pg_call_tracking.local.php')) {
    $dbPgCallTracking = ArrayHelper::merge($dbPgCallTracking, require($file));
}

$dbStatistic = require(__DIR__ . '/db_pgsql.php');
if (file_exists($file = __DIR__ . '/db_pg_statistic.local.php')) {
    $dbStatistic = ArrayHelper::merge($dbStatistic, require($file));
} else {
    $dbStatistic = $dbPg;
}

$dbHistory = require(__DIR__ . '/db_pgsql.php');
if (file_exists($file = __DIR__ . '/db_pg_history.local.php')) {
    $dbHistory = ArrayHelper::merge($dbHistory, require($file));
} else {
    $dbHistory = $dbPg;
}

$log = require(__DIR__ . '/log.php');
if (file_exists($file = __DIR__ . '/log.local.php')) {
    $log = ArrayHelper::merge($log, require($file));
}

$params = require(__DIR__ . '/params.php');
if (file_exists($file = __DIR__ . '/params.local.php')) {
    $params = ArrayHelper::merge($params, require($file));
}

$logAAAPath = Yii::getAlias('@app/runtime/log_aaa.config.php');
if (file_exists($logAAAPath)) {
    $params['isLogAAA'] = include $logAAAPath;
}

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
//    'aliases' => [
//        '@bower' => '@vendor/bower-asset',
//        '@npm'   => '@vendor/npm-asset',
//    ],
    'language' => 'ru-RU',
    'timeZone' => 'UTC',
    'components' => [
        'session' => $session,
        'view' => [
            'title' => 'stat - MCN Телеком',
            'class' => 'app\classes\BaseView',
        ],
        'request' => [
            'class' => 'app\classes\Request',
            'cookieValidationKey' => 'HGjhg78gUJ78234gh2jGYUgh38',
            'parsers' => ['application/json' => 'yii\web\JsonParser'],
        ],
        'response' => [
            'formatters' => [
                Response::FORMAT_HTML => 'yii\web\HtmlResponseFormatter',
                Response::FORMAT_XML => 'yii\web\XmlResponseFormatter',
                Response::FORMAT_JSON => 'app\classes\JsonResponseFormatter',
                Response::FORMAT_JSONP => [
                    'class' => 'app\classes\JsonResponseFormatter',
                    'useJsonp' => true,
                ],
            ],
        ],
        'assetManager' => [
            'appendTimestamp' => true,
            'hashCallback' => static function ($path) {
                return hash('crc32', $path);
            },
        ],
            /*
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'dirMode' => 0777,
            'fileMode' => 0666,
        ],*/
        'redis' => $cacheRedis,
        'cache' => [
            'class' => 'yii\redis\Cache',
            'keyPrefix' => 'stat:',
        ],
        'cacheDb' => [
            'class' => 'yii\caching\DbCache',
            'cacheTable' => 'z_cache',
            'db' => 'db2',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'authManager' => [
            'class' => 'app\classes\AuthManager',
        ],
        'errorHandler' => [
            'class' => 'app\classes\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            // 'useFileTransport' => true,
        ],
        'log' => $log,
        'db' => $dbConf,
        'db2' => $dbConf, // for working with the database outside of a main transaction
        'dbSms' => $dbSms,
        'dbPg' => $dbPg,
        'dbPgSlave' => $dbPgSlave,
        'dbPgSlaveCache' => $dbPgSlaveCache,
        'dbPgCache' => $dbPgCache,
        'dbPgNfDump' => $dbPgNfDump,
        'dbPgCallLegs' => $dbPgCallLegs,
        'dbAts' => $dbAts,
        'dbAts2' => $dbAts2,
        'dbPgAts' => $dbPgAts,
        'dbPgStatistic' => $dbStatistic,
        'dbroPlatforma' => [
            'class' => 'app\classes\DBROConnection',
            'url' => 'http://dbro.mcn.ru/dbro'
        ],
        'dbPgNnp' => $dbPgNnp,
        'dbPgNnp2' => $dbPgNnp2,
        'dbPgNnpSlave' => $dbPgSlave,
        'dbPgCallTracking' => $dbPgCallTracking,
        'dbHistory' => $dbHistory,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            // 'enableStrictParsing' => true,
            'rules' => [
                // '<controller:\w+>' => '<controller>/index',
                // '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ]
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'forceTranslation' => true,
                    // 'sourceLanguage' => 'ru-RU'
                ],
                'biller' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'forceTranslation' => true,
                ],
                'biller-voip' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'forceTranslation' => true,
                ],
            ],
        ],
        'mutex' => [
//            'class' => 'yii\redis\Mutex',
            'class' => 'yii\mutex\MysqlMutex',
        ]
    ],
    'modules' => [
        'gridview' => [
            'class' => '\kartik\grid\Module',
        ],
        'datecontrol' => [
            'class' => 'kartik\datecontrol\Module',
            // format settings for displaying each date attribute (ICU format example)
            'displaySettings' => [
                'date' => 'dd.MM.yyyy',
                'time' => 'HH:mm:ss a',
                'datetime' => 'dd.MM.yyyy HH:mm:ss a',
            ],
            // format settings for saving each date attribute (PHP format example)
            'saveSettings' => [
                'date' => 'php:Y-m-d', // saves as unix timestamp
                'time' => 'php:H:i:s',
                'datetime' => 'php:Y-m-d H:i:s',
            ],
            // default settings for each widget from kartik\widgets used when autoWidget is true
            'autoWidgetSettings' => [
                'date' => ['pluginOptions' => ['autoclose' => true, 'todayBtn' => 'linked']], // example
                'datetime' => [], // setup if needed
                'time' => [], // setup if needed
            ],
        ],
        'glpi' => ['class' => 'app\modules\glpi\Module'],
        'nnp' => ['class' => 'app\modules\nnp\Module'],
        'nnp2' => ['class' => 'app\modules\nnp2\Module'],
        'callTracking' => ['class' => 'app\modules\callTracking\Module'],
        'uu' => ['class' => 'app\modules\uu\Module'],
        'socket' => ['class' => 'app\modules\socket\Module'],
        'webhook' => ['class' => 'app\modules\webhook\Module'],
        'notifier' => ['class' => 'app\modules\notifier\Module'],
        'atol' => ['class' => 'app\modules\atol\Module'],
        'transfer' => ['class' => 'app\modules\transfer\Module'],
        'mtt' => ['class' => 'app\modules\mtt\Module'],
        'sim' => ['class' => 'app\modules\sim\Module'],
        'payments' => ['class' => 'app\modules\payments\Module'],
        'mchs' => ['class' => 'app\modules\mchs\Module'],
        'freeNumber' => ['class' => 'app\modules\freeNumber\Module'],
        'async' => ['class' => 'app\modules\async\Module'],
        'sbisTenzor' => ['class' => 'app\modules\sbisTenzor\Module',],
        'sorm' => ['class' => 'app\modules\sorm\Module',],
    ],
    'params' => $params,
];

if (file_exists($file = __DIR__ . '/web.local.php')) {
    $config = ArrayHelper::merge($config, require($file));
}

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
}

return $config;