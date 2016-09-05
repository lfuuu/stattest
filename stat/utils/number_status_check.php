<?php

use app\models\Number;
use app\models\UsageVoip;

define("NO_WEB", 1);
define("PATH_TO_ROOT", "../");
include PATH_TO_ROOT . "conf_yii.php";
include INCLUDE_PATH . "runChecker.php";


$usages = UsageVoip::find()->actual()->andWhere(['not', ['type_id' => 'line']]);
/** @var UsageVoip $u */
foreach ($usages->each() as $usage) {

    if (!$usage->tariff || !$usage->voipNumber) {
        continue;
    }

    if ($usage->tariff->isTest() && $usage->voipNumber->status != Number::STATUS_ACTIVE_TESTED) {
        echo "\n" . $usage->E164 . ' is not test';
        Number::dao()->actualizeStatusByE164($usage->E164);
    }

    if (!$usage->tariff->isTest() && $usage->voipNumber->status != Number::STATUS_ACTIVE_COMMERCIAL) {
        echo "\n" . $usage->E164 . ' is not worked';
        Number::dao()->actualizeStatusByE164($usage->E164);
    }
}

$numbers = Number::find()->where(['status' => Number::$statusGroup[Number::STATUS_GROUP_ACTIVE]]);

foreach ($numbers->each() as $number) {
    if (!UsageVoip::find()->actual()->andWhere(['E164' => $number])->count()) {
        echo PHP_EOL . $number->number . " - not active";
        Number::dao()->actualizeStatusByE164($number->number);

    }
}