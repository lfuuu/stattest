<?php
define("PATH_TO_ROOT",'../../../stat/');
require_once "../../../stat/conf_yii.php";

use app\models\User;
Yii::$app->user->setIdentity(User::findOne(User::SYSTEM_USER_ID));

Header('Content-type: text/xml; charset="UTF-8"');

if (isset($_GET['wsdl'])){

    echo Sync1C::me()->serverGetWsdl();
    exec("rm /tmp/wsdl-*");

} else {

    Sync1C::me()->serverProcessRequest();

}
