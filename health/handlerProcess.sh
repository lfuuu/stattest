#!/bin/bash
# Проверить, что handler запущен

if [ `ps aux | grep handler | grep -v grep | wc -l` -lt 3 ]
then
    itemVal=1
else
    itemVal=0
fi

./_echoJson.sh 'HandlerProcess' $itemVal 1 1 1 # itemId itemVal warning critical error
