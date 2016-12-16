<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=85.94.32.235;port=5432;dbname=nfdump',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
    'username' => 'readonly',
    'password' => 'readonly',
];
