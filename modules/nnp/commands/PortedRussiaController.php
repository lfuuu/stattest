<?php

namespace app\modules\nnp\commands;

use app\modules\nnp\models\Country;
use yii\web\NotFoundHttpException;

/**
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=25887976
 *
 * Port_All_201710010000_1299.csv
 * Number,OwnerId,MNC,Route,RegionCode,PortDate,RowCount
 * 9000000000,mMEGAFON,02,D2502,25,2016-09-07T12:00:17+03:00,6021751
 * 9000000001,mMEGAFON,02,D2502,25,2016-07-26T17:00:07+03:00,
 * 9000000007,mMEGAFON,02,D2502,25,2016-09-20T10:00:09+03:00,
 * 9000000013,mMEGAFON,02,D2502,25,2017-02-09T10:00:18+03:00,
 *
 * Port_All_Full_201710010000_1299.csv
 * Number,OwnerId,MNC,Route,RegionCode,PortDate,RowCount,NPId,DonorId,RangeHolderId,OldRoute,OldMNC,ProcessType
 * 9000000000,mMEGAFON,02,D2502,25,2016-09-07T12:00:17+03:00,6121908,1000000006441231,mTELE2,mTELE2,,,ShortTimePort
 * 9000000001,mMEGAFON,02,D2502,25,2016-07-26T17:00:07+03:00,0,1000000005946521,mTELE2,mTELE2,,,ShortTimePort
 * 9000000007,mMEGAFON,02,D2502,25,2016-09-20T10:00:09+03:00,0,1000000006603927,mTELE2,mTELE2,,,ShortTimePort
 * 9000000013,mMEGAFON,02,D2502,25,2017-02-09T10:00:18+03:00,0,1000000008386653,mBEELINE,mTELE2,D2599,99,ShortTimePort
 *
 * Port_Increment_201710010000_16932.csv
 * NPId,Number,RecipientId,DonorId,RangeHolderId,OldRoute,NewRoute,OldMNC,NewMNC,RegionCode,PortDate,RowCount
 * 1000000011129944,9193886341,mMEGAFON,mTELE2,mMTS,D6739,D6702,39,02,67,2017-09-30T23:00:09+03:00,391
 * 1000000011316868,9645188295,mMEGAFON,mBEELINE,mBEELINE,D7799,D7702,99,02,77,2017-09-30T23:00:10+03:00,
 * 1000000011320177,9176633142,mTELE2,mMTS,mMTS,D2101,D2120,01,20,21,2017-09-30T22:00:09+03:00,
 * 1000000011320272,9176633143,mTELE2,mMTS,mMTS,D2101,D2120,01,20,21,2017-09-30T22:00:06+03:00,
 */
class PortedRussiaController extends PortedController
{
    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    protected function readData()
    {
        $fileUrl = 'zip://' . \Yii::getAlias('@runtime/' . $this->fileName);
        $fp = fopen($fileUrl, 'r');
        if (!$fp) {
            throw new NotFoundHttpException('Ошибка чтения файла ' . $fileUrl);
        }

        fgetcsv($fp, 0); // пропустить первую строчку с заголовком

        $insertValues = [];
        while (($row = fgetcsv($fp, 0)) !== false) {

            if (count($row) < 7) {
                echo 'Неправильные данные: ' . print_r($row, true) . PHP_EOL;
                continue;
            }

            $number = $row[0];
            if (!$number || !is_numeric($number)) {
                throw new \LogicException('Неправильный номер: ' . print_r($row, true));
            }

            $number = Country::RUSSIA_PREFIX . $number;

            $operatorName = $row[1];
            $operatorName = substr($operatorName, 1); // удалить первую 'm'

            $insertValues[] = [$number, $operatorName];

            if (count($insertValues) >= self::CHUNK_SIZE) {
                $this->insertValues(Country::RUSSIA, $insertValues);
            }
        }

        fclose($fp);

        if ($insertValues) {
            $this->insertValues(Country::RUSSIA, $insertValues);
        }
    }
}
