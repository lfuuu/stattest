<?php

namespace app\models;

use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\dao\DidGroupDao;
use app\modules\uu\models\TariffStatus;
use InvalidArgumentException;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * Class DidGroup
 * @property int $id
 * @property string $name
 * @property int $city_id
 * @property int $beauty_level
 * @property int $country_code
 * @property string $comment
 * @property int $ndc_type_id
 * @property int $tariff_status_beauty
 * @property int $is_service
 * @property-read City $city
 * @property-read Country $country
 * @property-read DidGroupPriceLevel[] $didGroupPriceLevel
 * @property-read DidGroupPriceLevel $priceLevel
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
    const BEAUTY_LEVEL_EXCLUSIVE = 5;
    const BEAUTY_LEVEL_TECH = 99;

    const MIN_PRICE_LEVEL_FOR_BEAUTY = 3;

    public static $beautyLevelNames = [
        self::BEAUTY_LEVEL_STANDART => 'Стандартный',
        self::BEAUTY_LEVEL_BRONZE => 'Бронзовый',
        self::BEAUTY_LEVEL_SILVER => 'Серебряный',
        self::BEAUTY_LEVEL_GOLD => 'Золотой',
        self::BEAUTY_LEVEL_PLATINUM => 'Платиновый',
        self::BEAUTY_LEVEL_EXCLUSIVE => 'Эксклюзивный',
        self::BEAUTY_LEVEL_TECH => 'Технический',
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
            'ndc_type_id' => 'Тип номера',
            'comment' => 'Комментарий для пользователя',
            'tariff_status_beauty' => 'Пакет за красивость',
            'is_service' => 'Служебная группа',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'comment'], 'string'],
            [['beauty_level', 'city_id', 'ndc_type_id', 'country_code'], 'integer'],
            [['name', 'beauty_level', 'country_code', 'ndc_type_id', 'is_service'], 'required'],
            ['is_service', 'boolean'],
            ['tariff_status_beauty', 'number'],
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
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    /**
     * @return Country
     */
    public function getCachedCountry()
    {
        static $cache = [];

        if (!isset($cache[$this->country_code])) {
            $cache[$this->country_code] = $this->country;
        }

        return $cache[$this->country_code];
    }

    public function getDidGroupPriceLevel()
    {
        return $this->hasMany(DidGroupPriceLevel::class, ['did_group_id' => 'id']);
    }

    /**
     * @param $priceLevelId
     * @return DidGroupPriceLevel
     */
    public function getPriceLevel($priceLevelId)
    {
        static $_cache = [];

        $key = $this->id . '-' . $priceLevelId;
        if (!array_key_exists($key, $_cache)) {
            $_cache[$key] = DidGroupPriceLevel::find()->where([
                'did_group_id' => $this->id,
                'price_level_id' => $priceLevelId,
            ])->one();
        }

        return $_cache[$key];
    }

    public function getTariffStatusMain($priceLevelId = ClientAccount::DEFAULT_PRICE_LEVEL)
    {
        $didGroupPriceLevel = $this->getPriceLevel($priceLevelId);

        if (!$didGroupPriceLevel) {
            return TariffStatus::ID_TEST;
        }
        return $didGroupPriceLevel->tariff_status_main_id;
    }

    public function getTariffStatusPackage($priceLevelId = ClientAccount::DEFAULT_PRICE_LEVEL)
    {
        $didGroupPriceLevel = $this->getPriceLevel($priceLevelId);

        if (!$didGroupPriceLevel) {
            return TariffStatus::ID_PUBLIC;
        }
        return $didGroupPriceLevel->tariff_status_package_id;
    }

    public function getPrice($priceLevelId = ClientAccount::DEFAULT_PRICE_LEVEL)
    {
        $didGroupPriceLevel = $this->getPriceLevel($priceLevelId);

        if (!$didGroupPriceLevel) {
            return null;
        }
        return $didGroupPriceLevel->price;
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
    )
    {

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

    /**
     * Вернуть html: имя + ссылка
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getLink()
    {
        return Html::a(Html::encode($this->name), $this->getUrl());
    }
}
