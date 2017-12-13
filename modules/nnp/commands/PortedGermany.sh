logFile="../../../runtime/germany.gz"
wget http://status.teleflash.com/download/portierung.gz --output-document=$logFile --no-check-certificate
../../../yii nnp/ported-germany/import --fileName=germany.gz
rm $logFile

../../../yii nnp/import/link