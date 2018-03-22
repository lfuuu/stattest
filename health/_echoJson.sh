#!/bin/bash

timestamp=`date "+%Y.%m.%d %H:%M:%S"`
./_echoJsonWithTimestamp.sh "$1" $2 $3 $4 $5 "$timestamp"
