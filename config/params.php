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

    'mail_map_names' => require(__DIR__ . '/mail_params_names.php'),
];
