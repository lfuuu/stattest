#!/bin/bash
# По логу портирования MNC найти количество ошибок и отдать их в мониторинг
logFile="/var/log/nispd/nnp_mnc_porting.log"
itemVal=`grep Error $logFile | wc -l`

./_echoJson.sh 'MncPortedLog' $itemVal 1 1 1 # itemId itemVal warning critical error
