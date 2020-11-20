<?php

use yii\web\DbSession;

return [
    'class' => DbSession::class,
    'cookieParams' => ['lifetime' => 40 * 60 * 60]
];
