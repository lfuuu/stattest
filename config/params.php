<?php

$rights = require(__DIR__ . '/rights.php');


return [
    'SITE_URL' => 'https://stat.mcn.ru/',
    'rights' => $rights,
    'adminEmail' => 'admin@example.com',
    'STORE_PATH' => realpath(\Yii::getAlias('@app') . '/../store') . '/',
    'SMARTY_COMPILE_DIR' => realpath(\Yii::getAlias('@app')) . '/runtime/design_c/',
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
    'vps' => [
        'url' => '',
        'authinfo' => '',
        'createVpsParams' => [
            'family' => '89.235.184.128/25',
            'vmi' => 'isp-latest__CentOS-7-amd64',
        ],
    ],

    'health' => [
        'externalUrls' => [
            'main' => [],
        ],
        'export' => [
            'main' => '@app/web/export/health/health.json',
        ],
    ],
    'rocket_chat_token' => '',
    'matrix_notifier_token' => '',
    'sormRegions' => [],
    'isLogAAA' => false,
    'clientChangedAmqSettings' => [
        'host' => '',
        'port' => 5672,
        'vhost' => '/',
        'user' => '',
        'pass' => '',
        'queue' => 'stat_changes'
    ],
    'Tele2AmqSettings' => [
        'host' => '',
        'port' => 5672,
        'vhost' => '/',
        'user' => '',
        'pass' => '',
        'exchangeRequest' => 'tele2request',
        'queueRequest' => 'tele2ApiRequest',
        'exchangeResponse' => 'tele2response',
        'queueResponse' => 'tele2ApiResponse'
    ],
    'CALLTRACKING_SERVER' => '',
];
