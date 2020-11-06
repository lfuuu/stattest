<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => '',
    // 'dsn' => 'pgsql:host=eridanus.mcn.ru;port=5432;dbname=cdr',
    // 'dsn' => 'pgsql:host=iberus.mcn.ru;port=5432;dbname=cdr',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
];
