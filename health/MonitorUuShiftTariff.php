<?php

namespace app\health;

use app\models\HistoryChanges;
use app\modules\uu\models\AccountTariff;

/**
 * УУ. Смена тарифа на услуге не должна долго откладываться
 */
class MonitorUuShiftTariff extends Monitor
{
    /**
     * Текущее значение
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        $historyChangesTableName = HistoryChanges::tableName();

        $sql = <<<SQL
            SELECT COUNT(*) as cnt
            FROM
            (
                SELECT model_id
                FROM
                    {$historyChangesTableName} history_changes
                WHERE
                    model = :model
                    AND action = :action
                GROUP BY
                    model_id
                HAVING
                    COUNT(*) > 2
            ) t
SQL;
        $db = AccountTariff::getDb();
        return $db->createCommand($sql, [
            ':model' => AccountTariff::className(),
            ':action' => HistoryChanges::ACTION_UPDATE,
        ])->queryScalar();
    }
}