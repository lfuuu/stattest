<?php

namespace app\health;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\db\Expression;
use yii\db\Query;

class MonitorVoipDelayOnPackages extends Monitor
{
    private $_message = '';

    public $data = [];

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
     * @param int $secondsOffset
     * @param array $andWhere
     * @return int
     */
    public function getValue($secondsOffset = 0, $andWhere = [])
    {
        $countErrors = 0;
        $message = '';

        $utc = (new Query)
            ->select(new Expression('(UTC_TIMESTAMP()' . ($secondsOffset ? '- interval ' . $secondsOffset . ' second' : '') . ')'))
            ->scalar();

        $mainTariffs = AccountTariff::find()
            ->where(['prev_account_tariff_id' => null])
            ->andWhere(['NOT', ['tariff_period_id' => null]])
            ->andWhere(['service_type_id' => ServiceType::ID_VOIP])
            ->with('nextAccountTariffs.accountTariffLogs')
            ->asArray();

        $andWhere && $mainTariffs->andWhere($andWhere);

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
                    $this->data[] = $package;
                    ++$countErrors;
                    $message .= $mainTariff['client_account_id'] . ' (' . $mainTariff['id'] . ')' . ', ';
                    continue 2;
                }
            }
        }

        $this->_message = rtrim($message, ', ');

        return $countErrors;
    }
}