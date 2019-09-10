#!/bin/bash
# По логу nnpPortedNumberLog найти количество ошибок и отдать их в мониторинг
logFile="/var/log/nispd/nnp_ported_numberLog.log"
itemVal=`grep Error $logFile | wc -l`

./_echoJson.sh 'nnpPortedNumberLog' $itemVal 1 1 1 # itemId itemVal warning critical error
