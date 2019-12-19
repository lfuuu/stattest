#!/bin/bash
# По логу mtt найти количество ошибок и отдать их в мониторинг
logFile="/var/log/nispd/tele2.log"
itemVal=`grep Error $logFile | wc -l`
./_echoJson.sh 'Tele2Log' $itemVal 1 10 100 # itemId itemVal warning critical error
