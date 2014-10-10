<?php
define('token','43fb37aba737439f2ae2fa5da242d310ed3939d087c7926765e9e13e593b5772a706248935d573312fa3061cf6a71b4477c2e851c28284e8df346bc19de19ddf');

define("PATH_TO_ROOT",'../../');

$sPath = strtolower($_SERVER["SCRIPT_FILENAME"]);
$isTest = (strpos($sPath, "tst") !== false || strpos($sPath, "test") !== false);

if($isTest)
{
    $_1cUrl = "http://stattest.ws.dionis.mcn.ru/ws/ws/stat";
    $statUrl = "https://stat.mcn.ru/tst/1c/proxy.php?service";
    $getUrl = "http://web_service:sdfg94w758ht23g4r78394g@stattest.ws.dionis.mcn.ru/ws/ws/stat?wsdl";
}else{
    if(!isset($_GET[token])){
        Header('HTTP/1.0 404 Not Found');
        exit();
    }
    $_1cUrl = "http://stat.ws.dionis.mcn.ru/ws/ws/stat";
    $statUrl = "https://stat.mcn.ru/operator/1c/proxy.php?service&amp;".token;
    $getUrl = "http://web_service:sdfg94w758ht23g4r78394g@stat.ws.dionis.mcn.ru/ws/ws/stat?wsdl";
}

if(isset($_GET['wsdl'])){
	Header('Content-type: text/xml; charset="UTF-8"');
	$test = isset($_GET['test'])?'&amp;test':'';

	echo str_replace($_1cUrl, $statUrl, file_get_contents($getUrl));
	exit(0);
}

error_reporting(E_ALL);
ini_set('soap.wsdl_cache_enabled', '0');

require_once "../../conf.php";
require_once INCLUDE_PATH."MyDBG.php";
require_once INCLUDE_PATH."1c_integration.php";

if(isset($_GET["service"]))
{
    $s = new \_1c\server('wsdl.xml');
    $s->handle();
}else{
    $execStr = "wget -q ".$getUrl." -O- | sed -e \"s/".str_replace("/","\/", $_1cUrl)."/".str_replace(array("/", "&"), array("\/", "\&"), $statUrl)."/g\" > wsdl.xml";
    exec($execStr,$out);
    exec("rm /tmp/wsdl-*");
    echo "updated";
}

