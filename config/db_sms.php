<?php

return [
    'class' => 'app\classes\Connection',
    'charset' => 'utf8',
    'initQuery' => "SET @@session.time_zone = '+00:00';",
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];