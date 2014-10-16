<?php

// NOTE: Make sure this file is not accessible when deployed to production
if (!in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('You are not allowed to access this file.');
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

if (isset($_GET['module']) || isset($_POST['module'])) {
    $_SERVER['REQUEST_URI_ORIG'] = $_SERVER['REQUEST_URI'];
    if (strpos($_SERVER['REQUEST_URI'], '/index.php') === 0) {
        $_SERVER['REQUEST_URI'] = '/compatibility/index' . substr($_SERVER['REQUEST_URI'], 10);
    } elseif (strpos($_SERVER['REQUEST_URI'], '/index_lite.php') === 0) {
        $_SERVER['REQUEST_URI'] = '/compatibility/lite' . substr($_SERVER['REQUEST_URI'], 15);
    } else {
        $_SERVER['REQUEST_URI'] = '/compatibility/index' . substr($_SERVER['REQUEST_URI'], 1);
    }
}

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../tests/codeception/config/acceptance.php');

(new app\classes\WebApplication($config))->run();