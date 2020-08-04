<?php

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include("../en-US/biller.php"), [
    'Replenishment of the account {account} for the amount of {sum} {currency}' => 'Na zákaznickom účte {account} bol dobytí kredit na sumu {sum} {currency}.',
    'incomming_payment' => 'Zálohová platba za telekomunikačné služby',

    'nal' => 'hotovosť',
    'beznal' => 'prevod',
    'correct_sum' => 'Spotreba za predchádzajúce obdobie',
    'Current statement' => 'AKTUÁLNY VÝPIS',
]);