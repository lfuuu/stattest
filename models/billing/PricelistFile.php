<?php

namespace app\models\billing;

use app\classes\model\ActiveRecord;
use app\dao\billing\PricelistFileDao;
use Yii;

/**
 * @property int $id
 * @property int $pricelist_id
 * @property string $date
 * @property string $format
 * @property string $filename
 * @property bool $full
 * @property bool $active
 * @property int $rows
 * @property string $startdate
 * @property string $store_filename
 * @property bool $parsed
 *
 * @property-read Pricelist $pricelist
 *
 * @method static PricelistFile findOne($condition)
 * @method static PricelistFile[] findAll($condition)
 */
class PricelistFile extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip.raw_file';
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
     * @return PricelistFileDao
     */
    public static function dao()
    {
        return PricelistFileDao::me();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPricelist()
    {
        return $this->hasOne(Pricelist::className(), ['id' => 'pricelist_id']);
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
}