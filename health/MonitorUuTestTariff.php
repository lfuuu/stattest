<?php

namespace app\health;

use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;

/**
 * УУ. Услуга на тестовом тарифе не должна задерживаться надолго
 */
class MonitorUuTestTariff extends Monitor
{
    // позволим менеджерам увеличивать базовый срок тестового тарифа на несколько дней
    const DELTA_DAYS = 10;

    public $monitorGroup = self::GROUP_FOR_MANAGERS;

    private $_message = '';

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
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $clientAccountTableName = ClientAccount::tableName();
        $deltaDays = self::DELTA_DAYS;

        $testStatuses = implode(', ', Tariff::getTestStatuses());
        if (!$testStatuses) {
            return 0;
        }

        $sql = <<<SQL
            SELECT
                COUNT(*) as cnt,
                GROUP_CONCAT(account_tariff.id) AS message
            FROM
                {$accountTariffTableName} account_tariff
                        LEFT JOIN {$clientAccountTableName} client ON client.id = account_tariff.client_account_id
                        LEFT JOIN {$tariffPeriodTableName} tariff_period ON tariff_period.id = account_tariff.tariff_period_id
                        LEFT JOIN {$tariffTableName} tariff ON tariff.id = tariff_period.tariff_id
            WHERE
                client.voip_credit_limit_day != 0
                AND tariff.tariff_status_id IN ({$testStatuses})
                AND account_tariff.insert_time + INTERVAL tariff.count_of_validity_period day + INTERVAL {$deltaDays} day < NOW()
SQL;
        $db = AccountTariff::getDb();
        $row = $db->createCommand($sql)->queryOne();
        $this->_message = $row['message'];

        return $row['cnt'];
    }
}