<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\voip\Destination;
use app\models\billing\Pricelist;
use app\dao\TariffVoipPackageDao;

/**
 * @property int $id
 * @property
 */
class TariffVoipPackage extends ActiveRecord
{

    public static function tableName()
    {
        return 'tarifs_voip_package';
    }

    public static function dao()
    {
        return TariffVoipPackageDao::me();
    }

    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_id']);
    }

    public function getConnectionPoint()
    {
        return $this->hasOne(Region::className(), ['id' => 'connection_point_id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    public function getDestination()
    {
        return $this->hasOne(Destination::className(), ['id' => 'destination_id']);
    }

    public function getPricelist()
    {
        return $this->hasOne(Pricelist::className(), ['id' => 'pricelist_id']);
    }

}