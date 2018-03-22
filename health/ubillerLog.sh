#!/bin/bash
# По логу ubiller найти количество ошибок и отдать их в мониторинг
logFile="/var/log/nispd/ubiller.log"
itemVal=`grep Error $logFile | wc -l`
./_echoJson.sh 'Ubiller' $itemVal 1 10 100 # itemId itemVal warning critical error
