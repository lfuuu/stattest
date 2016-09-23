<?php

use app\classes\Event;

define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT . "conf_yii.php";
include INCLUDE_PATH . "runChecker.php";

echo "\n" . date("r") . ":";


Event::go(Event::CHECK__USAGES);
