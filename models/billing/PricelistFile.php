<?php
namespace app\models\billing;

use app\dao\billing\PricelistFileDao;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property int    $pricelist_id
 * @property string $date
 * @property string $format
 * @property string $filename
 * @property bool   $full
 * @property bool   $active
 * @property int    $rows
 * @property string $startdate
 * @property string $store_filename
 * @property bool   $parsed
 *
 * @property Pricelist $pricelist
 * @property
 */
class PricelistFile extends ActiveRecord
{
    public static function tableName()
    {
        return 'voip.raw_file';
    }

    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    public static function dao()
    {
        return PricelistFileDao::me();
    }

    public function getPricelist()
    {
        return $this->hasOne(Pricelist::className(), ['id' => 'pricelist_id']);
    }

    public function getStorageDir()
    {
        return Yii::$app->params['STORE_PATH'] . 'voip_pricelist_uploads';
    }

    public function getStorageFilePath()
    {
        return $this->getStorageDir() . '/' . $this->store_filename;
    }
}