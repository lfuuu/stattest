<?php

define("print_sql", 1);
define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf_yii.php";


$numbers = [
    "78005050214",
    "78005050248",
    "78005050284",
    "78005050298",
    "78005050375",
    "78005050485",
    "78005050513",
    "78005050514",
    "78005050523",
    "78005050527",
    "78005050528",
    "78005050529",
    "78005050531",
    "78005050536",
    "78005050537",
    "78005050538",
    "78005050539",
    "78005050542",
    "78005050546",
    "78005050547",
    "78005050613",
    "78005051950",
    "78005051957",
    "78005051968",
    "78005052064",
    "78005052198",
    "78005052307",
    "78005052450",
    "78005052451",
    "78005052483",
    "78005052536",
    "78005052671",
    "78005052701",
    "78005052734",
    "78005052745",
    "78005052746",
    "78005052761",
    "78005052793",
    "78005052794",
    "78005052840",
    "78005052845",
    "78005052905",
    "78005052935",
    "78005052965",
    "78005053017",
    "78005053054",
    "78005053082",
    "78005053084",
    "78005053085",
    "78005053102",
    "78005053149",
    "78005053185",
    "78005053215",
    "78005053428",
    "78005053492",
    "78005053501",
    "78005053691",
    "78005053718",
    "78005053725",
    "78005053789",
    "78005053945",
    "78005053960",
    "78005054017",
    "78005054075",
    "78005054179",
    "78005054267",
    "78005054269",
    "78005054356",
    "78005054357",
    "78005054367",
    "78005054501",
    "78005054520",
    "78005054560",
    "78005054705",
    "78005054857",
    "78005054872",
    "78005054902",
    "78005055027",
    "78005055043",
    "78005055061",
    "78005055071",
    "78005055091",
    "78005055094",
    "78005055098",
    "78005055196",
    "78005055316",
    "78005055319",
    "78005056075",
    "78005056182",
    "78005056245",
    "78005056250",
    "78005056324",
    "78005056327",
    "78005056370",
    "78005056847",
    "78005056879",
    "78005056935",
    "78005056970",
    "78005057012",
    "78005057124"
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
    $u->expire_dt =     \app\helpers\DateTimeZoneHelper::getExpireDateTime($actualTo, $client->timezone_name);
    $u->E164 = $number;
    $u->no_of_lines = 10;
    $u->edit_user_id = 54; //Yana
    $u->created = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
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
