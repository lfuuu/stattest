<?php

$rights = require(__DIR__ . '/rights.php');

return [
    'rights' => $rights,
    'adminEmail' => 'admin@example.com',
    'STORE_PATH' => realpath(\Yii::getAlias('@app') . '/../store') . '/',
    'SMARTY_COMPILE_DIR' => \Yii::getAlias('@app') . '/stat/design_c/',
    'SMARTY_TEMPLATE_DIR' => \Yii::getAlias('@app') . '/stat/design/',
    'API_SECURE_KEY' => '',
    'SIGNATURE_DIR' => '/images/signature/',
    'STAMP_DIR' => '/images/stamp/',
    'ORGANIZATION_LOGO_DIR' => '/images/logo/',
    'USER_PHOTO_DIR' => '/images/users/',
    'PROTOCOL_STRING' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://',
    'currencyDownloadUrl' => 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=%s',
    'encrypt' => [
        'CLIENTS' => 'ZyG,GJr:/J4![%qhA,;^w^}HbZz;+9s34Y74cOf7[El)[A.qy5_+AR6ZUh=|W)z]y=*FoFs`,^%vt|6tM>E-OX5_Rkkno^T.',
        'UDATA' => '}{)5PTkkaTx]>a{U8_HA%6%eb`qYHEl}9:aXf)@F2Tx$U=/%iOJ${9bkfZq)N:)W%_*Kkz.C760(8GjL|w3fK+#K`qdtk_m[;+Q;@[PHG`%U1^Qu'
    ],
    'mail_map_names' => require(__DIR__ . '/mail_params_names.php'),
    'vmCollocation' => [
        'url' => '',
        'authinfo' => '',
    ],

    'health' => [
        'externalUrls' => [
            'main' => [],
        ],
        'export' => [
            'main' => '@app/web/operator/_private/health.json',
        ],
    ],

    // false - нормальная обработка очереди
    // true - не выполнять тяжелые обработчики (биллинг, пересчеты и пр.) в очереди.
    //    Они будут выполнены ubiller'ом потом по cron.
    //    Это значительно ускоряет обработку очереди, но приводит к временной (до нескольких часов) неактуальности баланса, отсутствии транзакций и пр.
    //    Чтобы уменьшить этот лаг - можно вручную запустить ubiller (см. crontab)
    'eventQueueIsUnderTheHighLoad' => false,
    'sormRegions' => [],
];
