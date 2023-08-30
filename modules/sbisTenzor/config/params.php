<?php

return [
    'isEnabled' => true,
    'params' => [
        \app\models\Organization::MCN_TELECOM => [
            'authUrl' => 'https://online.sbis.ru/auth/service/',
            'serviceUrl' => 'https://online.sbis.ru/service/?srv=1',
            'login' => '',
            'password' => '',
            'signCommand' => '/home/jenkins/sbis/sign.sh {thumbprint} {file} {signatureFile}',
            'hashCommand' => '/home/jenkins/sbis/hash.sh {algorithm} {hashDir} {file}',
            //'signCommand' => '/opt/cprocsp/bin/amd64/cryptcp -sign -thumbprint {thumbprint} -cadesbes -detached {file} {signatureFile}',
            //'hashCommand' => '/opt/cprocsp/bin/amd64/cryptcp -hash -dir {hashDir} -hashAlg {algorithm} {file}'

        ],
        \app\models\Organization::MCN_TELECOM_SERVICE => [
            'authUrl' => 'https://online.sbis.ru/auth/service/',
            'serviceUrl' => 'https://online.sbis.ru/service/?srv=1',
            'login' => '',
            'password' => '',
            //'signCommand' => '/opt/cprocsp/bin/amd64/cryptcp -sign -thumbprint {thumbprint} -cadesbes -hashAlg {algorithm} -detached {file} {signatureFile}',
            //'hashCommand' => '/opt/cprocsp/bin/amd64/cryptcp -hash -dir {hashDir} -hashAlg {algorithm} {file}',
            'signCommand' => '/home/jenkins/sbis/sign.sh {thumbprint} {file} {signatureFile}',
            'hashCommand' => '/home/jenkins/sbis/hash.sh {algorithm} {hashDir} {file}',
        ],
        \app\models\Organization::AB_SERVICE_MARCOMNET => [
            'authUrl' => 'https://online.sbis.ru/auth/service/',
            'serviceUrl' => 'https://online.sbis.ru/service/?srv=1',
            'login' => '',
            'password' => '',
            //'signCommand' => '/opt/cprocsp/bin/amd64/cryptcp -sign -thumbprint {thumbprint} -cadesbes -hashAlg {algorithm} -detached {file} {signatureFile}',
            //'hashCommand' => '/opt/cprocsp/bin/amd64/cryptcp -hash -dir {hashDir} -hashAlg {algorithm} {file}',
            'signCommand' => '/home/jenkins/sbis/sign.sh {thumbprint} {file} {signatureFile}',
            'hashCommand' => '/home/jenkins/sbis/hash.sh {algorithm} {hashDir} {file}',
        ],
    ],
];
