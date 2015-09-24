<?php

require_once __DIR__ . '/web.php';

$config = array_merge_recursive($config, [
    'controllerNamespace' => 'app\controllers\external_operators',
]);

return $config;