<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\models\Country;
use yii\db\ActiveQuery;

/**
 * Страны тарифа (витрины)
 *
 * @property integer $id
 * @property integer $tariff_id
 * @property integer $country_id
 *
 * @property-read Tariff $tariff
 * @property-read Country $country
 *
 * @method static TariffCountry findOne($condition)
 * @method static TariffCountry[] findAll($condition)
 */
class TariffCountry extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::class,
            ]
        );
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_country';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'country_id',], 'integer'],
            [['tariff_id', 'country_id',], 'required'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_id']);
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->country->name_rus;
    }

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'country_id':
                if ($country = Country::findOne(['code' => $value])) {
                    return $country->getLink();
                }
                break;
        }

        return $value;
    }

    /**
     * Какие поля не показывать в исторических данных
     *
     * @param string $action
     * @return string[]
     */
    public static function getHistoryHiddenFields($action)
    {
        return [
            'id',
            'tariff_id',
        ];
    }

    /**
     * Вернуть ID родителя
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->tariff_id;
    }

    /**
     * Установить ID родителя
     *
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->tariff_id = $parentId;
    }
}
