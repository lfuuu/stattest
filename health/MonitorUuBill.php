<?php

namespace app\health;

use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\Bill;

/**
 * УУ. Не должно быть свежих счетов без проводок
 */
class MonitorUuBill extends MonitorUu
{
    /**
     * Текущее значение
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        $billTableName = Bill::tableName();
        $accountEntryTableName = AccountEntry::tableName();

        $sql = <<<SQL
            SELECT COUNT(*) as cnt
            FROM {$billTableName}
            WHERE
                date > :date_from
                AND id NOT IN (SELECT DISTINCT bill_id FROM {$accountEntryTableName} WHERE bill_id IS NOT NULL)
SQL;
        return $this->getValueNySql($sql);
    }
}