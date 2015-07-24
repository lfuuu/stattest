<?php

$debugLogging = false;
$graylogHost = '85.94.32.204';
$source = 'developer_stat';

if ($debugLogging) {
    return [
        'targets' => [
            [
                'class' => 'welltime\graylog\GraylogTarget',
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
                'class' => 'welltime\graylog\GraylogTarget',
                'levels' => ['error', 'warning'],
                'host' => $graylogHost,
                'source' => $source,
            ],
            [
                'class' => 'welltime\graylog\GraylogTarget',
                'levels' => ['info', 'trace'],
                'categories' => ['application', 'request'],
                'host' => $graylogHost,
                'source' => $source,
            ],
        ],
    ];
}
