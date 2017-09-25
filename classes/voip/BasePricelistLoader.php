<?php

namespace app\classes\voip;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\PricelistFile;
use Yii;
use yii\base\InvalidParamException;

abstract class BasePricelistLoader extends BaseLoader
{
    /**
     * @var PricelistFile;
     */
    public $file;

    public function load(PricelistFile $file)
    {
        $this->file = $file;
    }

    /**
     * @return PricelistFile
     */
    public function uploadFile($uploadedFile, $pricelistId)
    {
        $file = new PricelistFile();
        $file->pricelist_id = $pricelistId;
        $file->date = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $file->format = '';
        $file->filename = $uploadedFile['name'];
        $file->full = false;
        $file->active = false;
        $file->parsed = false;
        $file->rows = false;
        $file->startdate = (new \DateTime())->modify('+1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT);

        mkdir($file->getStorageDir());

        $file->store_filename = md5(time() . rand());

        if (!move_uploaded_file($uploadedFile['tmp_name'], $file->getStorageFilePath())) {
            throw new \Exception('Не удалось переместить файл');
        }

        $file->save();

        $this->file = $file;

        return $file;
    }

    public function savePrices(PricelistFile $file, $data)
    {
        $transaction = Yii::$app->dbPg->beginTransaction();
        try {

            $new_rows = array();
            foreach ($data as $row) {
                $new_rows[] = $row;
                if (count($new_rows) >= 10000) {
                    $this->insertPrices($file, $new_rows);
                    $new_rows = array();
                }
            }
            if (count($new_rows) >= 0) {
                $this->insertPrices($file, $new_rows);
            }

            $file->rows = count($data);
            $file->parsed = true;
            $file->save();

            Yii::$app->dbPg->createCommand("select new_destinations(" . (int)$file->id . ")")->execute();
            //Event::go('update_voip_destination', $file->id);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function insertPrices(PricelistFile $file, $new_rows)
    {
        $q = "INSERT INTO voip.raw_price (rawfile_id, ndef, deleting, price, mob) VALUES ";
        $is_first = true;
        foreach ($new_rows as $key => $row) {
            if ($is_first == false) {
                $q .= ",";
            } else {
                $is_first = false;
            }

            $mob = false ? 'TRUE' : 'NULL';

            if (!isset($row['deleting'])) {
                $row['deleting'] = 0;
            }

            if ($row['rate'] === 'DEL') {
                $row['deleting'] = 1;
                $row['rate'] = 0;
            } elseif (!preg_match('#^\d+(\.\d+)?$#', $row['rate'])) {
                throw new InvalidParamException('В строке ' . $key . ' неправильная цена ' . $row['rate']);
            }


            $deleting = isset($row['deleting']) && $row['deleting'] ? 'TRUE' : 'FALSE';

            $q .= "('" . pg_escape_string($file->id) . "','" . pg_escape_string($row['prefix']) . "'," . $deleting . ",'" . pg_escape_string($row['rate']) . "'," . $mob . ")";
        }

        if (count($new_rows) > 0) {
            Yii::$app->dbPg->createCommand($q)->execute();
        }
    }

}
