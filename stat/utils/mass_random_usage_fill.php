<?php

define("print_sql", 1);
define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf_yii.php";

use app\models\Number;

$userId = 10; // ava
$userId = 48; // dga

$clientId = 36254;

$confs = [
    /*
    [
        "didGroupId" => 12,
        "region" => 97,
        "count_numbers" => 150,
        "id_tarif" => 638,
        "id_tarif_local_mob" => 636,
        "id_tarif_russia" => 637,
        "id_tarif_russia_mob" => 63,
        "id_tarif_intern" => 69
    ],
    [
        "didGroupId" => 17,
        "region" => 96,
        "count_numbers" => 150,

        "id_tarif" => 665,
        "id_tarif_local_mob" => 669,
        "id_tarif_russia" => 668,
        "id_tarif_russia_mob" => 173,
        "id_tarif_intern" => 174

    ],
    [
        "didGroupId" => 32,
        "region" => 93,
        "count_numbers" => 150,

        "id_tarif" => 651,
        "id_tarif_local_mob" => 654,
        "id_tarif_russia" => 655,
        "id_tarif_russia_mob" =>252,
        "id_tarif_intern" => 255,
    ],
*/
    [
        "didGroupId" => 47,
        "region" => 87,
        "count_numbers" => 50,

        "id_tarif" => 659,
        "id_tarif_local_mob" => 661,
        "id_tarif_russia" => 662,
        "id_tarif_russia_mob" =>186,
        "id_tarif_intern" => 188
    ]
];

foreach($confs as $conf)
{

    for($i = 1; $i<=$conf["count_numbers"]; $i++)
    {
        $number = Number::dao()->getRandomFreeNumber($conf["didGroupId"]);
        $numbers = [$number->number];

        $client = app\models\ClientAccount::findOne(["id" => $clientId]);
        app\classes\Assert::isObject($client);

        $actualFrom = date("Y-m-d");
        $actualTo = "4000-01-01";

        foreach($numbers as $number)
        {
            echo "\nadd number: ".$number;

            $u = new app\models\UsageVoip;
            $u->region = $conf["region"];
            $u->actual_from = $actualFrom;
            $u->actual_to = "4000-01-01";
            $u->client = $client->client;
            $u->activation_dt = (new DateTime($actualFrom, new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $u->expire_dt =     (new DateTime($actualTo,   new DateTimeZone($client->timezone_name)))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $u->E164 = $number;
            $u->no_of_lines = 1;
            $u->edit_user_id = $userId;
            $u->created = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $u->status = "connecting";
            $u->save();

            $l = new app\models\LogTarif;

            $l->service= "usage_voip";
            $l->id_service = $u->id;
            $l->id_tarif =           $conf["id_tarif"];
            $l->id_tarif_local_mob=  $conf["id_tarif_local_mob"];
            $l->id_tarif_russia =    $conf["id_tarif_russia"];
            $l->id_tarif_russia_mob= $conf["id_tarif_russia_mob"];
            $l->id_tarif_intern =    $conf["id_tarif_intern"];

            $l->dest_group = 0;
            $l->minpayment_group = 0;
            $l->minpayment_local_mob = 0;
            $l->minpayment_russia = 0;
            $l->minpayment_intern= 0;
            $l->minpayment_sng = 0;

            $l->id_user = $userId;
            $l->ts = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
            $l->date_activation = (new DateTime())->setTimestamp(strtotime("first day of this month, midnight"))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');;
            $l->save();
        }
    }
}
