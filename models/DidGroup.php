<?php
namespace app\models;

use app\dao\DidGroupDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $beauty_level
 *
 * @property City $city
 * @property
 */
class DidGroup extends ActiveRecord
{
    const BEAUTY_LEVEL_STANDART = 0;
    const BEAUTY_LEVEL_PLATINUM = 1;
    const BEAUTY_LEVEL_GOLD = 2;
    const BEAUTY_LEVEL_SILVER = 3;
    const BEAUTY_LEVEL_BRONZE = 4;

    public static $beautyLevelNames = [
        self::BEAUTY_LEVEL_STANDART => 'Стандартный',
        self::BEAUTY_LEVEL_PLATINUM => 'Платиновый',
        self::BEAUTY_LEVEL_GOLD     => 'Золотой',
        self::BEAUTY_LEVEL_SILVER   => 'Серебрянный',
        self::BEAUTY_LEVEL_BRONZE   => 'Бронзовый',
    ];

    public static function tableName()
    {
        return 'did_group';
    }

    public static function dao()
    {
        return DidGroupDao::me();
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }
}
