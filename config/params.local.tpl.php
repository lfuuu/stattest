<?php

return [
    'SITE_URL' => '',
    'API_SECURE_KEY' => '',
    'CORE_SERVER' => 'vpbxphoneapi.mcn.loc',
    'PHONE_SERVER' => 'vpbxphoneapi.mcn.loc',
    'VPBX_API_AUTHORIZATION' => [
        'method' => 'bearer',
        'token' => '',
    ],
    'LK_PATH' => 'https://vpbxphoneapi.mcn.loc/lk/',
    'BASE_SERVER' => '',
    'FEEDBACK_SERVER' => false,
    'FEEDBACK_API_KEY' => '',

    'NOTIFICATION_TOKEN' => '4Zf|4Jdney10@t55?Xj80tiDIfTe&{',

    // доступ к шлюзу отправки sms-сообщений на thiamis.mcn.ru
    'sms_client' => null,
    'sms_password' => null,
    'sms_server' => 'http://thiamis.mcn.ru/sms/gateway.php',

    // емайл, на который дублируются все сообщения системы
    'monitoring_email' => null,

    'SberbankApi' => [
        \app\models\Organization::MCN_TELECOM_RETAIL => [
            'user' => '',
            'password' => ''
        ]
    ],

    'yandex' => [
        'kassa' => [
            \app\models\Organization::MCN_TELECOM_RETAIL => [
                'shop_id' => 101321,
                'sc_id' => 34952,
                'password' => '',
            ],
        ]
    ],

    'PayPal' => [
        'default' => [ // TEL2TEL_KFT || TEL2TEL_LTD
            'user' => '',
            'password' => '',
            'signature' => '',
        ],
        \app\models\Organization::TEL2TEL_GMBH => [
            'user' => '',
            'password' => '',
            'signature' => '',
        ]
    ],

    'nnpInfoServiceURL' => '', // https://api-gw.mcn.ru/voipbilld/reg/test/nnpcalc
    'nnpMCNOperatorId' => 6720,
    'billerApiURL' => '', // http://reg99.mcntelecom.ru:8101/api/

    'API_SERVER' => '', // 'https://api.mcn.ru/v2/rest',
    'ROBOCALL_AUTH' => '', // ['method' => 'bearer', 'token' => 'xxx'],
    'ROBOCALL_DEFAULT_PARAMS' => [], //['task_id' =>  100, 'account_id' => 10000, 'robocall_id' => 10],

    'CHAT_BOT_SERVER' => '',

];
