<?php
namespace app\models;

use app\dao\DidGroupDao;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property int $beauty_level
 * @property int $country_code
 * @property int $city_id
 * @property int $number_type_id
 * @property float $price1
 * @property float $price2
 * @property float $price3
 * @property float $price4
 * @property float $price5
 * @property float $price6
 * @property float $price7
 * @property float $price8
 * @property float $price9
 * @property float $comment
 *
 * @property City $city
 * @property Country $country
 */
class DidGroup extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    const ID_MOSCOW_STANDART_495 = 1;
    const ID_MOSCOW_STANDART_499 = 2;

    const BEAUTY_LEVEL_STANDART = 0;
    const BEAUTY_LEVEL_PLATINUM = 1;
    const BEAUTY_LEVEL_GOLD = 2;
    const BEAUTY_LEVEL_SILVER = 3;
    const BEAUTY_LEVEL_BRONZE = 4;

    public static $beautyLevelNames = [
        self::BEAUTY_LEVEL_STANDART => 'Стандартный',
        self::BEAUTY_LEVEL_BRONZE => 'Бронзовый',
        self::BEAUTY_LEVEL_SILVER => 'Серебряный',
        self::BEAUTY_LEVEL_GOLD => 'Золотой',
        self::BEAUTY_LEVEL_PLATINUM => 'Платиновый',
    ];

    /**
     * Вернуть имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => 'Страна',
            'city_id' => 'Город',
            'name' => 'Название',
            'beauty_level' => 'Красивость',
            'number_type_id' => 'Тип номера',
            'price1' => 'Цена 1',
            'price2' => 'Цена 2',
            'price3' => 'Цена 3',
            'price4' => 'Цена 4',
            'price5' => 'Цена 5',
            'price6' => 'Цена 6',
            'price7' => 'Цена 7',
            'price8' => 'Цена 8',
            'price9' => 'Цена 9',
            'comment' => 'Комментарий для пользователя',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name','comment'], 'string'],
            [['beauty_level', 'city_id', 'number_type_id', 'country_code'], 'integer'],
            [['name', 'beauty_level', 'country_code', 'number_type_id'], 'required'],
            [['price1', 'price2', 'price3', 'price4', 'price5', 'price6', 'price7', 'price8', 'price9'], 'number'],
        ];
    }

    /**
     * @return string
     */
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_code']);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param int $cityId
     * @param int $countryId
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $cityId = null,
        $countryId = null
    ) {

        $where = [];
        if ($cityId) {
            if ($countryId) {
                $where = [
                    'AND',
                    ['country_code' => $countryId],
                    [
                        'OR',
                        ['city_id' => $cityId],
                        ['city_id' => null]
                    ]
                ];
            } else {
                $where = ['city_id' => $cityId];
            }
        }

        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['name' => SORT_ASC],
            $where
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param integer $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/tariff/did-group/edit', 'id' => $id]);
    }
}