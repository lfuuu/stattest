<?php
namespace app\models;

use yii\db\ActiveRecord;

class VoipNumber extends ActiveRecord
{

    const NUMBER_STATUS_INSTOCK = 'Свободен';
    const NUMBER_STATUS_HOLD = 'В отстойнике';
    const NUMBER_STATUS_ACTIVE = 'Используется';
    const NUMBER_STATUS_RESERVED = 'В резерве';
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

    public function getUsageVoip()
    {
        return $this->hasOne(UsageVoip::className(), ['E164' => 'number']);
    }

}
