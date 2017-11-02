#!/bin/bash
# По логу UuLog найти количество ошибок и отдать их в мониторинг

logFile="../runtime/logs/uu_api.log"

# Посчитать количество ошибок
itemVal=`cat $logFile | grep Error | wc -l`
./_echoJson.sh 'UuApiLog' $itemVal 1 10 100 # itemId itemVal warning critical error
