<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property string $defcode
 * @property int $ndef
 * @property bool $mob
 * @property int $geo_id
 */
class VoipDestinations extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'public.voip_destinations';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['id' => 'geo_id']);
    }

}