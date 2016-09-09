<?php

namespace app\classes\uu\model;

use Yii;

/**
 * Стоимость тарифа
 *
 * @property integer $id
 * @property float $price_per_period
 * @property float $price_setup
 * @property float $price_min
 * @property int $tariff_id
 * @property int $period_id
 * @property int $charge_period_id
 *
 * @property Period $period
 * @property Period $chargePeriod
 * @property Tariff $tariff
 */
class TariffPeriod extends \yii\db\ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    const IS_NOT_SET = -1;
    const IS_SET = -2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_tariff_period';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price_per_period', 'price_min', 'price_setup'], 'required'],
            [['charge_period_id'], 'required'],
            [['period_id'], 'required'],
            [['price_per_period', 'price_min', 'price_setup'], 'number'],
            ['tariff_id', 'getIsOneTime'], // не важно, что он вернет. Важно, чтобы проверки прошли, и не было exception
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        $tariff = $this->tariff;
        return sprintf('%s %d+%d %s %s/%s',
            $tariff->name,
            $this->price_setup, $this->price_per_period,
            $tariff->currency_id,
            $this->period->name, $this->chargePeriod->name
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->tariff->getUrl();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPeriod()
    {
        return $this->hasOne(Period::className(), ['id' => 'period_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChargePeriod()
    {
        return $this->hasOne(Period::className(), ['id' => 'charge_period_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @param int $defaultTariffPeriodId
     * @param int $serviceTypeId
     * @param int $currency
     * @param bool $isWithEmpty
     * @return []
     */
    public static function getList(
        &$defaultTariffPeriodId,
        $serviceTypeId,
        $currency = null,
        $cityId = null,
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        $defaultTariffPeriodId = null;

        $activeQuery = self::find()
            ->innerJoinWith('tariff tariff')
            ->andWhere(['tariff.service_type_id' => $serviceTypeId])
            ->orderBy(['tariff.tariff_status_id' => SORT_ASC]);

        if ($currency) {
            $activeQuery->andWhere(['tariff.currency_id' => $currency]);
        }

        if ($cityId && ($serviceTypeId == ServiceType::ID_VOIP || $serviceTypeId == ServiceType::ID_VOIP_PACKAGE)) {
            $activeQuery
                ->innerJoin(TariffVoipCity::tableName() . ' cities', 'tariff.id = cities.tariff_id')
                ->andWhere(['cities.city_id' => $cityId]);
        }

        $selectboxItems = [];

        if ($isWithEmpty) {
            $selectboxItems[''] = '----';
        }

        if ($isWithNullAndNotNull) {
            $selectboxItems[self::IS_NOT_SET] = Yii::t('common', 'Switched off');
            $selectboxItems[self::IS_SET] = Yii::t('common', 'Switched on');
        }

        /** @var TariffPeriod $tariffPeriod */
        foreach ($activeQuery->each() as $tariffPeriod) {
            $status = $tariffPeriod->tariff->status->name; // @todo надо бы заджойнить таблицу status
            if ($tariffPeriod->tariff->is_default) {
                $defaultTariffPeriodId = $tariffPeriod->id;
            }
            if (!isset($selectboxItems[$status])) {
                $selectboxItems[$status] = [];
            }
            $selectboxItems[$status][$tariffPeriod->id] = $tariffPeriod->getName();
        }
        return $selectboxItems;
    }

    /**
     * Одноразовый ли? Относится только к пакетам
     * Одноразовый не продлевается, не имеет абонентки, имеет плату за подключение. В качестве бонуса нет лимита по времени
     * @return bool
     */
    public function getIsOneTime()
    {
        $tariff = $this->tariff;

        if ($tariff->service_type_id != ServiceType::ID_VOIP_PACKAGE) {
            // не пакет
            return false;
        }

        if ($tariff->getIsTest()) {
            // к тестовому неприменимо
            return false;
        }

        if ($tariff->is_autoprolongation) {
            // не одноразовый
            return false;
        }

        if ($this->price_setup && !$this->price_per_period && !$this->price_min && !$tariff->count_of_validity_period) {
            // одноразовый
            return true;
        }

        throw new \LogicException('Одноразовый тариф не продлевается, не имеет абонентки, имеет плату за подключение');

    }
}
