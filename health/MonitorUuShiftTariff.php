<?php

namespace app\health;

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
     * Текущее значение
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
                    SELECT model_id
                    FROM
                        {$historyChangesTableName}
                    WHERE
                        model = :model
                        AND action = :action
                    GROUP BY
                        model_id
                    HAVING
                        COUNT(*) > 2
                ) t
            WHERE
	            account_tariff_log.id = t.model_id
SQL;
        $db = AccountTariffLog::getDb();
        $row = $db->createCommand($sql, [
            ':model' => AccountTariffLog::className(),
            ':action' => HistoryChanges::ACTION_UPDATE,
        ])->queryOne();
        $this->_message = $row['message'];

        return $row['cnt'];
    }
}