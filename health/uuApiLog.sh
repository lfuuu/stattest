#!/bin/bash
# По логу UuApi найти количество ошибок и отдать их в мониторинг
logFile="../runtime/logs/uu_api.log"
itemVal=`grep Error $logFile | wc -l`
./_echoJson.sh 'UuApiLog' $itemVal 1 10 100 # itemId itemVal warning critical error
