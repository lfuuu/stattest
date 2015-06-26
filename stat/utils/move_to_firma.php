<?php

define("print_sql", 1);
define('NO_WEB',1);
define('PATH_TO_ROOT','../');
include PATH_TO_ROOT."conf_yii.php";

$clients = app\models\ClientAccount::find()->where(
    [
        'and', 
        [
            'or', 
            ["regexp", "company_full", "^[ ]*(И|и)ндивидуальный[ ]+(П|п)редприниматель"], 
            ["regexp", "company_full", "^[ ]*И\.?[ ]*П\.?"],
            ["type" => "priv"]
        ],

        [
            "firma" => 'mcn_telekom',
            "contract_type_id" => 2,
            "business_process_id" => 1,
            "business_process_status_id" => [8 ,9, 11, 19, 21]
        ],

        ['not', ["or", ['like', 'company_full', 'ООО'], ['like', 'company_full', 'ЗАО']]]
    ])->all();




$count = 1;
foreach($clients as $client)
{
    echo "\n".($count++).": client ID: ".$client->id." ".$client->company_full." (".$client->firma.")";

    continue;

    $l = new app\models\LogClient;
    $l->client_id = $client->id;
    $l->user_id = 48;
    $l->ts = (new DateTime())->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    $l->comment = "firma";
    $l->type = "fields";
    $l->apply_ts = "2015-07-01";
    $l->is_apply_set = "no";
    $l->save();

    $f = new app\models\LogClientFields;
    $f->ver_id = $l->id;
    $f->field = "firma";
    $f->value_from = $client->firma;
    $f->value_to = "mcm_telekom";
    $f->save();
}

