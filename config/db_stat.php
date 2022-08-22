<?php

return [
    'dsn' => 'mysql:host=127.0.0.1:3306;dbname=nispd',
    'class' => 'app\classes\Connection',
    'username' => 'vagrant',
    'password' => 'vagrant',
    'charset' => 'utf8',
    'initQuery' => "SET @@session.time_zone = '+00:00';",
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
    //'attributes' => [PDO::ATTR_CASE => PDO::CASE_LOWER],
];
