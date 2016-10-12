<?php

use app\helpers\DateTimeZoneHelper;
use app\forms\client\ClientAccountOptionsForm;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\models\CurrencyRate;

define('NO_WEB',1);
define('NUM',20);
define('PATH_TO_ROOT','./');
include PATH_TO_ROOT . 'conf_yii.php';

for ($i=1,$work_days=0,$time = time(); $i<=30; $i++) {
    $time = $time - 86400;

    $dayOfWeek = date('w', $time);
    if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
        $work_days++;
    }
}

$counters = $pg_db->AllRecords($query ="
    SELECT
        number_service_id AS usage_id,
        destination_id,
        CASE WHEN destination_id = 2 THEN ROUND(-SUM(cost)) ELSE null END AS amount_mn,
        CASE WHEN destination_id != 2 THEN ROUND(-SUM(cost)) ELSE null END AS amount
    FROM calls_raw.calls_raw c
    WHERE
        connect_time >= '" . date('Y-m-d', strtotime('-1 month')) . "'
        AND connect_time <  '" . date('Y-m-d') . "'
        AND number_service_id IS NOT NULL
    GROUP BY number_service_id, destination_id
");

$usages = $db->AllRecords('
    SELECT
        u.id AS usage_id,
        c.id AS client_id, c.client, c.currency, c.voip_is_day_calc, c.voip_credit_limit_day, c.voip_is_mn_day_calc, c.voip_limit_mn_day
    FROM
        usage_voip u
        LEFT JOIN clients c ON c.client = u.client
    WHERE
        u.actual_from < CAST(NOW() AS DATE)
        AND u.actual_to > CAST(NOW() AS DATE)
        AND (voip_is_day_calc > 0 OR voip_is_mn_day_calc > 0)
');

$usagesCounters = [];
$clients = [];

foreach ($counters as $record) {
    if (!isset($usagesCounters[$record['usage_id']])) {
        $usagesCounters[$record['usage_id']] = [
            'amount' => 0,
            'amount_mn' => 0,
        ];
    }

    switch ($record['destination_id']) {
        case 2:
            $usagesCounters[$record['usage_id']]['amount_mn'] += $record['amount_mn'];
            break;
        default:
            $usagesCounters[$record['usage_id']]['amount'] += $record['amount'];
            break;
    }
}

foreach ($usages as $usage) {
    $clientId = $usage['client_id'];
    $usageId = $usage['usage_id'];

    if (!isset($clients[$clientId])) {
        $clients[$clientId] = [
            'id'                    => $clientId,
            'client'                => $usage['client'],
            'currency'              => $usage['currency'],
            'voip_is_day_calc'      => $usage['voip_is_day_calc'],
            'voip_credit_limit_day' => $usage['voip_credit_limit_day'],
            'voip_is_mn_day_calc'   => $usage['voip_is_mn_day_calc'],
            'voip_limit_mn_day'     => $usage['voip_limit_mn_day'],
            'sum'                   => 0,
            'sum_mn'                => 0,
        ];
    }

    if (!isset($usagesCounters[$usageId])) {
        continue;
    }

    $clients[$clientId]['sum'] = $usagesCounters[$usageId]['amount'];
    $clients[$clientId]['sum_mn'] = $usagesCounters[$usageId]['amount_mn'];
}

$currencyRateStore = [];

foreach ($clients as $clientId => $data) {
    $clients[$clientId]['new_day_limit'] = (int)($data['sum'] / $work_days * 3);
    $clients[$clientId]['new_day_limit_mn'] = (int)($data['sum_mn'] / $work_days * 3);

    // Сохранение настройки "Пересчет дневного лимита" - когда произошел пересчет
    $option =
        (new ClientAccountOptionsForm)
            ->setClientAccountId($clientId)
            ->setOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_WHEN)
            ->setValue(date(DateTimeZoneHelper::DATETIME_FORMAT));

    if (!$option->save($deleteExisting = true)) {
        Yii::error('Option "' . ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_WHEN . '" not saved for client #' . $clientId . ': ' . implode(',', (array)$option->getFirstErrors()));
    }

    // Сохранение настройки "Пересчет дневного (МН) лимита" - когда произошел пересчет
    $option =
        (new ClientAccountOptionsForm)
            ->setClientAccountId($clientId)
            ->setOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_MN_WHEN)
            ->setValue(date(DateTimeZoneHelper::DATETIME_FORMAT));

    if (!$option->save($deleteExisting = true)) {
        Yii::error('Option "' . ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_MN_WHEN . '" not saved for client #' . $clientId . ': ' . implode(',', (array)$option->getFirstErrors()));
    }

    // Сохранение настройки "Пересчет дневного лимита" - значение после пересчета
    $option =
        (new ClientAccountOptionsForm)
            ->setClientAccountId($clientId)
            ->setOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_VALUE)
            ->setValue((string)$clients[$clientId]['new_day_limit']);

    if (!$option->save($deleteExisting = true)) {
        Yii::error('Option "' . ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_VALUE . '" not saved for client #' . $clientId . ': ' . implode(',', (array)$option->getFirstErrors()));
    }

    // Сохранение настройки "Пересчет дневного лимита" - значение после пересчета
    $option =
        (new ClientAccountOptionsForm)
            ->setClientAccountId($clientId)
            ->setOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_MN_VALUE)
            ->setValue((string)$clients[$clientId]['new_day_limit_mn']);

    if (!$option->save($deleteExisting = true)) {
        Yii::error('Option "' . ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_MN_VALUE . '" not saved for client #' . $clientId . ': ' . implode(',', (array)$option->getFirstErrors()));
    }

    if (!isset($currencyRateStore[$data['currency']])) {
        $currencyRateStore[$data['currency']] = CurrencyRate::find()->currency($data['currency']);
    }
    $currencyRate = $currencyRateStore[$data['currency']];

    // Курс валюты найден - пересчить, иначе взять по-умолчанию
    $defaultVoipCreditLimitDay =
        !is_null($currencyRate)
            ? ClientAccount::DEFAULT_VOIP_CREDIT_LIMIT_DAY / $currencyRate->rate
            : ClientAccount::DEFAULT_VOIP_CREDIT_LIMIT_DAY;

    $clients[$clientId]['new_day_limit'] =
        $clients[$clientId]['new_day_limit'] > $defaultVoipCreditLimitDay
            ? $clients[$clientId]['new_day_limit']
            : $defaultVoipCreditLimitDay;

    // Курс валюты найден - пересчить, иначе взять по-умолчанию
    $defaultVoipMNLimitDay =
        !is_null($currencyRate)
            ? ClientAccount::DEFAULT_VOIP_MN_LIMIT_DAY / $currencyRate->rate
            : ClientAccount::DEFAULT_VOIP_MN_LIMIT_DAY;

    $clients[$clientId]['new_day_limit_mn'] =
        $clients[$clientId]['new_day_limit_mn'] > $defaultVoipMNLimitDay
            ? $clients[$clientId]['new_day_limit_mn']
            : $defaultVoipMNLimitDay;
}

