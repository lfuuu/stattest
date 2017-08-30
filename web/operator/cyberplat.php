<?php
use app\classes\payments\cyberplat\CyberplatProcessor;

define("PATH_TO_ROOT", '../../stat/');
include PATH_TO_ROOT . "conf_yii.php";


(new CyberplatProcessor())->proccessRequest()->echoAnswer();




