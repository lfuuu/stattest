<?php

namespace app\models;

use app\dao\DidGroupDao;
use app\modules\nnp\models\NdcType;
use yii\db\ActiveRecord;
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
 * @property float $price1
 * @property float $price2
 * @property float $price3
 * @property float $price4
 * @property float $price5
 * @property float $price6
 * @property float $price7
 * @property float $price8
 * @property float $price9
 *
 * @property float $tariff_status_main1
 * @property float $tariff_status_main2
 * @property float $tariff_status_main3
 * @property float $tariff_status_main4
 * @property float $tariff_status_main5
 * @property float $tariff_status_main6
 * @property float $tariff_status_main7
 * @property float $tariff_status_main8
 * @property float $tariff_status_main9
 *
 * @property float $tariff_status_package1
 * @property float $tariff_status_package2
 * @property float $tariff_status_package3
 * @property float $tariff_status_package4
 * @property float $tariff_status_package5
 * @property float $tariff_status_package6
 * @property float $tariff_status_package7
 * @property float $tariff_status_package8
 * @property float $tariff_status_package9
 *
 * @property float tariff_status_beauty
 *
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
     * @param int $cityId
     * @param int $ndcTypeId
     * @return \string[]
     * @internal param int $countryId
     */
    public static function getList(
        $isWithEmpty = false,
        $countryId = null,
        $cityId = null,
        $ndcTypeId = NdcType::ID_GEOGRAPHIC
    ) {

        $where = [];

        /** @var City $city */
        if ($cityId && ($city = City::findOne(['id' => $cityId]))) {
            $where = DidGroup::dao()->getQueryWhereByCity($city, $ndcTypeId);
        }

        $ndcTypeId && $where = ['AND', ['ndc_type_id' => $ndcTypeId], $where];
        $countryId && $where = ['AND', ['country_code' => $countryId], $where];


        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull = false,
            $indexBy = 'id',
            $select = 'name',
            $orderBy = ['beauty_level' => SORT_ASC],
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