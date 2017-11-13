#!/bin/bash
# Проверить, что socket отвечает по API

if wget https://stat.mcn.ru:3000 -q
then
    itemVal=0
else
    itemVal=1
fi

./_echoJson.sh 'socketApi' $itemVal 1 1 1 # itemId itemVal warning critical error
