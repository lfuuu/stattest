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
        $ids = AccountTariff::find()
            ->select('id')
            ->where(['NOT', ['tariff_period_id' => null]])
            ->andWhere(['service_type_id' => ServiceType::ID_VOIP])
            ->indexBy('client_account_id')
            ->having(['>', 'count(*)', 1])
            ->groupBy('voip_number')
            ->column();

        foreach ($ids as $client_account_id => $id) {
            $message .= $client_account_id . ' (' . $id . ')' . ', ';
        }

        $this->_message = rtrim($message, ', ');

        return count($ids);
    }
}