#!/bin/bash
# Мониторинг экспорта свободных номеров (см. modules/platform)
file='../web/export/free-number/number.tsv.gz'

# declare, чтобы было числовое значение, которое инвертируем. То есть проверяем, что кол-во строк БОЛЬШЕ 50К
declare -i itemVal=50000-`cat $file | gunzip | wc -l`

# проверяем дату редактирования (создания) файла
timestamp=`stat $file --format=%y`

./_echoJsonWithTimestamp.sh 'exportFreeNumbers' $itemVal 1 2 3 "$timestamp" # itemId itemVal warning critical error timestamp
