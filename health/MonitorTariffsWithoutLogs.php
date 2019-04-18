<?php

namespace app\health;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use yii\db\Expression;

class MonitorTariffsWithoutLogs extends Monitor
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

        $ids = AccountTariff::find()
            ->select('id')
            ->alias('uat')
            ->where(['NOT EXISTS', AccountTariffLog::find()->where(['account_tariff_id' => new Expression('uat.id')])])
            ->indexBy('client_account_id')
            ->column();

        foreach ($ids as $client_account_id => $id) {
            $message .= $client_account_id . ' (' . $id . ')' . ', ';
        }

        $this->_message = rtrim($message, ', ');

        return count($ids);
    }
}