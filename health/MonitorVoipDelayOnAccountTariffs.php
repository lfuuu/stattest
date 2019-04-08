<?php

namespace app\health;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\db\Expression;
use yii\db\Query;

class MonitorVoipDelayOnAccountTariffs extends Monitor
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
            ->with('accountTariffLogs')
            ->where(['>=', 'insert_time', new Expression('NOW() - interval 7 day')])
            ->asArray();
        foreach ($mainTariffs->each() as $mainTariff) {
            foreach ($mainTariff['accountTariffLogs'] as $accountTariffLog) {
                if ($accountTariffLog['actual_from_utc'] > $utc) {
                    continue;
                }
                $isHasError = ($mainTariff['tariff_period_id'] != $accountTariffLog['tariff_period_id']);
                break;
            }

            if ($isHasError) {
                ++$countErrors;
                $message .= $mainTariff['client_account_id'] . ' (' . $mainTariff['id'] . ')' . ', ';
            }
        }

        $this->_message = rtrim($message, ',');

        return $countErrors;
    }
}