$updated = 0;
foreach ($clients as $client) {
    /** @var ClientAccount $clientAccount */
    $clientAccount = ClientAccount::findOne($client['id']);
    if (!$clientAccount) {
        Yii::error('Cant find client#' . $client['id']);
        continue;
    }

    $updateData = [];

    if ($client['new_day_limit'] > $clientAccount->voip_credit_limit_day) {
        echo 'Other - ' . $client['id'] . ': ' . $clientAccount->voip_credit_limit_day . ' - ' . $client['new_day_limit'] . PHP_EOL;
        $updateData['voip_credit_limit_day'] = $client['new_day_limit'];
    }

    if ($client['new_day_limit_mn'] > $clientAccount->voip_limit_mn_day) {
        echo 'MN - ' . $client['id'] . ': ' . $clientAccount->voip_limit_mn_day . ' - ' . $client['new_day_limit_mn'] . PHP_EOL;
        $updateData['voip_limit_mn_day'] = $client['new_day_limit_mn'];
    }

    if ($updateData) {
        if (!ClientAccount::updateAll($updateData, ['id' => $clientAccount->id])) {
            Yii::error('Cant save client#' . $clientAccount->id);
        } else {
            $updated++;
        }
    }
}

echo date('Y-m-d H:i:s', time()) . ' updated: ' . $updated . PHP_EOL;

