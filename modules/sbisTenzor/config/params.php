<?php

return [
    'isEnabled' => false,
    'params' => [
        \app\models\Organization::MCN_TELECOM => [
            'authUrl' => 'https://online.sbis.ru/auth/service/',
            'serviceUrl' => 'https://online.sbis.ru/service/?srv=1',
            'login' => '',
            'password' => '',
            'signCommand' => '', //'/opt/cprocsp/bin/amd64/cryptcp -sign -thumbprint {thumbprint} -cadesbes -hashAlg {algorithm} -detached {file} {signatureFile}'
            'hashCommand' => '', //'/opt/cprocsp/bin/amd64/cryptcp -hash -dir {hashDir} -hashAlg {algorithm} {file}'
        ],
    ],
];
