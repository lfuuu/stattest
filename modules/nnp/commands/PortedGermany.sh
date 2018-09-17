cd ~/stat

echo "Скачать портированные номера с Teleflash"
logFile="~/stat/runtime/germany.gz"
wget http://status.teleflash.com/download/portierung.gz --output-document=$logFile --no-check-certificate

echo "Выключить триггеры, чтобы не тормозило"
./yii nnp/import/disable-trigger

echo "Импорт портированных номеров"
./yii nnp/ported-germany/import --fileName=germany.gz
rm $logFile

echo "Привязать к ID оператора по его имени"
./yii nnp/import/link

echo "Включить триггеры"
./yii nnp/import/enable-trigger
