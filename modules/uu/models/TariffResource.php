<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;

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
 * @property-read \app\modules\uu\models\Resource $resource
 * @property-read Tariff $tariff
 *
 * @method static TariffResource findOne($condition)
 * @method static TariffResource[] findAll($condition)
 */
class TariffResource extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    protected $isAttributeTypecastBehavior = true;

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
        return 'uu_tariff_resource';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['amount', 'price_per_unit', 'price_min'], 'number'],
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
        return $this->hasOne(Resource::class, ['id' => 'resource_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
    }

    /**
     * @return string
     */
    public function getTariffName()
    {
        return $this->tariff->name;
    }

    /**
     * Валидировать тип услуги
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateServiceType($attribute, $params)
    {
        if ($this->tariff->service_type_id != $this->resource->service_type_id) {
            $this->addError($attribute, 'Этот ресурс от другого типа услуги.');
        }
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        if ($this->resource->isNumber()) {
            return $this->amount;
        } else {
            return $this->amount ? '+' : '-';
        }
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
            'resource_id',
        ];
    }
}
