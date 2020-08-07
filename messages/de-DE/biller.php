<?php

use app\models\Language;
use yii\helpers\ArrayHelper;

return ArrayHelper::merge(include(realpath(__DIR__ . '/../' . Language::LANGUAGE_ENGLISH . '/biller.php')), [
    'date_range_month' => ' ab {0, date, dd} bis {1, date, dd MMMM}',
    'date_range_year' => ' ab {0, date, dd MMMM} Jahre bis {1, date, dd MMMM yyyy} Jahre',
    'date_range_full' => ' ab {0, date, dd MMMM yyyy} Jahre bis {1, date, dd MMMM yyyy} Jahre',

    'by_agreement' => ', nach Vereinbarung {contract_no} bis {contract_date,date,dd MMMM yyyy} Jahre',
    'Replenishment of the account {account} for the amount of {sum} {currency}' => 'Guthaben aufgeladen für Account {account} {sum} {currency}',

    'incomming_payment' => 'Prepayment für Telekommunikationsdienstleistungen',

    'Communications services contract #{contract_number}' => 'Telekommunikationsdienstleistungen №{contract_number}',

    'Month' => 'Monat',

    'nal' => 'bargeld',
    'beznal' => 'überweisung',
    'correct_sum' => 'Verbrauch für Vorperiode',
    'Current statement' => 'AKTUELLER AUSZUG',
    'invoice' => 'Rechnung',
]);