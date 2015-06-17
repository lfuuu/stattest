<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=127.0.0.1;port=5432;dbname=voipdb',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
];
