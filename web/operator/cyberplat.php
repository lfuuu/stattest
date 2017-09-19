<?php
use app\classes\payments\cyberplat\CyberplatProcessor;
use app\models\Organization;

define("PATH_TO_ROOT", '../../stat/');
include PATH_TO_ROOT . "conf_yii.php";


(new CyberplatProcessor())
    ->setOrganization([Organization::MCN_TELECOM, Organization::MCN_TELECOM_RETAIL])
    ->proccessRequest()
    ->echoAnswer();