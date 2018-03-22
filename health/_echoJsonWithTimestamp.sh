#!/bin/bash

itemId=$1 # Название. Например, "loadAverage"
itemVal=$2 # Текущее значение. Например, 1
warning=$3 # Значение, с которого начинается warning. Например, 2
critical=$4 # Значение, с которого начинается critical. Например, 4
error=$5 # Значение, с которого начинается error. Например, 6
timestamp=$6 # Время

if [ $itemVal -ge $error ]
then
    status='STATUS_ERROR'
elif [ $itemVal -ge $critical ]
then
    status='STATUS_CRITICAL'
elif [ $itemVal -ge $warning ]
then
    status='STATUS_WARNING'
else
    status='STATUS_OK'
fi

echo "{\"itemId\": \"$itemId\", \"itemVal\": $itemVal, \"statusId\": \"$status\", \"statusMessage\": \"$itemVal\", \"timestamp\": \"$timestamp\"}"
