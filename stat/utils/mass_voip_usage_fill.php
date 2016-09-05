<?php

use app\helpers\DateTimeZoneHelper;

define("print_sql", 1);
define('NO_WEB', 1);
define('PATH_TO_ROOT', '../');
include PATH_TO_ROOT . "conf_yii.php";


$numbers = [
    '78002222374',
    '78002222376',
    '78002222378',
    '78002222379',
    '78002222381',
    '78002222384',
    '78002222386',
    '78002222387',
    '78002222389',
    '78002222394',
    '78002222396',
    '78002222397',
    '78002222398',
    '78002225012',
    '78002225014',
    '78002225016',
    '78002225017',
    '78002225019',
    '78002225023',
    '78002225024',
    '78002225026',
    '78002225027',
    '78002225028',
    '78002225029',
    '78002225031',
    '78002225032',
    '78002225033',
    '78002225034',
    '78002225035',
    '78002225036'
];

$client = app\models\ClientAccount::findOne(["id" => 34523]);
app\classes\Assert::isObject($client);

$actualFrom = date("Y-m-d");
$actualTo = "4000-01-01";

$activationDt = DateTimeZoneHelper::getActivationDateTime($actualFrom, DateTimeZoneHelper::TIMEZONE_MOSCOW);
$expireDt = DateTimeZoneHelper::getExpireDateTime($actualTo, DateTimeZoneHelper::TIMEZONE_MOSCOW);


foreach ($numbers as $number) {
    echo "\nadd number: " . $number;

    $u = new app\models\UsageVoip;
    $u->region = 99;
    $u->actual_from = $actualFrom;
    $u->actual_to = "4000-01-01";
    $u->client = $client->client;
    $u->E164 = $number;
    $u->no_of_lines = 10;
    $u->edit_user_id = 54; //Yana
    $u->created = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $u->status = "connecting";
    $u->activation_dt = $activationDt;
    $u->expire_dt = $expireDt;
    $u->save();

    $l = new app\models\LogTarif;

    $l->service = "usage_voip";
    $l->id_service = $u->id;
    $l->id_tarif = 448;
    $l->id_tarif_local_mob = 86;
    $l->id_tarif_russia = 72;
    $l->id_tarif_russia_mob = 72;
    $l->id_tarif_intern = 78;
    $l->id_user = 54;
    $l->ts = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $l->date_activation = (new DateTime())->setTimestamp(strtotime("first day of this month, midnight"))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $l->save();
}
