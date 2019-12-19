#!/bin/bash
# Проверить, что mtt запущен

if [ `ps aux | grep 'run-tele2-daemon' | grep -v grep | wc -l` -lt 4 ]
then
    itemVal=1
else
    itemVal=0
fi

./_echoJson.sh 'Tele2Process' $itemVal 1 1 1 # itemId itemVal warning critical error
