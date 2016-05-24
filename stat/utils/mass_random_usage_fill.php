<?php

define("print_sql", 1);
define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf_yii.php";

use app\models\Number;

$userId = 10; // ava

$clientId = 36166;

//
//Краснодар 100 номеров - 97 - 36166++
//Ростов-на-Дону 50 номеров - 87 - 36254++
//Нижний Новгород 50 номеровa - 88 - 36917
//Самара 50 номеров - 96 - 36253++ //33
//
//Тариф: Партнёр 1000 и более
//


$confs = [
    /*
    [
        "didGroupId" => 17,
        "region" => 96,
        "count_numbers" => 25,

        "id_tarif" => 665,
        "id_tarif_local_mob" => 117,
        "id_tarif_russia" => 173,
        "id_tarif_russia_mob" => 173,
        "id_tarif_intern" => 174

    ],
    */

    [
        "didGroupId" => 32,
        "region" => 93,
        "count_numbers" => 100,

        "id_tarif" => 653,
        "id_tarif_local_mob" => 249,
        "id_tarif_russia" => 252,
        "id_tarif_russia_mob" =>252,
        "id_tarif_intern" => 255,
    ],

    /*
    [
        "didGroupId" => 12,
        "region" => 97,
        "count_numbers" => 100,

        "id_tarif" => 640,
        "id_tarif_local_mob" => 85,
        "id_tarif_russia" => 63,
        "id_tarif_russia_mob" => 63,
        "id_tarif_intern" => 69
    ],
    */
    /*
    [
        "didGroupId" => 22,
        "region" => 95,
        "count_numbers" => 50,

        "id_tarif" => 645,
        "id_tarif_local_mob" => 647,
        "id_tarif_russia" => 648,
        "id_tarif_russia_mob" => 160,
        "id_tarif_intern" => 162

    ],
*/
    /*
    [
        "didGroupId" => 42,
        "region" => 88,
        "count_numbers" => 50,

        "id_tarif" => 681,
        "id_tarif_local_mob" => 207,
        "id_tarif_russia" => 208,
        "id_tarif_russia_mob" => 208,
        "id_tarif_intern" => 210,
    ],
    [
        "didGroupId" => 47,
        "region" => 87,
        "count_numbers" => 50,

        "id_tarif" => 660,
        "id_tarif_local_mob" => 185,
        "id_tarif_russia" => 186,
        "id_tarif_russia_mob" => 186,
        "id_tarif_intern" => 188
    ],

    [
        "didGroupId" => 37,
        "region" => 86,
        "count_numbers" => 25,

        "id_tarif" => 672,
        "id_tarif_local_mob" => 315,
        "id_tarif_russia" => 316,
        "id_tarif_russia_mob" => 316,
        "id_tarif_intern" => 318
    ],
        */
];

$actualFrom = date("Y-m-d");

foreach($confs as $conf)
{
    $client = app\models\ClientAccount::findOne([
        "id" => (isset($conf['client_id']) ? $conf['client_id'] : $clientId)
    ]);
    app\classes\Assert::isObject($client);

    $actualTo = \app\models\usages\UsageInterface::MAX_POSSIBLE_DATE;

    for($i = 1; $i<=$conf["count_numbers"]; $i++)
    {
        $number =
            (new \app\models\filter\FreeNumberFilter)
                ->getNumbers()
                ->setDidGroup($conf['didGroupId'])
                ->randomOne();

        $numbers = [$number->number];

        foreach($numbers as $number)
        {
            echo "\nadd number: ".$number;

            $u = new app\models\UsageVoip;
            $u->region = $conf["region"];
            $u->actual_from = $actualFrom;
            $u->actual_to = "4000-01-01";
            $u->client = $client->client;
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
