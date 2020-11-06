<?php

return [
    'class' => 'app\classes\Connection',
    // 'dsn' => 'pgsql:host=eridanus.mcn.ru;port=5432;dbname=nispd',
    'dsn' => 'pgsql:host=iberus.mcn.ru;port=5432;dbname=nispd',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
];
