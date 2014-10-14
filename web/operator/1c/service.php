<?php
define("PATH_TO_ROOT",'../../../stat/');
require_once "../../../stat/conf_yii.php";

Header('Content-type: text/xml; charset="UTF-8"');

if (isset($_GET['wsdl'])){

    echo Sync1C::me()->serverGetWsdl();
    exec("rm /tmp/wsdl-*");

} else {

    Sync1C::me()->serverProcessRequest();

}