<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=nispd',
    'username' => 'vagrant',
    'password' => 'vagrant',
    'charset' => 'utf8',
    'initQuery' => "SET @@session.time_zone = '+00:00';",
];
