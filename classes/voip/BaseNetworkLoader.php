<?php

namespace app\classes\voip;

use app\classes\Event;
use app\models\billing\NetworkConfig;
use app\models\billing\NetworkFile;
use Yii;
use app\models\billing\PricelistFile;
use yii\base\Object;

abstract class BaseNetworkLoader extends BaseLoader
{
    public function uploadFileByGeo(NetworkConfig $networkConfig)
    {
        $transaction = Yii::$app->dbPg->beginTransaction();
        try {

            $file = new NetworkFile();
            $file->network_config_id = $networkConfig->id;
            $file->created_at = (new \DateTime())->format('Y-m-d H:i:s');
            $file->file_name = '';
            $file->active = false;
            $file->parsed = false;
            $file->rows = false;
            $file->startdate = (new \DateTime())->modify('+1 day')->format('Y-m-d H:i:s');
            $file->save();

            Yii::$app->dbPg->createCommand("
                insert into voip.network_file_data(network_file_id, prefix, network_type_id)
                    select '{$file->id}', p.prefix, '101'
                    from geo.prefix p
                    left join geo.geo g on g.id = p.geo_id
                    where g.city = '{$networkConfig->geo_city_id}' and p.operator_id = '{$networkConfig->geo_operator_id}';
            ")->execute();

            $file->rows = Yii::$app->dbPg->createCommand("select count(*) from voip.network_file_data where network_file_id='{$file->id}'")->queryScalar();
            $file->parsed = true;
            $file->save();

            $transaction->commit();

            return $file;

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @return PricelistFile
     */
    public function uploadFile($uploadedFile, $networkConfigId)
    {
        $file = new NetworkFile();
        $file->network_config_id = $networkConfigId;
        $file->created_at = (new \DateTime())->format('Y-m-d H:i:s');
        $file->file_name = '';
        $file->active = false;
        $file->parsed = false;
        $file->rows = false;
        $file->startdate = (new \DateTime())->modify('+1 day')->format('Y-m-d H:i:s');

        mkdir($file->getStorageDir());

        $file->store_filename = md5(time() . rand());

        if (!move_uploaded_file($uploadedFile['tmp_name'], $file->getStorageFilePath())) {
            throw new \Exception('Не удалось переместить файл');
        }

        $file->save();

        $this->file = $file;

        return $file;
    }

    public function savePrices(NetworkFile $file, $data)
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

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function insertPrices(PricelistFile $file, $new_rows)
    {
        $q = "insert into voip.network_file_data(network_file_id, prefix, network_type_id) values ";
        $is_first = true;
        foreach ($new_rows as $row) {
            if ($is_first == false) $q .= ","; else $is_first = false;

            $q .= "('" . pg_escape_string($file->id) . "','" . pg_escape_string($row['prefix']) . "','" . pg_escape_string($row['network_type_id']) . "')";
        }

        Yii::$app->dbPg->createCommand($q)->execute();
    }

}
