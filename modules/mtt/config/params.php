<?php

return [
    'params' => [
//        'HOST' => '',
//        'PORT' => 5672,
//        'USER' => '',
//        'PASS' => '',
        'VHOST' => '/',

        // очередь и точка доступа для отправки сообщений
        'INCOMING_QUEUE' => 'mttApiRequest',
        'INCOMING_EXCHANGE' => 'request',

        // очередь и точка доступа для получения ответов
        'OUTGOING_QUEUE' => 'mttApiResponse',
        'OUTGOING_EXCHANGE' => 'response',

//        'SMS_RECEIVER_QUEUE' => 'smsReceiverQueue',
//        'SMS_RECEIVER_EXCHANGE' => 'smsReceiverExchange',
//
//        'SMS_SENDER_QUEUE' => 'smsSenderQueue',
//        'SMS_SENDER_EXCHANGE' => 'smsSenderExchange'
    ],
];
