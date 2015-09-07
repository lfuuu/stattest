<?php

define("print_sql", 1);
define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf_yii.php";


$numbers = [
    //"78005058621",
    "78005058623",
    "78005058624",
    "78005058630",
    "78005058631",
    "78005058632",
    "78005058634",
    "78005058635",
    "78005058637",
    "78005058639",
    "78005058640",
    "78005058641",
    "78005058642",
    "78005058643",
    "78005058647",
    "78005058649",
    "78005058651",
    "78005058652",
    "78005058653",
    "78005058657",
    "78005058659",
    "78005058671",
    "78005058672",
    "78005058673",
    "78005058674",
    "78005058679",
    "78005058691",
    "78005058693",
    "78005058694",
    "78005058702",
    "78005058703",
    "78005058704",
    "78005058709",
    "78005058712",
    "78005058713",
    "78005058714",
    "78005058716",
    "78005058719",
    "78005058720",
    "78005058721",
    "78005058723",
    "78005058724",
    "78005058726",
    "78005058729",
    "78005058731",
    "78005058734",
    "78005058736",
    "78005058739",
    "78005058742",
    "78005058743"

    ];

$client = app\models\ClientAccount::findOne(["id" => 34523]);
app\classes\Assert::isObject($client);

$actualFrom = date("Y-m-d");
$actualTo = "4000-01-01";

foreach($numbers as $number)
{
    echo "\nadd number: ".$number;

    $u = new app\models\UsageVoip;
    $u->region = 99;
    $u->actual_from = $actualFrom;
    $u->actual_to = "4000-01-01";
    $u->client = $client->client;
    $u->activation_dt = (new DateTime($actualFrom, new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $u->expire_dt =     (new DateTime($actualTo,   new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $u->E164 = $number;
    $u->no_of_lines = 10;
    $u->edit_user_id = 54; //Yana
    $u->created = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $u->allowed_direction = "full";
    $u->status = "connecting";
    $u->save();

    $l = new app\models\LogTarif;

    $l->service= "usage_voip";
    $l->id_service = $u->id;
    $l->id_tarif =  448;
    $l->id_tarif_local_mob= 86;
    $l->id_tarif_russia = 72;
    $l->id_tarif_russia_mob= 72;
    $l->id_tarif_intern = 78;
    $l->id_user = 54;
    $l->ts = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $l->date_activation = (new DateTime())->setTimestamp(strtotime("first day of this month, midnight"))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');;
    $l->save();
}
