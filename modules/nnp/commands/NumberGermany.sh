logFile="../../../runtime/germany.gz"
wget http://status.teleflash.com/download/portierung.gz --output-document=$logFile --no-check-certificate
../../../yii nnp/number/germany
rm $logFile

../../../yii nnp/import/link