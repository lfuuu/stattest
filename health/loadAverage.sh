#!/bin/bash
# Мониторинг Load average

loadAverageFloat=`awk '{print $2}' /proc/loadavg`
LC_ALL=C loadAverageInt=$(printf '%.*f\n' 0 $loadAverageFloat) # bash не умеет оперировать вещественными числами. Дает либо ошибку, либо сравнивает посимвольно. Поэтому надо int. LC_ALL для разделителя точки, а не запятой

./_echoJson.sh "LoadAverage"  $loadAverageInt 4 5 8 # itemId itemVal warning critical error
