<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=85.94.32.252;port=5432;dbname=voipdb',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
];
