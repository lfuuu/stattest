<?php

use app\models\Number;
use app\models\Trouble;
use app\models\ClientAccount;
use app\models\TariffVoip;
use app\forms\comment\ClientContractCommentForm;
use app\forms\usage\UsageVoipEditForm;
use app\helpers\DateTimeZoneHelper;
use app\forms\client\ClientCreateExternalForm;

if (isset($_GET) && isset($_GET["test"])) {
    define('YII_ENV', 'test');
}

define('NO_WEB',1);
define("PATH_TO_ROOT",'../../stat/');
header("Content-Type: application/json; charset=UTF-8");
include PATH_TO_ROOT."conf_yii.php";

$db->Connect();

class UserService
{
	function Get($field)
	{
		return 25;
	}
}
$user = new UserService();

$action=get_param_raw('action','');

if($action == "get_free_numbers")
{

    $region = isset($_GET["region"]) ? (int)$_GET["region"] : null;

    $numbers = Number::dao()->getFreeNumbersByRegion($region);


    $response = [];
    foreach($numbers as $r) {
	$response []= [ "number" => $r->number, "beauty" => $r->beauty_level, "price" => $r->price, "region" => $r->region];
    }
    echo json_encode($response);

}
