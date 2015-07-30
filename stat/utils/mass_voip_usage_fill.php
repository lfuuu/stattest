<?php

define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf_yii.php";


$numbers = [
"78005058201", "78005058203", "78005058204", "78005058206", "78005058207", "78005058209",
"78005058213", "78005058214", "78005058216", "78005058217", "78005058219", "78005058231",
"78005058234", "78005058237", "78005058239", "78005058241", "78005058243", "78005058245",
"78005058246", "78005058247", "78005058249", "78005058251", "78005058253", "78005058254",
"78005058256", "78005058257", "78005058259", "78005058261", "78005058263", "78005058264",
"78005058267", "78005058269", "78005058270", "78005058271", "78005058273", "78005058274",
"78005058276", "78005058279", "78005058291", "78005058293", "78005058296", "78005058297",
"78005058301", "78005058302", "78005058304", "78005058306", "78005058307", "78005058309",
"78005058312", "78005058314", "78005058315", "78005058316", "78005058317", "78005058319",
"78005058321", "78005058324", "78005058326", "78005058327", "78005058329", "78005058340",
"78005058341", "78005058342", "78005058347", "78005058349", "78005058351", "78005058354",
"78005058356", "78005058357", "78005058359", "78005058360", "78005058361", "78005058362",
"78005058365", "78005058367", "78005058369", "78005058370", "78005058371", "78005058375",
"78005058376", "78005058379", "78005058390", "78005058391", "78005058392", "78005058394",
"78005058395", "78005058396", "78005058397", "78005058407", "78005058409", "78005058412",
"78005058413", "78005058415", "78005058416", "78005058417", "78005058419", "78005058421",
"78005058423", "78005058425", "78005058426", "78005058427" 
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
    $l->id_tarif_sng =75;
    $l->id_user = 54;
    $l->ts = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $l->date_activation = (new DateTime())->setTimestamp(strtotime("first day of this month, midnight"))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');;
    $l->save();
}
