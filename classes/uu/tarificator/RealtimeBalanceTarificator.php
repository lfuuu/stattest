<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\Bill;
use app\models\ClientAccount;
use app\models\Payment;
use Yii;

/**
 * Пересчитать RealtimeBalance
 */
class RealtimeBalanceTarificator implements TarificatorI
{
    /**
     * @param int|null $accountClientId Если указан, то только для этого аккаунта. Если не указан - для всех
     */
    public function tarificate($accountClientId = null)
    {
        $db = Yii::$app->db;

        $clientAccountTableName = ClientAccount::tableName();
        $paymentTableName = Payment::tableName();
        $billTableName = Bill::tableName();
        $versionBillerUniversal = ClientAccount::VERSION_BILLER_UNIVERSAL;

        if ($accountClientId) {
            $sqlAndWhere = ' AND clients.id = ' . $accountClientId;
        } else {
            $sqlAndWhere = '';
        }

        $selectSQL = <<<SQL
            CREATE TEMPORARY TABLE clients_tmp
            SELECT
                clients.id,
                COALESCE(SUM(payment.sum), 0) - COALESCE(SUM(bill.price), 0) AS balance
            FROM
                {$clientAccountTableName} clients
            LEFT JOIN {$paymentTableName} payment
                ON payment.client_id = clients.id
            LEFT JOIN {$billTableName} bill
                ON bill.client_account_id = clients.id
            WHERE
                clients.account_version = {$versionBillerUniversal}
                {$sqlAndWhere}
            GROUP BY
                clients.id
SQL;
        $db->createCommand($selectSQL)
            ->query();
        echo '. ';

        if ($accountClientId) {
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
        $db->createCommand($updateSQL)
            ->query();
        echo '. ';

        $updateSQL = <<<SQL
            DROP TEMPORARY TABLE clients_tmp
SQL;
        $db->createCommand($updateSQL)
            ->query();
        echo '. ';

        // Еще в реалтайм-балансе надо учитывать ручные счета
        // Для УУ старый счет считается ручным, если у него нет ссылки на УУ-счет
        // Счет с задатком не учитывается, но эта логика заложена в \app\dao\BillDao::calculateBillSum, а здесь достаточно просуммировать суммы старых счетов (для zadatok она будет нулевой)
        $oldBillTableName = \app\models\Bill::tableName();
        $updateSQL = <<<SQL
            UPDATE
                {$clientAccountTableName} clients,
                {$oldBillTableName} bill
            SET
                clients.balance = clients.balance - bill.sum
                {$updateSqlSet}
            WHERE
                clients.account_version = {$versionBillerUniversal}
                {$sqlAndWhere}
                AND clients.id = bill.client_id
                AND bill.uu_bill_id IS NULL
SQL;
        $db->createCommand($updateSQL)
            ->query();
        echo '. ';

    }
}
