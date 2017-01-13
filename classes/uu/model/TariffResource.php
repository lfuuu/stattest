<?php

namespace app\classes\uu\model;

use app\classes\model\HistoryActiveRecord;

/**
 * Стоимость ресурса (дисковое пространство, абоненты, линии и пр.)
 *
 * @property integer $id
 * @property float $amount
 * @property float $price_per_unit
 * @property float $price_min
 * @property integer $resource_id
 * @property integer $tariff_id
 *
 * @property \app\classes\uu\model\Resource $resource
 * @property Tariff $tariff
 */
class TariffResource extends HistoryActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::className(),
        ];

    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_resource';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['amount', 'price_per_unit'], 'number'],
            [['resource_id', 'tariff_id'], 'integer'],
            [['resource_id', 'amount', 'price_per_unit', 'price_min'], 'required'],
            ['resource_id', 'validateServiceType'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(Resource::className(), ['id' => 'resource_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return mixed
     */
    public function getTariffName()
    {
        return $this->tariff->name;
    }

    /**
     * Валидировать тип услуги
     *
     * @param string $attribute
     * @param [] $params
     */
    public function validateServiceType($attribute, $params)
    {
        if ($this->tariff->service_type_id != $this->resource->service_type_id) {
            $this->addError($attribute, 'Этот ресурс от другого типа услуги.');
        }
    }
}
