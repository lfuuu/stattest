<?php

use app\models\EventQueue;

define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT . "conf_yii.php";
include INCLUDE_PATH . "runChecker.php";

echo "\n" . date("r") . ":";


EventQueue::go(EventQueue::CHECK__USAGES);
