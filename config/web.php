<?php
use \yii\helpers\ArrayHelper;

Yii::setAlias('@app', dirname(__DIR__));

$db = require(__DIR__ . '/db_stat.php');
if (file_exists($file = __DIR__ . '/db_stat.local.php')) {
    $db = ArrayHelper::merge($db, require($file));
}

$dbPg = require(__DIR__ . '/db_pgsql.php');
if (file_exists($file = __DIR__ . '/db_pgsql.local.php')) {
    $dbPg = ArrayHelper::merge($dbPg, require($file));
}

$log = require(__DIR__ . '/log.php');
if (file_exists($file = __DIR__ . '/log.local.php')) {
    $log = ArrayHelper::merge($log, require($file));
}

$params = require(__DIR__ . '/params.php');
if (file_exists($file = __DIR__ . '/params.local.php')) {
    $params = ArrayHelper::merge($params, require($file));
}

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru',
    'timeZone' => 'UTC',
    'components' => [
        'view' => [
            'title' => 'stat - MCN Телеком',
        ],
        'request' => [
            'class' => 'app\classes\Request',
            'cookieValidationKey' => 'HGjhg78gUJ78234gh2jGYUgh38',
            'parsers' => [ 'application/json' => 'yii\web\JsonParser' ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
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
            'useFileTransport' => true,
        ],
        'log' => $log,
        'db' => $db,
        'dbPg' => $dbPg,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
//            'enableStrictParsing' => true,
            'rules' => array(
//                '<controller:\w+>' => '<controller>/index',
//                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            )
        ],
    ],
    'modules' => [
        'gridview' =>  [
            'class' => '\kartik\grid\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment

}

return $config;
