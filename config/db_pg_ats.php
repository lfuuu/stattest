<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=eridanus.mcn.ru;port=5432;dbname=voipdb',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
];
