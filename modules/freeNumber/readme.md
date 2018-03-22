Скачивание свободных номеров
============================

* Периодически (раз в час) выгружаются все свободные номера в файл.
* При изменении номера (становится свободным или перестает быть свободным) происходит уведомление через RabbitMQ.

Результат
---------

Ссылки для скачивания:
* Все свободные номера:  https://stat.mcn.ru/export/free-number/number.tsv.gz;
* Страны: https://stat.mcn.ru/export/free-number/country.tsv.gz;
* Города: https://stat.mcn.ru/export/free-number/city.tsv.gz;
* Типы NDC: https://stat.mcn.ru/export/free-number/ndc-type.tsv.gz;
* Уровни красивости: https://stat.mcn.ru/export/free-number/beauty-level.tsv.gz;

Описание формата:
* Кодировка UTF-8.
* Формат TSV (тексто-табулированный), сжат GZ.
* На первой строке - заголовок.
* В 45 минут каждого часа файл уже перегенерирован.
* Удобно читать и парсить "на лету" с помощью fgetcsv прямо GZ-файл по вышеуказанной ссылке.

Мониторинг
----------

`/health/exportFreeNumbers.sh`