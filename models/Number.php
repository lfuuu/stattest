<?php
namespace app\models;

use app\dao\NumberDao;
use yii\db\ActiveRecord;

/**
 * @property string number
 * @property string status
 * @property string reserve_from
 * @property string reserve_till
 * @property string hold_from
 * @property string hold_to
 * @property int beauty_level
 * @property int price
 * @property int region
 * @property int client_id
 * @property int usage_id
 * @property string reserved_free_date
 * @property string used_until_date
 * @property int edit_user_id
 * @property string site_publish
 * @property int city_id
 * @property int did_group_id
 *
 * @property City $city
 * @property DidGroup $didGroup
 * @property UsageVoip $usage
 */
class Number extends ActiveRecord
{

    const STATUS_INSTOCK = 'instock';
    const STATUS_HOLD = 'hold';
    const STATUS_ACTIVE = 'active';
    const STATUS_RESERVED = 'reserved';
    const STATUS_NOTSELL = 'notsell';

    public static $statusList = [
        self::STATUS_NOTSELL => 'Не продается',
        self::STATUS_INSTOCK => 'Свободен',
        self::STATUS_RESERVED => 'В резерве',
        self::STATUS_ACTIVE => 'Используется',
        self::STATUS_HOLD => 'В отстойнике',
    ];

    public static function tableName()
    {
        return 'voip_numbers';
    }

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'number' => 'Номер',
            'client_id' => 'Клиент',
            'usage_id' => 'Услуга',
            'city_id' => 'Город',
            'did_group_id' => 'DID группа',
            'beauty_level' => 'Степень красивости',
        ];
    }

    public static function dao()
    {
        return NumberDao::me();
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    public function getDidGroup()
    {
        return $this->hasOne(DidGroup::className(), ['id' => 'did_group_id']);
    }

}