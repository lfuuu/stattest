<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=lkdb.mcn.ru;port=5432;dbname=core',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
    'username' => 'readonly',
    'password' => 'readonly',
];
