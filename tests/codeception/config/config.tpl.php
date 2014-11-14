<?php
/**
 * Application configuration shared by all test types
 */
return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=nispd_test',
            'username' => 'root',
            'password' => '123',
        ],
        'mailer' => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => false,
        ],
    ],
];
