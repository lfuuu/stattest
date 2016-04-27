<?php

namespace app\classes\uu\model;

use Yii;

/**
 * Стоимость ресурса
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
class TariffResource extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_tariff_resource';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount', 'price_per_unit'], 'number'],
            [['resource_id', 'tariff_id'], 'integer'],
            [['resource_id', 'amount', 'price_per_unit', 'price_min'], 'required'],
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

}
