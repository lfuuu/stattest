<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'mysql:host=localhost;dbname=nispd',
    'charset' => 'utf8',
    'initQuery' => "SET @@session.time_zone = '+00:00';",
];
