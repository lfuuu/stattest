<?php

namespace app\modules\uu\tarificator;

use app\models\ClientAccount;
use app\models\Payment;
use app\models\Saldo;
use app\modules\uu\models\AccountEntryCorrection;
use app\modules\uu\models\Bill;
use app\modules\uu\models\Estimation;
use Yii;

/**
 * Пересчитать RealtimeBalance
 */
class RealtimeBalanceTarificatorWithSaldo extends Tarificator
{
    /**
     * @param int|null $clientAccountId Если указан, то только для этого ЛС. Если не указан - для всех
     * @throws \yii\db\Exception
     */
    public function tarificate($clientAccountId = null, $accountTariffId = null)
    {
        $db = Yii::$app->db;

        $clientAccountTableName = ClientAccount::tableName();
        $versionBillerUniversal = ClientAccount::VERSION_BILLER_UNIVERSAL;

        if ($clientAccountId) {
            $sqlAndWhere = ' AND clients.id = ' . $clientAccountId;
        } else {
            $sqlAndWhere = '';
        }

        // основа
        $selectSQL = <<<SQL
            CREATE TEMPORARY TABLE clients_tmp (PRIMARY KEY pk_clients_tmp (id))
            SELECT
                clients.id,
                CAST(0 AS DECIMAL(14,4)) AS balance
            FROM
                {$clientAccountTableName} clients
            WHERE
                clients.account_version = {$versionBillerUniversal}
                {$sqlAndWhere}
            GROUP BY
                clients.id
SQL;
        $db->createCommand($selectSQL)
            ->execute();
        $this->out('. ');


        $saldoTableName = Saldo::tableName();

        // saldo view
        $selectSQL = <<<SQL
            CREATE TEMPORARY TABLE clients_tmp_saldo (PRIMARY KEY clients_tmp_saldo (id))
            SELECT clients.id,
                   CAST(saldo.saldo AS DECIMAL(14, 4)) AS saldo_sum,
                   saldo.ts AS saldo_date
            FROM {$clientAccountTableName} clients,
                 {$saldoTableName} saldo
            WHERE                 
                clients.account_version = {$versionBillerUniversal}
                AND saldo.client_id = clients.id
                AND saldo.is_history = 0
                AND saldo.ts >= '2000-01-01'
                {$sqlAndWhere}
SQL;
        $db->createCommand($selectSQL)
            ->execute();
        $this->out('. ');

        // saldo apply
        $updateSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                   SELECT
                        clients.id                          AS client_id,
                        MIN(COALESCE(saldo.saldo_sum, 0))   AS balance
                   FROM
                        {$clientAccountTableName} clients
                        JOIN clients_tmp_saldo saldo on (saldo.id = clients.id)
                   WHERE
                        clients.account_version = {$versionBillerUniversal}
                        {$sqlAndWhere}
                   GROUP BY
                        clients.id
                ) t
            SET
                clients_tmp.balance = clients_tmp.balance + t.balance
            WHERE
                t.client_id = clients_tmp.id
SQL;
        $db->createCommand($updateSQL)
            ->execute();
        $this->out('. ');

        // платежи
        $paymentTableName = Payment::tableName();
        $updateSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                   SELECT
                        clients.id AS client_id,
                        SUM(payment.sum) AS balance
                   FROM
                        {$paymentTableName} payment,
                        {$clientAccountTableName} clients
                   LEFT JOIN clients_tmp_saldo saldo ON (saldo.id = clients.id)
                   WHERE
                        clients.account_version = {$versionBillerUniversal}
                        AND payment.client_id = clients.id
                        AND (saldo.id IS NULL OR payment.payment_date >= saldo.saldo_date)
                        {$sqlAndWhere}
                   GROUP BY
                        clients.id
                ) t
            SET
                clients_tmp.balance = clients_tmp.balance + t.balance
            WHERE
                t.client_id = clients_tmp.id
SQL;
        $db->createCommand($updateSQL)
            ->execute();
        $this->out('. ');


        // автоматические счета (универсальные)
        $billTableName = Bill::tableName();
        $updateSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                    SELECT
                        clients.id AS client_id,
                        SUM(COALESCE(bill.price, 0)) AS balance
                    FROM
                        {$billTableName} bill,
                        {$clientAccountTableName} clients
                    LEFT JOIN clients_tmp_saldo saldo ON (saldo.id = clients.id)
                    WHERE
                        clients.account_version = {$versionBillerUniversal}
                        AND bill.client_account_id = clients.id
                        AND (saldo.id IS NULL OR bill.date >= saldo.saldo_date)
                        {$sqlAndWhere}
                    GROUP BY
                        clients.id
                ) t
            SET
                clients_tmp.balance = clients_tmp.balance - t.balance
            WHERE
                t.client_id = clients_tmp.id
