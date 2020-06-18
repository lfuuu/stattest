<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=85.94.32.235;port=5432;dbname=nispd', // eridanus.mcn.ru 85.94.32.235
    // 'dsn' => 'pgsql:host=85.94.32.228;port=5432;dbname=nispd', // iberus.mcn.ru 85.94.32.228
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
    'slaveConfig' => [
        'attributes' => [
            PDO::ATTR_TIMEOUT => 10,
        ],
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,
        'schemaCache' => 'cache',

    ],



    'slaves' => [
        ['dsn' => 'pgsql:host=85.94.32.228;port=5432;dbname=nispd'],
    ],

    'enableSlaves' => false,

    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
