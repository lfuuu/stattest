<?php

use yii\web\DbSession;

return [
//    'class' => DbSession::class,
    'class' => 'yii\redis\Session',
    'keyPrefix' => 'sess:',
    'cookieParams' => ['lifetime' => 40 * 60 * 60]
];
