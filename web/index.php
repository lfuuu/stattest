<?php

if (getenv('YII_ENV')) {
    define('YII_ENV', getenv('YII_ENV'));
}

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

if (YII_ENV !== 'test') {
    $config = require(__DIR__ . '/../config/web.php');
} else {
    $config = require(__DIR__ . '/../tests/codeception/config/acceptance.php');
}

(new app\classes\WebApplication($config))->run();
