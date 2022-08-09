<?php

use welltime\graylog\GraylogTarget;
use yii\log\FileTarget;

/*
return [
    'traceLevel' => 5,
    'flushInterval' => 1000,
    'targets' => [
        'file' => [
            'class' => FileTarget::class,
            'levels' => ['error', 'warning', 'info'], // 'error', 'warning', 'info', 'trace'
            'categories' => ['atol'],
        ],
    ],
];
*/

//return [];


$debugLogging = false;
$graylogHost = '127.0.0.1';
$source = 'developer_stat';

if ($debugLogging) {
    return [
        'targets' => [
            [
                'class' => GraylogTarget::class,
                'levels' => ['error', 'warning', 'info', 'trace'],
                'host' => $graylogHost,
                'source' => $source,
            ],
        ],
    ];
} else {
    return [
        'targets' => [
            [
                'class' => GraylogTarget::class,
                'levels' => ['error', 'warning'],
                'host' => $graylogHost,
                'source' => $source,
            ],
            [
                'class' => GraylogTarget::class,
                'levels' => ['info'], // 'trace'
                'categories' => ['application', 'request', 'health', 'sbis'],
                'host' => $graylogHost,
                'source' => $source,
            ],
            [
                'class' => FileTarget::class,
                'levels' => ['error'], // 'error', 'warning', 'info', 'trace'
                'categories' => ['uu_api'],
                'logFile' => '@runtime/logs/uu_api.log',
            ],
            /*
            [
                'class' => FileTarget::class,
                'levels' => ['info'], // 'trace'
                'categories' => ['application', 'request', 'health'],
                'logFile' => '@runtime/logs/info.log',
            ],
            */
        ],
    ];
}
