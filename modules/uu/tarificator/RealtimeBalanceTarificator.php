<?php

namespace app\modules\uu\tarificator;

use app\models\ClientAccount;
use app\models\Payment;
use app\modules\uu\models\Bill;
use app\modules\uu\models\Estimation;
use Yii;

/**
 * Пересчитать RealtimeBalance
 */
class RealtimeBalanceTarificator extends Tarificator
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
            CREATE TEMPORARY TABLE clients_tmp
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

        // платежи
        $paymentTableName = Payment::tableName();
        $selectSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                   SELECT
                        clients.id AS client_id,
                        SUM(COALESCE(payment.sum, 0)) AS balance
                   FROM
                        {$clientAccountTableName} clients,
                        {$paymentTableName} payment
                   WHERE
                        clients.account_version = {$versionBillerUniversal}
                        AND payment.client_id = clients.id
                        {$sqlAndWhere}
                   GROUP BY
                        clients.id
                ) t
            SET
                clients_tmp.balance = clients_tmp.balance + t.balance
            WHERE
                t.client_id = clients_tmp.id
SQL;
        $db->createCommand($selectSQL)
            ->execute();
        $this->out('. ');


        // автоматические счета (универсальные)
        $billTableName = Bill::tableName();
        $selectSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                    SELECT
                        clients.id AS client_id,
                        SUM(COALESCE(bill.price, 0)) AS balance
                    FROM
                        {$clientAccountTableName} clients,
                        {$billTableName} bill
                    WHERE
                        clients.account_version = {$versionBillerUniversal}
                        AND bill.client_account_id = clients.id
                        {$sqlAndWhere}
                    GROUP BY
                        clients.id
                ) t
            SET
                clients_tmp.balance = clients_tmp.balance - t.balance
            WHERE
                t.client_id = clients_tmp.id
SQL;
        $db->createCommand($selectSQL)
            ->execute();
        $this->out('. ');


        // ручные счета (неуниверсальные)
        // Для УУ старый счет считается ручным, если у него нет ссылки на УУ-счет
        // Счет с задатком не учитывается, но эта логика заложена в \app\dao\BillDao::calculateBillSum, а здесь достаточно просуммировать суммы старых счетов (для zadatok она будет нулевой)
        $oldBillTableName = \app\models\Bill::tableName();
        $selectSQL = <<<SQL
            UPDATE
                clients_tmp,
                (
                    SELECT
                        clients.id AS client_id,
                        SUM(COALESCE(bill.sum, 0)) AS balance
                    FROM
                        {$clientAccountTableName} clients,
                        {$oldBillTableName} bill
                    WHERE
                        clients.account_version = {$versionBillerUniversal}
                        AND bill.client_id = clients.id
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
        $db->createCommand($selectSQL)
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
    }

    /**
     * @return array
     */
    public static function getHelpConfluence()
    {
        return ['confluenceId' => 13337092, 'message' => 'Баланс'];
    }
}
