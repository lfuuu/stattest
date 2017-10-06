#!/bin/bash
# По логу ubiller найти количество ошибок и отдать их в мониторинг

logFile="/var/log/ubiller/`date "+%Y.%m.%d"`.log"

# Посчитать количество ошибок
itemVal=`cat $logFile | grep Error | wc -l`
./_echoJson.sh 'Ubiller' $itemVal 1 10 100 # itemId itemVal warning critical error
