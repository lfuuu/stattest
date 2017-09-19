Health monitoring
====

Мониторинг критичных процессов

Настройка
---------

В config/params.local.php добавить

    'HEALTH_JSON_URLS' => [
        'sync' => 'http://eridanus.mcn.ru/health/healthSync.json',
        'fullSync' => 'http://eridanus.mcn.ru/health/healthFullSync.json',
        'loadAverage' => 'https://stat.mcn.ru/operator/_private/healthLoadAverage.json',
        'apiFreeNumbers495' => 'https://stat.mcn.ru/operator/_private/healthApiFreeNumbers495.json',
        'apiFreeNumbers499' => 'https://stat.mcn.ru/operator/_private/healthApiFreeNumbers499.json',
        'apiFreeNumbersSilver' => 'https://stat.mcn.ru/operator/_private/healthApiFreeNumbersSilver.json',
        'apiFreeNumbersAccount' => 'https://stat.mcn.ru/operator/_private/healthApiFreeNumbersAccount.json',
    ],

Запуск
---------

* * * * * cd /home/httpd/stat.mcn.ru/stat/health; ./run.sh; cd ..; ./yii health >> /var/log/nispd/health.log