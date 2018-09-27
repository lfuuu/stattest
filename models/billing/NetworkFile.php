<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\NetworkFileDao;
use Yii;

/**
 * @property int $id
 * @property int $network_config_id
 * @property string $date
 * @property string $format
 * @property string $filename
 * @property bool $active
 * @property int $rows
 * @property string $startdate
 * @property string $created_at
 * @property string $date_to
 * @property string $store_filename
 * @property bool $parsed
 *
 * @property-read NetworkConfig $config
 */
class NetworkFile extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip.network_file';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * @return NetworkFileDao
     */
    public static function dao()
    {
        return NetworkFileDao::me();
    }

    /**
     * @return string
     */
    public function getStorageDir()
    {
        return Yii::$app->params['STORE_PATH'] . 'voip_pricelist_uploads';
    }

    /**
     * @return string
     */
    public function getStorageFilePath()
    {
        return $this->getStorageDir() . '/' . $this->store_filename;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfig()
    {
        return $this->hasOne(NetworkConfig::class, ['id' => 'network_config_id']);
    }
}