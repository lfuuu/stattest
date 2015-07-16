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
    const STATE_PUBLIC = 'public';
    const STATE_SPECIAL = 'special';
    const STATE_STORE = 'archive';
    const STATE_OPERATOR = 'operator';

    const DEST_RUSSIA = 1;
    const DEST_INTERNATIONAL = 2;
    const DEST_LOCAL_FIXED = 4;
    const DEST_LOCAL_MOBILE = 5;

    public static $statuses = [
        self::STATE_PUBLIC => 'Публичный',
        self::STATE_SPECIAL => 'Специальный',
        self::STATE_STORE => 'Архивный',
        self::STATE_OPERATOR => 'Оператор',
    ];

    public static $destinations = [
        self::DEST_RUSSIA => 'Россия',
        self::DEST_INTERNATIONAL => 'Международка',
        self::DEST_LOCAL_FIXED => 'Местные Стационарные',
        self::DEST_LOCAL_MOBILE => 'Местные Мобильные',
    ];

    public static function tableName()
    {
        return 'tarifs_voip';
    }

    public function beforeSave()
    {
        $this->edit_user = \Yii::$app->user->id;
        $this->edit_time = date('Y.m.d H:i:s');
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