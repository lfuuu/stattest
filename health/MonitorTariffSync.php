<?php

namespace app\health;

use app\modules\uu\models\Period;
use app\modules\uu\models\Tariff;

class MonitorTariffSync extends Monitor
{
    private $_message = '';

    /**
     * @inheritdoc
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 1, 1];
    }

    /**
     * Получение сообщения для статуса
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Текущее значение
     *
     * @return int
     */
    public function getValue()
    {
        $message = '';
        $countErrors = 0;

        $tariffsQuery = Tariff::find()->orderBy(['id' => SORT_ASC]);

        foreach ($tariffsQuery->each() as $tariff) {
            /** @var Tariff $tariff */
            $package = $tariff->package;
            $tariffPeriod = $tariff
                ->getTariffPeriods()
                ->where(['charge_period_id' => Period::ID_MONTH])
                ->orderBy(['id' => SORT_DESC])
                ->one();
            $priceMin = $tariffPeriod ? $tariffPeriod->price_min : 0;
            $mismatchedFields = [];
            if (!$package) {
                $message .= $tariff->id . ' (no package), ';
                $countErrors++;
                continue;
            }

            if ($tariff->currency_id != $package->currency_id) {
                $mismatchedFields[] = 'currency_id';
            }
            if ($tariff->is_include_vat != $package->is_include_vat) {
                $mismatchedFields[] = 'is_include_vat';
            }
            if ($tariff->service_type_id != $package->service_type_id) {
                $mismatchedFields[] = 'service_type_id';
            }
            if ($priceMin != $package->price_min) {
                $mismatchedFields[] = 'price_min';
            }

            if ($mismatchedFields) {
                $countErrors++;
                $message .= $tariff->id . ' (' . implode(', ', $mismatchedFields) .  '), ';
            }
        }

        $this->_message = rtrim($message, ', ');

        return $countErrors;
    }
}