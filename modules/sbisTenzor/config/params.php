<?php

return [
    'params' => [
        \app\models\Organization::MCN_TELECOM => [
            'authUrl' => 'https://online.sbis.ru/auth/service/',
            'serviceUrl' => 'https://online.sbis.ru/service/?srv=1',
            'login' => '',
            'password' => '',
            'signCommand' => '', //'/opt/cprocsp/bin/amd64/cryptcp -sign -thumbprint {thumbprint} -cadesbes -detached {file} {signatureFile}'
        ],
    ],
];
