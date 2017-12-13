logFile="../../../runtime/germany.gz"
wget http://status.teleflash.com/download/portierung.gz --output-document=$logFile --no-check-certificate
../../../yii nnp/germany/ported --fileName=germany.gz
rm $logFile

../../../yii nnp/import/link