<?php
namespace app\models;

use yii\db\ActiveRecord;

class VoipNumber extends ActiveRecord
{

    const NUMBER_STATUS_INSTOCK = 'В наличии';
    const NUMBER_STATUS_HOLD = 'Удержание';
    const NUMBER_STATUS_ACTIVE = 'Активен';
    const NUMBER_STATUS_RESERVED = 'Резерв';
    const NUMBER_STATUS_NOTSELL = 'Не продается';

    public static $statuses = [
        'instock' => self::NUMBER_STATUS_INSTOCK,
        'hold' => self::NUMBER_STATUS_HOLD,
        'active' => self::NUMBER_STATUS_ACTIVE,
        'reserved' => self::NUMBER_STATUS_RESERVED,
        'notsell' => self::NUMBER_STATUS_NOTSELL,
    ];

    public static function tableName()
    {
        return 'voip_numbers';
    }

    public function getDidGroup()
    {
        return $this->hasOne(TariffNumber::className(), ['did_group_id' => 'did_group_id']);
    }

}
