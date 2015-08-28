<?php

return [
    'dsn' => 'mysql:host=127.0.0.1;dbname=nispd',
    'class' => 'app\classes\Connection',
    'username' => 'vagrant',
    'password' => 'vagrant',
    'charset' => 'utf8',
    'initQuery' => "SET @@session.time_zone = '+00:00';",
];
