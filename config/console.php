<?php
use yii\helpers\ArrayHelper;

Yii::setAlias('@app', dirname(__DIR__));
Yii::setAlias('@tests', dirname(__DIR__) . '/tests');
Yii::setAlias('@webroot', Yii::getAlias('@app/web'));
Yii::setAlias('@web', Yii::getAlias('@app/web'));

$cacheRedis = require(__DIR__ . '/cache_redis.php');
if (file_exists($file = __DIR__ . '/cache_redis.local.php')) {
    $cacheRedis = ArrayHelper::merge($cacheRedis, require($file));
}

$db = require(__DIR__ . '/db_stat.php');
if (file_exists($file = __DIR__ . '/db_stat.local.php')) {
    $db = ArrayHelper::merge($db, require($file));
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

$dbPgLk = require(__DIR__ . '/db_pg_lk.php');
if (file_exists($file = __DIR__ . '/db_pg_lk.local.php')) {
    $dbPgLk = ArrayHelper::merge($dbPgLk, require($file));
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
    $dbPgCallTracking = ArrayHelper::merge($dbPgNnp, require($file));
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

return [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'language' => 'ru-RU',
    'timeZone' => 'UTC',
    'components' => [
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
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\User',
            'enableSession' => false,
            'enableAutoLogin' => false,
        ],
        'authManager' => [
            'class' => 'app\classes\AuthManager',
        ],
        'assetManager' => [
            'appendTimestamp' => true,
            'hashCallback' => static function ($path) {
                return hash('crc32', $path);
            },
        ],
        'view' => [
            'title' => 'stat - MCN Телеком',
            'class' => 'app\classes\BaseView',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'baseUrl' => false,
        ],
        'log' => $log,
        'db' => $db,
        'dbSms' => $dbSms,
        'dbAts' => $dbAts,
        'dbAts2' => $dbAts2,
        'dbPgAts' => $dbPgAts,
        'dbPgNnp' => $dbPgNnp,
        'dbPgNnp2' => $dbPgNnp2,
        'dbPgNnpSlave' => $dbPgSlave,
        'dbPgCallTracking' => $dbPgCallTracking,
        'dbPgNfDump' => $dbPgNfDump,
        'dbPgLk' => $dbPgLk,
        'dbPg' => $dbPg,
        'dbPgSlave' => $dbPgSlave,
        'dbPgSlaveCache' => $dbPgSlaveCache,
        'dbPgCache' => $dbPgCache,
        'dbPgStatistic' => $dbStatistic,
        'dbHistory' => $dbHistory,
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
        ],
    ],
    'modules' => [
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
        'freeNumber' => ['class' => 'app\modules\freeNumber\Module'],
        'async' => ['class' => 'app\modules\async\Module'],
        'sbisTenzor' => ['class' => 'app\modules\sbisTenzor\Module',],
        'sorm' => ['class' => 'app\modules\sorm\Module',],
    ],
    'params' => $params,
];