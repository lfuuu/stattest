<?php

use \yii\helpers\ArrayHelper;
use yii\web\Response;

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
            'parsers' => [ 'application/json' => 'yii\web\JsonParser'],
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
                'date' => ['pluginOptions' => ['autoclose' => true]], // example
                'datetime' => [], // setup if needed
                'time' => [], // setup if needed
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
}

return $config;
