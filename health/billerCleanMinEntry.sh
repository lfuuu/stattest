#!/bin/bash

itemVal=`../yii ubiller/clear-min-entry | grep '^AccountEntry' --count`
./_echoJson.sh 'billerMin' $itemVal 1 2 2 # itemId itemVal warning critical error
