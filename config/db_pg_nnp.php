<?php

return [
    'class' => 'app\classes\Connection',
    'dsn' => 'pgsql:host=eridanus.mcn.ru;port=5432;dbname=nispd',
    // 'dsn' => 'pgsql:host=iberus.mcn.ru;port=5432;dbname=nispd',
    'charset' => 'utf8',
    'initQuery' => "SET SESSION TIME ZONE 'UTC';",
    'slaveConfig' => [
        'attributes' => [
            PDO::ATTR_TIMEOUT => 10,
        ],
        'enableSchemaCache' => false,
        'schemaCacheDuration' => 3600,
        'schemaCache' => 'cache',

    ],



    'slaves' => [
        ['dsn' => 'pgsql:host=iberus.mcn.ru;port=5432;dbname=nispd'],
    ],

    'enableSlaves' => false,

    'enableSchemaCache' => false,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
