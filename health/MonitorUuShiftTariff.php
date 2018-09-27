<?php

namespace app\health;

use app\helpers\DateTimeZoneHelper;
use app\models\HistoryChanges;
use app\modules\uu\models\AccountTariffLog;

/**
 * УУ. Смена тарифа на услуге не должна долго откладываться
 */
class MonitorUuShiftTariff extends Monitor
{
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
     * За последние сутки не должно быть откладываний смен тарифа
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        $historyChangesTableName = HistoryChanges::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();

        $sql = <<<SQL
            SELECT
                COUNT(*) as cnt,
                GROUP_CONCAT(account_tariff_log.account_tariff_id) AS message
            FROM
                {$accountTariffLogTableName} account_tariff_log,
                (
                    SELECT DISTINCT model_id
                    FROM
                        {$historyChangesTableName}
                    WHERE
                        model = :model
                        AND action = :action
                        AND created_at > :date
                ) t
            WHERE
	            account_tariff_log.id = t.model_id
SQL;
        $db = AccountTariffLog::getDb();
        $row = $db->createCommand($sql, [
            ':model' => AccountTariffLog::class,
            ':action' => HistoryChanges::ACTION_UPDATE,
            ':date' => DateTimeZoneHelper::getUtcDateTime()->modify('-1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT),
        ])->queryOne();
        $this->_message = $row['message'];

        return $row['cnt'];
    }
}