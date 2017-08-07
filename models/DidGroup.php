<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\dao\DidGroupDao;
use InvalidArgumentException;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * @property int $id
 * @property string $name
 * @property int $beauty_level
 * @property int $country_code
 * @property int $city_id
 * @property int $ndc_type_id
 * @property int $is_service
 *
 * @property float $price1 // Клиент i. Подробнее см. \app\models\ClientAccount::getPriceLevels
 * @property float $price2
 * @property float $price3 // ОТТ i-2
 * @property int $price4
 * @property int $price5
 * @property int $price6
 * @property int $price7
 * @property int $price8
 * @property int $price9
 * @property int $price10
 * @property int $price11 // ОТТz i-10
 * @property int $price12
 * @property int $price13
 * @property int $price14
 * @property int $price15
 * @property int $price16
 * @property int $price17
 * @property int $price18
 *
 * @property int $tariff_status_main1 // Клиент i. Подробнее см. \app\models\ClientAccount::getPriceLevels
 * @property int $tariff_status_main2
 * @property int $tariff_status_main3 // ОТТ i-2
 * @property int $tariff_status_main4
 * @property int $tariff_status_main5
 * @property int $tariff_status_main6
 * @property int $tariff_status_main7
 * @property int $tariff_status_main8
 * @property int $tariff_status_main9
 * @property int $tariff_status_main10
 * @property int $tariff_status_main11 // ОТТz i-10
 * @property int $tariff_status_main12
 * @property int $tariff_status_main13
 * @property int $tariff_status_main14
 * @property int $tariff_status_main15
 * @property int $tariff_status_main16
 * @property int $tariff_status_main17
 * @property int $tariff_status_main18
 *
 * @property int $tariff_status_package1 // Клиент i. Подробнее см. \app\models\ClientAccount::getPriceLevels
 * @property int $tariff_status_package2
 * @property int $tariff_status_package3 // ОТТ i-2
 * @property int $tariff_status_package4
 * @property int $tariff_status_package5
 * @property int $tariff_status_package6
 * @property int $tariff_status_package7
 * @property int $tariff_status_package8
 * @property int $tariff_status_package9
 * @property int $tariff_status_package10
 * @property int $tariff_status_package11 // ОТТz i-10
 * @property int $tariff_status_package12
 * @property int $tariff_status_package13
 * @property int $tariff_status_package14
 * @property int $tariff_status_package15
 * @property int $tariff_status_package16
 * @property int $tariff_status_package17
 * @property int $tariff_status_package18
 *
 * @property int tariff_status_beauty
 *
 * @property string $comment
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
        $labels = [
            'id' => 'ID',
            'country_code' => 'Страна',
            'city_id' => 'Город',
            'name' => 'Название',
            'beauty_level' => 'Красивость',
            'ndc_type_id' => 'Тип номера',
            'comment' => 'Комментарий для пользователя',
            'tariff_status_beauty' => 'Пакет за красивость',
            'is_service' => 'Служебная группа',
        ];

        for ($i = 1; $i <= 9; $i++) {
            $labels['price' . $i] = 'Цена ' . $i;
            $labels['tariff_status_main' . $i] = 'Тариф ' . $i;
            $labels['tariff_status_package' . $i] = 'Пакет ' . $i;
        }

        return $labels;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [
            [['name', 'comment'], 'string'],
            [['beauty_level', 'city_id', 'ndc_type_id', 'country_code'], 'integer'],
            [['name', 'beauty_level', 'country_code', 'ndc_type_id', 'is_service'], 'required'],
            ['is_service', 'boolean'],
            ['tariff_status_beauty', 'number'],
        ];

        for ($i = 1; $i <= 9; $i++) {
            $rules[] = ['price' . $i, 'number'];

            $rules[] = ['tariff_status_main' . $i, 'number'];
            $rules[] = ['tariff_status_main' . $i, 'required'];

            $rules[] = ['tariff_status_package' . $i, 'number'];
            $rules[] = ['tariff_status_package' . $i, 'required'];
        }

        return $rules;
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
     * @param int $countryId
     * @param int $cityId Не указан - не фильтровать. Больше 0 - если есть такая красивость/служебность у города, то брать ее, иначе от страны. Меньше 0 - только для страны без города.
     * @param int $ndcTypeId
     * @return \string[]
     * @throws \InvalidArgumentException
     * @internal param int $countryId
     */
    public static function getList(
        $isWithEmpty = false,
        $countryId = null,
        $cityId = null,
        $ndcTypeId = null
    ) {

        $where = [];
        $ndcTypeId && $where['ndc_type_id'] = $ndcTypeId;
        $countryId && $where['country_code'] = $countryId;

        if ($cityId > 0) {

            // есть такая красивость/служебность у города, то брать ее, иначе от страны
            if (!$countryId) {
                // страну взять от города
                $city = City::findOne(['id' => $cityId]);
                if (!$city) {
                    throw new InvalidArgumentException('Неправильный cityId');
                }

                $where['country_code'] = $city->country_id;
            }

            $query = self::find()
                ->where($where)
                ->andWhere([
                    'OR',
                    ['city_id' => $cityId],
                    ['city_id' => null]
                ])
                ->orderBy(new Expression('city_id IS NOT NULL DESC, is_service ASC, beauty_level ASC')); // важно city_id IS NOT NULL DESC! чтобы сначала был город, а потом дефолтный по стране
            $list = [];
            $сitiesCache = []; // нужно для определения, есть ли такая красивость/служебность у города. Если есть - брать ее, иначе от страны
            /** @var DidGroup $didGroup */
            foreach ($query->each() as $didGroup) {

                $сitiesCacheKey = $didGroup->beauty_level . '_' . $didGroup->is_service; // красивость/служебность
                if ($didGroup->city_id) {
                    // запомнить красивость/служебность города, чтобы такую же у страны не брать
                    $сitiesCache[$сitiesCacheKey] = true;
                } elseif (isset($сitiesCache[$сitiesCacheKey])) {
                    // Такую красивость/служебность уже брали у города - такую же у страны не брать!
                    continue;
                }

                $list[$didGroup->id] = $didGroup->name;
            }

            return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull = false) + $list;
        }

        if ($cityId < 0) {
            // только для страны без города
            $where['city_id'] = null;
        }

        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['is_service' => SORT_ASC, 'beauty_level' => SORT_ASC],
            $where
        );
    }

    /**
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param integer $id
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($id)
    {
        return Url::to(['/tariff/did-group/edit', 'id' => $id]);
    }
}