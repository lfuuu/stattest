<?php
namespace app\models\billing;

use app\dao\billing\NetworkFileDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $network_config_id
 * @property string $date
 * @property string $format
 * @property string $file_name
 * @property bool   $active
 * @property int    $rows
 * @property string $startdate
 * @property string $created_at
 * @property string $date_to
 * @property string $store_filename
 * @property bool   $parsed
 *
 * @property NetworkConfig $config
 * @property
 */
class NetworkFile extends ActiveRecord
{
    public static function tableName()
    {
        return 'voip.network_file';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return NetworkFileDao::me();
    }

    public function getStorageDir()
    {
        return Yii::$app->params['STORE_PATH'] . 'voip_pricelist_uploads';
    }

    public function getStorageFilePath()
    {
        return $this->getStorageDir() . '/' . $this->store_filename;
    }

    public function getConfig()
    {
        return $this->hasOne(NetworkConfig::className(), ['id' => 'network_config_id']);
    }
}