#!/bin/bash
# Проверить, что nnp-ported отвечает по API

if wget https://stat.mcn.ru:3001 -q -O /dev/null
then
    itemVal=0
else
    itemVal=1
fi

./_echoJson.sh 'nnpPortedApi' $itemVal 1 1 1 # itemId itemVal warning critical error
