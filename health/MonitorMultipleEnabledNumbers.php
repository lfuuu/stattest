<?php

namespace app\health;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

class MonitorMultipleEnabledNumbers extends MonitorUu
{
    private $_message = '';

    /**
     * @inheritdoc
     * @return array
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
        $numbers = AccountTariff::find()
            ->select('GROUP_CONCAT(client_account_id)')
            ->where(['NOT', ['tariff_period_id' => null]])
            ->andWhere(['service_type_id' => ServiceType::ID_VOIP])
            ->indexBy('voip_number')
            ->having(['>', 'count(*)', 1])
            ->groupBy('voip_number')
            ->column();

        foreach ($numbers as $number => $clientAccountIds) {
            $message .= $number . ' (' . $clientAccountIds . ')' . ' ';
        }

        $this->_message = rtrim($message, ' ');

        return count($numbers);
    }
}