#!/bin/bash
# По логу mtt найти количество ошибок и отдать их в мониторинг
logFile="/var/log/nispd/nnp_ported_numberLog.log"
itemVal=`grep -i -E '(fail|except|Error)' $logFile | wc -l`
./_echoJson.sh 'NumberPortedLog' $itemVal 1 1 1 # itemId itemVal warning critical error
