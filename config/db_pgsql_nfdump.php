<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=eridanus.mcn.ru;port=5432;dbname=nfdump',
    // 'dsn' => 'pgsql:host=iberus.mcn.ru;port=5432;dbname=nfdump',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
    'username' => 'readonly',
    'password' => 'readonly',
];
