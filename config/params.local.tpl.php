<?php

return [
    'API_SECURE_KEY' => '',
    'CORE_SERVER' => 'vpbxphoneapi.mcn.loc',
    'PHONE_SERVER' => 'vpbxphoneapi.mcn.loc',
    'VPBX_API_AUTHORIZATION' => [
        'method' => 'bearer',
        'token' => '',
    ],
    'LK_PATH' => 'https://vpbxphoneapi.mcn.loc/lk/',
    'FEEDBACK_SERVER' => false,
    'FEEDBACK_API_KEY' => '',

    'NOTIFICATION_TOKEN' => '4Zf|4Jdney10@t55?Xj80tiDIfTe&{',

    // доступ к шлюзу отправки sms-сообщений на thiamis.mcn.ru
    'sms_client' => null,
    'sms_password' => null,
    'sms_server' => 'http://thiamis.mcn.ru/sms/gateway.php',

    // емайл, на который дублируются все сообщения системы
    'monitoring_email' => null,

];
