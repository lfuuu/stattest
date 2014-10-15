<?php

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

if (isset($_GET['module']) || isset($_POST['module'])) {
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

$config = require(__DIR__ . '/../config/web.php');

(new app\classes\Application($config))->run();