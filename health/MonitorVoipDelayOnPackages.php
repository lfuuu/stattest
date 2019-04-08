<?php

namespace app\health;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\db\Expression;
use yii\db\Query;

class MonitorVoipDelayOnPackages extends Monitor
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
        $countErrors = 0;
        $message = '';

        $utc = (new Query)->select(new Expression('UTC_TIMESTAMP()'))->scalar();

        $mainTariffs = AccountTariff::find()
            ->where(['prev_account_tariff_id' => null])
            ->andWhere(['NOT', ['tariff_period_id' => null]])
            ->andWhere(['service_type_id' => ServiceType::ID_VOIP])
            ->with('nextAccountTariffs.accountTariffLogs')
            ->asArray();
        foreach ($mainTariffs->each() as $mainTariff) {
            $packages = $mainTariff['nextAccountTariffs'];
            foreach ($packages as $package) {
                foreach ($package['accountTariffLogs'] as $accountTariffLog) {
                    if ($accountTariffLog['actual_from_utc'] > $utc) {
                        continue;
                    }
                    $isHasError = ($package['tariff_period_id'] != $accountTariffLog['tariff_period_id']);
                    break;
                }

                if ($isHasError) {
                    ++$countErrors;
                    $message .= $mainTariff['client_account_id'] . ' (' . $mainTariff['id'] . ')' . ', ';
                    continue 2;
                }
            }
        }

        $this->_message = rtrim($message, ',');

        return $countErrors;
    }
}