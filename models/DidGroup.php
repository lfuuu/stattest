<?php
namespace app\models;

use app\dao\DidGroupDao;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $beauty_level
 * @property int city_id
 *
 * @property City $city
 */
class DidGroup extends ActiveRecord
{
    const MOSCOW_STANDART_GROUP_ID = 2;

    const BEAUTY_LEVEL_STANDART = 0;
    const BEAUTY_LEVEL_PLATINUM = 1;
    const BEAUTY_LEVEL_GOLD = 2;
    const BEAUTY_LEVEL_SILVER = 3;
    const BEAUTY_LEVEL_BRONZE = 4;

    public static $beautyLevelNames = [
        self::BEAUTY_LEVEL_STANDART => 'Стандартный',
        self::BEAUTY_LEVEL_PLATINUM => 'Платиновый',
        self::BEAUTY_LEVEL_GOLD => 'Золотой',
        self::BEAUTY_LEVEL_SILVER => 'Серебрянный',
        self::BEAUTY_LEVEL_BRONZE => 'Бронзовый',
    ];

    /**
     * Вернуть имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'city_id' => 'Город',
            'name' => 'Название',
            'beauty_level' => 'Красивость',
        ];
    }

    public static function tableName()
    {
        return 'did_group';
    }

    /**
     * @return DidGroupDao
     */
    public static function dao()
    {
        return DidGroupDao::me();
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * Вернуть список красивостей
     * @param bool $isWithEmpty
     * @return string[]
     */
    public static function getBeautyLevelList($isWithEmpty = false)
    {
        $list = self::$beautyLevelNames;

        if ($isWithEmpty) {
            $list = ['' => ' ---- '] + $list;
        }
        return $list;
    }
}
