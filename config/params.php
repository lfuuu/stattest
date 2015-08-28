<?php

$rights = require(__DIR__ . '/rights.php');

return [
    'rights' => $rights,
    'adminEmail' => 'admin@example.com',
    'STORE_PATH' => realpath("../../store")."/",
    'SMARTY_COMPILE_DIR' => realpath("../stat/design_c")."/",
    'SMARTY_TEMPLATE_DIR' => realpath("../stat/design")."/",
    'API_SECURE_KEY' => '',
    'SIGNATURE_DIR' => '/images/signature/',
    'STAMP_DIR' => '/images/stamp/',
    'ORGANIZATION_LOGO_DIR' => '/images/logo/',
];
