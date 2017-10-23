#!/bin/bash
# Проверить, что socket запущен

if [ `ps aux | grep "node index" | grep -v grep | wc -l` -lt 2 ]
then
    itemVal=1
else
    itemVal=0
fi

./_echoJson.sh 'SocketProcess' $itemVal 1 1 1 # itemId itemVal warning critical error
