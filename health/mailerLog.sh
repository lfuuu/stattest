#!/bin/bash
# По логу mailer найти количество ошибок и отдать их в мониторинг
logFile="/var/log/nispd/mailer/`date "+%Y.%m.%d"`.log"
itemVal=`grep Error $logFile | wc -l`
./_echoJson.sh 'MailerLog' $itemVal 1 10 100 # itemId itemVal warning critical error
