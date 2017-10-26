#!/bin/bash
# Мониторинг API free-numbers
itemId=$1
ndcType=$2
beautyLvl=$3
city=$4
ndc=$5
client_account_id=$6

file='../runtime/apiFreeNumbers.txt'
wget "https://stat.mcn.ru/api/open/get-free-numbers?ndcType=$ndcType&beautyLvl=$beautyLvl&limit=10&cities[0]=$city&ndc=$ndc&client_account_id=$client_account_id" --timeout=3 --output-document=$file  > /dev/null 2> /dev/null

# declare, чтобы было числовое значение, которое инвертируем. То есть в итоге находим кол-во строк, где НЕТ тарифа
# sed, чтобы разбить по строчкам, ибо он все лепит в одну строку
declare -i itemVal=10-`cat $file | sed 's#{#\n{#g' | grep "tariff_period_id" --count`

./_echoJson.sh $itemId $itemVal 1 2 3 # itemId itemVal warning critical error
rm $file -f
