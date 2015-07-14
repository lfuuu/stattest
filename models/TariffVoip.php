<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\TariffVoipDao;
use app\models\billing\Pricelist;

/**
 * @property int $id
 * @property
 */
class TariffVoip extends ActiveRecord
{
    public static $statuses = [
        'public' => 'Публичный',
        'special' => 'Специальный',
        'archive' => 'Архивный',
        'operator' => 'Оператор',
    ];

    public static $destinations = [
        '1' => 'Россия',
        '2' => 'Международка',
        '4' => 'Местные Стационарные',
        '5' => 'Местные Мобильные',
    ];

    public static function tableName()
    {
        return 'tarifs_voip';
    }

    public static function dao()
    {
        return TariffVoipDao::me();
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

    public function getPricelist()
    {
        return $this->hasOne(Pricelist::className(), ['id' => 'pricelist_id']);
    }

}