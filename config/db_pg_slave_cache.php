<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => '', // pgsql:host=<HOST>;port=6432;dbname=cdr
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
];
