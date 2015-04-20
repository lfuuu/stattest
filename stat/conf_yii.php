<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

if (php_sapi_name() != 'cli') {
    $config = require(__DIR__ . '/../config/web.php');
    new yii\web\Application($config);
} else {
    $config = require(__DIR__ . '/../config/console.php');
    new yii\console\Application($config);
}

include PATH_TO_ROOT."conf.php";


global $design, $user;

if (!defined('NO_WEB')) {
    $design = new \MySmarty();
    $user = new \AuthUser();
}

use app\models\User;
Yii::$app->user->setIdentity(User::findOne(User::SYSTEM_USER_ID));
