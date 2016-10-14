<?php

use app\classes\Html;

return [
    'client.id' => [
        'method' => 'getClientAccountId',
        'descr' => 'ID клиента',
    ],
    'client.dayLimit' => [
        'method' => 'getClientAccountDayLimit',
        'descr' =>
            'Размер суточного лимита лицевого счета ' .
            Html::tag('label', 'на момент генерации уведомления', ['class' => 'label label-info']),
    ],
    'client.minDayLimit' => [
        'method' => 'getClientAccountMinDayLimit',
        'descr' =>
            'Размер суточного лимита установленного пользователем ' .
            Html::tag('label', 'на момент генерации уведомления', ['class' => 'label label-info']),
    ],
    'client.balance' => [
        'method' => 'getBalance',
        'descr' =>
            'Баланс лицевого счета ' .
            Html::tag('label', 'на момент генерации уведомления', ['class' => 'label label-info']),
    ],
    'client.currency' => [
        'method' => 'getClientAccountCurrency',
        'descr' => 'Валюта лицевого счета',
    ],
];
