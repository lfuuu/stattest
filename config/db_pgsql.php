<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=85.94.32.252;port=5432;dbname=nispd', // eridanus.mcn.ru 85.94.32.252
    // 'dsn' => 'pgsql:host=85.94.32.228;port=5432;dbname=nispd', // iberus.mcn.ru 85.94.32.228
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
];
