<?php

return [
    'params' => [
        // работа с сокет-сервером. В config/params.local.php необходимо указать те же параметры, что и в node.js/config.json
        'url' => '', // Адрес сокет-сервера. Например, 'https://stat.mcn.ru:3000'
        'backend_url' => '', // Адрес сокет-сервера для бэка. Может быть, 'http://localhost:3000'
        'secretKey' => '', // Ключ для сигнатуры. Например, 'kjhhIUTj234olijasd899U*&#kjads'
    ],
];
