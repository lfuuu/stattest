#!/bin/bash
# По логу mtt найти количество ошибок и отдать их в мониторинг
logFile="/var/log/nispd/mtt/`date "+%Y.%m.%d"`.log"
itemVal=`grep Error $logFile | wc -l`
./_echoJson.sh 'MttLog' $itemVal 1 10 100 # itemId itemVal warning critical error