SQL;
        $db->createCommand($updateSQL)
            ->execute();
        $this->out('. ');


        // ручные счета (неуниверсальные)
        // Для УУ старый счет считается ручным, если у него нет ссылки на УУ-счет
        // Счет с задатком не учитывается, но эта логика заложена в \app\dao\BillDao::calculateBillSum, а здесь достаточно просуммировать суммы старых счетов (для zadatok она будет нулевой)
        $oldBillTableName = \app\models\Bill::tableName();
        $updateSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                    SELECT
                        clients.id AS client_id,
                        SUM(COALESCE(bill.sum, 0)) AS balance
                    FROM
                        {$oldBillTableName} bill,
                        {$clientAccountTableName} clients
                    LEFT JOIN clients_tmp_saldo saldo ON (saldo.id = clients.id)
                    WHERE
                        clients.account_version = {$versionBillerUniversal}
                        AND bill.client_id = clients.id
                        AND (saldo.id IS NULL OR bill.bill_date >= saldo.saldo_date)
                        AND bill.uu_bill_id IS NULL
                        {$sqlAndWhere}
                    GROUP BY
                        clients.id
                ) t
            SET
                clients_tmp.balance = clients_tmp.balance - t.balance
            WHERE
                t.client_id = clients_tmp.id
SQL;
        $db->createCommand($updateSQL)
            ->execute();
        $this->out('. ');


        // ручные строки в универсальных счетах
        $billLinesTableName = \app\models\BillLine::tableName();
        $updateSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                    SELECT 
                        clients.id AS client_id, 
                        SUM(nl.sum) as balance
                    FROM {$clientAccountTableName} clients
                        LEFT JOIN clients_tmp_saldo saldo ON (saldo.id = clients.id)
                        JOIN {$oldBillTableName} b ON clients.id = b.client_id
                        JOIN {$billLinesTableName} nl ON b.bill_no = nl.bill_no and nl.type != 'zadatok'
                    WHERE
                      clients.account_version = {$versionBillerUniversal}
                      AND (saldo.id IS NULL OR b.bill_date >= saldo.saldo_date)
                      AND b.uu_bill_id IS NOT NULL
                      AND nl.uu_account_entry_id IS NULL
                      {$sqlAndWhere}
                    GROUP BY clients.id
                ) t
            SET
                clients_tmp.balance = clients_tmp.balance - t.balance
            WHERE
                t.client_id = clients_tmp.id
SQL;
        $db->createCommand($updateSQL)
            ->execute();
        $this->out('. ');


        // корректировки к проводкам
        $correctionTableName = AccountEntryCorrection::tableName();
        $updateSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                    SELECT
                        correction.client_account_id AS client_id,
                        SUM(COALESCE(correction.sum, 0)) AS balance
                    FROM
                        {$clientAccountTableName} clients
                        LEFT JOIN clients_tmp_saldo saldo ON (saldo.id = clients.id)
                        JOIN {$correctionTableName} correction ON (correction.client_account_id = clients.id)
                        JOIN {$oldBillTableName} bill ON (bill.bill_no = correction.bill_no)
                    WHERE
                        clients.account_version = {$versionBillerUniversal}
                        AND (saldo.id IS NULL OR bill.bill_date >= saldo.saldo_date)
                        {$sqlAndWhere}
                    GROUP BY
                        correction.client_account_id
                ) t
            SET
                clients_tmp.balance = clients_tmp.balance - t.balance
            WHERE
                t.client_id = clients_tmp.id
SQL;

        $db->createCommand($updateSQL)
            ->execute();
        $this->out('. ');


        if ($clientAccountId) {
            // вызов триггером по конкретной модели (смена тарифа прямо сейчас или платеж). Но это не пересчитывает ресурсы, поэтому дату не надо обновлять
            $updateSqlSet = '';
        } else {
            // по крону пересчет после ресурсов. Только в этом случае надо обновить дату. А баланс надо обновлять всегда
            $updateSqlSet = ', clients.last_account_date = DATE(NOW())';
        }

        $updateSQL = <<<SQL
            UPDATE
                {$clientAccountTableName} clients,
                clients_tmp
            SET
                clients.balance = clients_tmp.balance
                {$updateSqlSet}
            WHERE
                clients.id = clients_tmp.id
SQL;
        $this->out($db->createCommand($updateSQL)
            ->execute());
        $this->out('. ');

        Estimation::deleteAll([]
            + ($clientAccountId ? ['client_account_id' => $clientAccountId] : [])
            + ($accountTariffId ? ['account_tariff_id' => $accountTariffId] : [])
        );

        $updateSQL = <<<SQL
            DROP TEMPORARY TABLE clients_tmp
SQL;
        $db->createCommand($updateSQL)
            ->execute();
        $this->out('. ');

        $updateSQL = <<<SQL
            DROP TEMPORARY TABLE clients_tmp_saldo
SQL;
        $db->createCommand($updateSQL)
            ->execute();
        $this->out('. ');
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 13337092, 'message' => 'Баланс'];
    }
}
