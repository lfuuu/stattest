<?php

namespace app\modules\uu\tarificator;

use app\models\ClientAccount;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\Bill;
use Yii;

/**
 * Расчет технического кредита МГП
 * Списали МГП (минимальный гарантированный платеж по услуге), реалтайм-баланс уменьшился (вплоть до 0), но в пределах этого списания он может тратить ресурсы (звонки), не боясь финансовой блокировки
 */
class CreditMgpTarificator extends Tarificator
{
    /**
     * @param int|null $clientAccountId Если указан, то только для этого ЛС. Если не указан - для всех
     * @throws \yii\db\Exception
     */
    public function tarificate($clientAccountId = null)
    {
        $db = Yii::$app->db;
        $versionBillerUniversal = ClientAccount::VERSION_BILLER_UNIVERSAL;
        $billTableName = Bill::tableName();
        $accountEntryTableName = AccountEntry::tableName();
        $accountLogMinTableName = AccountLogMin::tableName();

        if ($clientAccountId) {
            $subSqlAndWhere = ' AND bill.client_account_id = ' . $clientAccountId;
            $sqlAndWhere = ' AND clients.id = ' . $clientAccountId;
        } else {
            $subSqlAndWhere = '';
            $sqlAndWhere = '';
        }

        $selectSQL = <<<SQL
            SELECT
                clients.id,
                COALESCE(t.credit_mgp, 0) AS credit_mgp
            FROM
                clients
            LEFT JOIN
                (
                    SELECT
                        bill.client_account_id AS client_id,
                        SUM(COALESCE(account_log.price, 0)) AS credit_mgp
                    FROM
                        {$billTableName} bill,
                        {$accountEntryTableName} account_entry,
                        {$accountLogMinTableName} account_log
                    WHERE
                        bill.is_converted = 0
                        AND account_entry.bill_id = bill.id
                        AND account_log.account_entry_id = account_entry.id
                        {$subSqlAndWhere}
                    GROUP BY
                        bill.client_account_id
                ) t
                ON t.client_id = clients.id
            WHERE
                clients.account_version = {$versionBillerUniversal}
                AND clients.credit_mgp <> COALESCE(t.credit_mgp, 0)
                {$sqlAndWhere}
SQL;

        $query = $db->createCommand($selectSQL)
            ->query();

        foreach ($query as $row) {
            $this->out('. ');

            // делаем не multi-update, а через модель, чтобы сработали все behaviors
            $clientAccount = ClientAccount::findOne(['id' => $row['id']]);
            $clientAccount->credit_mgp = $row['credit_mgp'];
            if (!$clientAccount->save()) {
                // "Не надо фаталиться, вся жизнь впереди. Вся жизнь впереди, надейся и жди." (С) Р. Рождественский
                $this->out('Error. ' . implode('. ', $clientAccount->getFirstErrors()));
            }
        }

    }
}
