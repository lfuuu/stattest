<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use DateTimeZone;
use Yii;

/**
 * Проверить баланс при смене тарифа
 */
class ChangeTariffTarificator implements TarificatorI
{
    /**
     * Если не хватает денег при смене тарифа - откладывать смену по +1 день, пока деньги не появятся, тогда списать.
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    public function tarificate($accountTariffId = null)
    {
        // перебирать все услуги слишком долго. Быстрее по логу тарифов найти нужное
        // Надо учесть таймзону клиента
        $db = Yii::$app->db;
        $clientAccountTableName = ClientAccount::tableName();
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();
        $accountLogPeriodTableName = AccountLogPeriod::tableName();

        // выбрать все уникальные таймзоны
        $selectSQL = <<<SQL
            SELECT DISTINCT timezone_name
            FROM {$clientAccountTableName}
SQL;
        $timezoneQuery = $db->createCommand($selectSQL)
            ->query();

        foreach ($timezoneQuery as $timezone) {
            echo '# ';
            $timezoneName = $timezone['timezone_name'];
            $timezone = new DateTimeZone($timezoneName);
            $dateTime = (new \DateTimeImmutable())
                ->setTimezone($timezone);
            $clientDate = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
            $versionBillerUniversal = ClientAccount::VERSION_BILLER_UNIVERSAL;

            // По каждой таймзоне выбрать всех, кому сегодня (по его таймзоне) надо менять тариф и кого пока не билинговали (достаточно проверить абонентку)
            // Только для билингующихся универсально. А для старых - не проверять баланс
            $selectSQL = <<<SQL
            SELECT
                account_tariff.id AS account_tariff_id,
                account_tariff_log.id AS account_tariff_log_id
            FROM
                (
                {$clientAccountTableName} clients,
                {$accountTariffTableName} account_tariff,
                {$accountTariffLogTableName} account_tariff_log
                )
            LEFT JOIN {$accountLogPeriodTableName} account_log_period
                ON account_log_period.account_tariff_id = account_tariff.id
                AND account_log_period.date_from = '{$clientDate}'
            WHERE
                clients.timezone_name = '{$timezoneName}'
                AND clients.id = account_tariff.client_account_id
                AND clients.account_version = {$versionBillerUniversal}
                AND account_tariff.id = account_tariff_log.account_tariff_id
                AND account_tariff_log.actual_from = '{$clientDate}'
                AND account_log_period.id IS NULL
SQL;
            if ($accountTariffId) {
                $selectSQL .= " AND account_tariff.id = {$accountTariffId} ";
            }
            $accountTariffQuery = $db->createCommand($selectSQL)
                ->query();
            foreach ($accountTariffQuery as $accountTariffArray) {

                echo '. ';

                $accountTariff = AccountTariff::findOne(['id' => $accountTariffArray['account_tariff_id']]);
                $accountTariffLog = AccountTariffLog::findOne(['id' => $accountTariffArray['account_tariff_log_id']]);
                if (!$accountTariffLog->tariff_period_id) {
                    // закрыть можно при любом балансе
                    continue;
                }

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $clientAccount = $accountTariff->clientAccount;

                    ob_start();
                    (new AccountLogSetupTarificator)->tarificateAccountTariff($accountTariff);
                    (new AccountLogPeriodTarificator)->tarificateAccountTariff($accountTariff);
                    (new AccountLogResourceTarificator)->tarificateAccountTariff($accountTariff);
                    (new AccountLogMinTarificator)->tarificate($accountTariff->id);
                    (new AccountEntryTarificator)->tarificate($accountTariff->id);
                    (new BillTarificator)->tarificate($accountTariff->id);
                    (new RealtimeBalanceTarificator)->tarificate($clientAccount->id);
                    ob_end_clean();

                    $credit = $clientAccount->credit; // кредитный лимит
                    $realtimeBalance = $clientAccount->balance; // $clientAccount->billingCounters->getRealtimeBalance()
                    $realtimeBalanceWithCredit = $realtimeBalance + $credit;

                    if ($realtimeBalanceWithCredit < 0) {
                        throw new \LogicException(
                            sprintf('У клиента %d нет денег на смену тарифа по услуге %d. После смены получится на счету %.2f %s и кредит %.2f %s',
                                $accountTariff->client_account_id,
                                $accountTariff->id,
                                $realtimeBalance, $clientAccount->currency,
                                $credit, $clientAccount->currency)
                        );
                    }

                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    echo PHP_EOL . $e->getMessage() . PHP_EOL;
                    Yii::error($e->getMessage());

                    // смену тарифа отодвинуть на 1 день в надежде, что за это время клиент пополнит баланс
                    $transaction = Yii::$app->db->beginTransaction();
                    $accountTariffLog->actual_from = $dateTime->modify('+1 day')->format(DateTimeZoneHelper::DATE_FORMAT);
                    $accountTariffLog->save();
                    $transaction->commit();
                }
            }
        }
    }
}
