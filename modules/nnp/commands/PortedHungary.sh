cd ~/nnp_ported_hu
./run.sh

logFile="~/stat/runtime/numlist_decode.xml"
mv numlist_decode.xml $logFile
cd ~/stat
./yii nnp/ported-hungary/import --fileName=numlist_decode.xml
rm $logFile

./yii nnp/import/link