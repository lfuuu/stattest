<?php

namespace app\health;

use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;

/**
 * УУ. Не должно быть свежих проводок без транзакций
 */
class MonitorUuAccountEntry extends MonitorUu
{
    /**
     * Текущее значение
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function getValue()
    {
        $accountEntryTableName = AccountEntry::tableName();
        $accountLogMinTableName = AccountLogMin::tableName();
        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $accountLogResourceTableName = AccountLogResource::tableName();
        $accountLogSetupTableName = AccountLogSetup::tableName();

        $sql = <<<SQL
            SELECT COUNT(*) as cnt
            FROM {$accountEntryTableName}
            WHERE
                date > :date_from
                AND id NOT IN (SELECT DISTINCT account_entry_id FROM {$accountLogMinTableName} WHERE account_entry_id IS NOT NULL)
                AND id NOT IN (SELECT DISTINCT account_entry_id FROM {$accountLogPeriodTableName} WHERE account_entry_id IS NOT NULL)
                AND id NOT IN (SELECT DISTINCT account_entry_id FROM {$accountLogResourceTableName} WHERE account_entry_id IS NOT NULL)
                AND id NOT IN (SELECT DISTINCT account_entry_id FROM {$accountLogSetupTableName} WHERE account_entry_id IS NOT NULL)
SQL;
        return $this->getValueNySql($sql);
    }
}