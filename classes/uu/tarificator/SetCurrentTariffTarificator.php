<?php

namespace app\classes\uu\tarificator;

use app\classes\Event;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use Yii;

/**
 * Обновить AccountTariff.TariffPeriod на основе AccountTariffLog
 */
class SetCurrentTariffTarificator implements TarificatorI
{
    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     * @throws \Exception
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $db = Yii::$app->db;
        $accountTariffTableName = AccountTariff::tableName();
        $accountTariffLogTableName = AccountTariffLog::tableName();

        // найти все услуги, у которых надо обновить тариф
        $sql = <<<SQL
            SELECT
                account_tariff.id,
                account_tariff.tariff_period_id,
                (
                    SELECT
                        account_tariff_log.tariff_period_id
                    FROM
                        {$accountTariffLogTableName} account_tariff_log
                    WHERE
                        account_tariff.id = account_tariff_log.account_tariff_id
                        AND account_tariff_log.actual_from_utc <= :now
                    ORDER BY
                        account_tariff_log.actual_from_utc DESC,
                        account_tariff_log.id DESC
                    LIMIT 1
                ) AS new_tariff_period_id
            FROM
                {$accountTariffTableName} account_tariff
            WHERE
                account_tariff.id >= :delta
SQL;
        if ($accountTariffId) {
            // только конкретную услугу, даже если не надо менять тариф
            $sql .= " AND account_tariff.id = {$accountTariffId} ";
        } else {
            // все услуги, где надо менять тариф
            $sql .= ' HAVING IFNULL(account_tariff.tariff_period_id, 0) != IFNULL(new_tariff_period_id, 0)';
        }

        $query = $db->createCommand(
            $sql,
            [
                ':delta' => AccountTariff::DELTA, // только новые, а не сконвертированные
                ':now' => DateTimeZoneHelper::getUtcDateTime()
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ]
        )
            ->query();

        foreach ($query as $row) {

            $accountTariff = AccountTariff::findOne(['id' => $row['id']]);

            $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
            try {

                if ($accountTariff->tariff_period_id && $accountTariff->tariff_period_id != $row['new_tariff_period_id'] && $row['new_tariff_period_id']) {
                    // Проверить баланс при смене тарифа (но не при закрытии услуги)
                    $this->checkBalance($accountTariff);
                }

                // услуга подключена или изменена - надо УУ-счет конвертировать в старую бухгалтерию
                $eventQueue = Event::go(Event::UU_TARIFICATE, [
                    'client_account_id' => $accountTariff->client_account_id,
                    'account_tariff_id' => $accountTariff->id,
                ]);
                // но не прямо сейчас, а чуть позже, когда создадутся проводки и УУ-счет
                // для этого добавляем отложенное событие в BillTarificator. Когда он построит УУ-счет - тогда и разрешит конвертировать УУ-счет в старую бухгалтерию
                // если вдруг что-то пойдет не так, тогда гарантированно выполним событие через заданное время с большим запасом
                $eventQueue->status = EventQueue::STATUS_ERROR;
                $eventQueue->next_start = date("Y-m-d H:i:s", strtotime($accountTariffId ? '+3 minutes' : '+3 hours'));
                $eventQueue->save();
                BillTarificator::$eventQueues[] = $eventQueue;

                // доп. обработка в зависимости от типа услуги
                switch ($accountTariff->service_type_id) {
                    case ServiceType::ID_VOIP: {
                        Event::go(Event::UU_ACCOUNT_TARIFF_VOIP, [
                            'account_id' => $accountTariff->client_account_id,
                            'account_tariff_id' => $accountTariff->id,
                            'number' => $accountTariff->voip_number
                        ]);
                        break;
                    }

                    case ServiceType::ID_VPBX: {
                        Event::go(Event::UU_ACCOUNT_TARIFF_VPBX, [
                            'account_id' => $accountTariff->client_account_id,
                            'account_tariff_id' => $accountTariff->id,
                        ]);
                        break;
                    }
                }

                if ($accountTariff->tariff_period_id != $row['new_tariff_period_id']) {
                    // сменить тариф
                    $accountTariff->tariff_period_id = $row['new_tariff_period_id'];
                    $accountTariff->save();
                }

                $isWithTransaction && $transaction->commit();

            } catch (\LogicException $e) {
                $isWithTransaction && $transaction->rollBack();
                echo PHP_EOL . $e->getMessage() . PHP_EOL;
                Yii::error($e->getMessage());

                // смену тарифа отодвинуть на 1 день в надежде, что за это время клиент пополнит баланс
                $isWithTransaction && $transaction = Yii::$app->db->beginTransaction();
                $accountTariffLog = reset($accountTariff->accountTariffLogs);
                $accountTariffLog->actual_from_utc = (new \DateTimeImmutable($accountTariffLog->actual_from_utc))
                    ->modify('+1 day')
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);
                $accountTariffLog->save();
                $isWithTransaction && $transaction->commit();

            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                echo PHP_EOL . $e->getMessage() . PHP_EOL;
                Yii::error($e->getMessage());
                if ($accountTariffId) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Проверить баланс при смене тарифа
     * Если не хватает денег при смене тарифа - откладывать смену по +1 день, пока деньги не появятся, тогда списать.
     *
     * @param AccountTariff $accountTariff
     * @return bool
     */
    protected function checkBalance(AccountTariff $accountTariff)
    {
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

    }
}
