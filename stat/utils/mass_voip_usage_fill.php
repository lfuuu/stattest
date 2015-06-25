<?php

define("print_sql", 1);
define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf_yii.php";


$numbers = [
    "78005059102",
    "78005059103",
    "78005059104",
    "78005059106",
    "78005059107",
    "78005059120",
    "78005059126",
    "78005059127",
    "78005059128",
    "78005059134",
    "78005059135",
    "78005059136",
    "78005059137",
    "78005059138",
    "78005059140",
    "78005059142",
    "78005059143",
    "78005059145",
    "78005059146",
    "78005059152",
    "78005059153",
    "78005059154",
    "78005059156",
    "78005059157",
    "78005059158",
    "78005059160",
    "78005059162",
    "78005059163",
    "78005059164",
    "78005059165",
    "78005059167",
    "78005059168",
    "78005059172",
    "78005059173",
    "78005059175",
    "78005059176",
    "78005059180",
    "78005059182",
    "78005059183",
    "78005059184",
    "78005059186",
    "78005059187",
    "78005059731",
    "78005059732",
    "78005059736",
    "78005059738",
    "78005059816",
    "78005059817",
    "78005059821",
    "78005059823"
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
    $u->save();

    $l = new app\models\LogTarif;

    $l->service= "usage_voip";
    $l->id_service = $u->id;
    $l->id_tarif =  448;
    $l->id_tarif_local_mob= 86;
    $l->id_tarif_russia = 72;
    $l->id_tarif_russia_mob= 72;
    $l->id_tarif_intern = 78;
    $l->id_tarif_sng =75;
    $l->id_user = 54;
    $l->ts = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $l->date_activation = (new DateTime())->setTimestamp(strtotime("first day of this month, midnight"))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');;
    $l->save();
}
