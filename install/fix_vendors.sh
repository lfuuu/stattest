#!/bin/bash

cd ../
DIR_PATH_SOURCE_CODE=$(pwd)

#DIR_PATH_SOURCE_CODE="/opt/stat_rep/stat"
#cd $DIR_PATH_SOURCE_CODE

echo "Source code dir: $DIR_PATH_SOURCE_CODE"


CURRENT_FILE="$DIR_PATH_SOURCE_CODE/vendor/smarty/smarty/libs/sysplugins/smarty_internal_templatecompilerbase.php"
echo "Fixing: $CURRENT_FILE"
sed -i "s/->\$function/->{\$function}/" $CURRENT_FILE

CURRENT_FILE="$DIR_PATH_SOURCE_CODE/vendor/kartik-v/mpdf/mpdf.php"
echo "Fixing: $CURRENT_FILE"
sed -i "s/->\$v\[/->{\$v}\[/" $CURRENT_FILE

CURRENT_FILE="$DIR_PATH_SOURCE_CODE/vendor/phpoffice/phpexcel/Classes/PHPExcel/Worksheet/AutoFilter/Column.php"
echo "Fixing: $CURRENT_FILE"
sed -i "s/->\$key/->{\$key}/" $CURRENT_FILE

CURRENT_FILE="$DIR_PATH_SOURCE_CODE/vendor/kartik-v/mpdf/classes/ttfontsuni.php"
echo "Fixing: $CURRENT_FILE"
sed -i "s/\$\$tag/\${\$tag}/" $CURRENT_FILE


echo "Done"