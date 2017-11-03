<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use app\models\City;
use yii\db\ActiveQuery;

/**
 * Телефония. Точка подключения
 *
 * @property integer $id
 * @property integer $tariff_id
 * @property integer $city_id
 *
 * @property-read Tariff $tariff
 * @property-read City $city
 *
 * @method static TariffVoipCity findOne($condition)
 * @method static TariffVoipCity[] findAll($condition)
 */
class TariffVoipCity extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return array
     */
    public function behaviors()
    {
        return parent::behaviors() + [
                'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
            ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_voip_city';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'city_id',], 'integer'],
            [['tariff_id', 'city_id',], 'required'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * Преобразовать объект в строку
     *
     * @return string
     */
    public function __toString()
    {
        return $this->city->name;
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

            case 'city_id':
                if ($city = City::findOne(['id' => $value])) {
                    return $city->getLink();
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
