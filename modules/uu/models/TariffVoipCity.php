<?php

namespace app\modules\uu\models;

use app\classes\model\HistoryActiveRecord;
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
 */
class TariffVoipCity extends HistoryActiveRecord
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

}
