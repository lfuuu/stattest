# http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=25887925
echo "Скачать портированные номера"
cd ~/nnp_ported_hu
./run.sh

logFile="~/stat/runtime/numlist_decode.xml"
mv numlist_decode.xml $logFile

cd ~/stat

echo "Выключить триггеры, чтобы не тормозило"
./yii nnp/import/disable-trigger

echo "Импорт портированных номеров"
./yii nnp/ported-hungary/import --fileName=numlist_decode.xml
rm $logFile

echo "Привязать к ID оператора по его имени"
./yii nnp/import/link

echo "Включить триггеры"
./yii nnp/import/enable-trigger
