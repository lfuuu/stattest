#!/bin/bash
# По логу mtt найти количество ошибок и отдать их в мониторинг

logFile="/var/log/nispd/mtt/`date "+%Y.%m.%d"`.log"

# Посчитать количество ошибок
itemVal=`cat $logFile | grep Error | wc -l`
./_echoJson.sh 'Mtt' $itemVal 1 10 100 # itemId itemVal warning critical error
