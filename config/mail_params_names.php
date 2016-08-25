<?php

return [
    '{client_id}' => [
        'method' => 'getClientAccountId',
        'descr' => 'ID лицевого счета',
    ],
    '{balance}' => [
        'method' => 'getBalance',
        'descr' => 'Баланса лицевого счета',
    ],
    '{lk_link}' => [
        'method' => 'getLnk',
        'descr' => 'Ссылка активации контакта в личном кабинете',
    ],
    '{day_limit}' => [
        'method' => 'getClientAccountDayLimit',
        'descr' => 'Размер суточного лимита лицевого счета',
    ],
    '{min_day_limit}' => [
        'method' => 'getClientAccountMinDayLimit',
        'descr' => 'Размер суточного лимита установленного пользователем',
    ],
    '{currency}' => [
        'method' => 'getClientAccountCurrency',
        'descr' => 'Валюта лицевого счета',
    ],
    '{pay_sum}' => [
        'method' => 'getNewPaymentValue',
        'descr' => 'Сумма платежа',
    ],
];
