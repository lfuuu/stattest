#!/bin/bash
# Проверить, что mailer запущен

if [ `ps aux | grep mail/mail | grep -v grep | wc -l` -lt 2 ]
then
    itemVal=1
else
    itemVal=0
fi

./_echoJson.sh 'MttProcess' $itemVal 1 1 1 # itemId itemVal warning critical error
