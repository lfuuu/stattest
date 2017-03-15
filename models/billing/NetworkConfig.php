<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $geo_city_id
 * @property int $geo_operator_id
 *
 * @property Pricelist $pricelist
 */
class NetworkConfig extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'voip.network_config';
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
     * @return \yii\db\ActiveQuery
     */
    public function getPricelist()
    {
        return $this->hasOne(Pricelist::className(), ['id' => 'pricelist_id']);
    }
}