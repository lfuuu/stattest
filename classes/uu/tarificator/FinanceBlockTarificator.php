<?php

namespace app\classes\uu\tarificator;

use app\models\ClientAccount;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use Yii;

/**
 * Месячную финансовую блокировку заменить на постоянную
 */
class FinanceBlockTarificator implements TarificatorI
{
    const DAYS_LIMIT = 30; // через сколько суток непрерывной финансовой блокировки заменять на постоянную

    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     */
    public function tarificate($accountTariffId = null)
    {
        $db = Yii::$app->db;
        $dateTimeString = (new \DateTimeImmutable())->modify('-' . self::DAYS_LIMIT . ' days')->format('c');
        $clientAccountTableName = ClientAccount::tableName();
        $importantEventsTableName = ImportantEvents::tableName();
        $setZeroBalance = ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE;
        $unsetZeroBalance = ImportantEventsNames::IMPORTANT_EVENT_UNSET_ZERO_BALANCE;

        $selectSQL = <<<SQL
            SELECT
                clients.id
                
            FROM
                {$clientAccountTableName} clients

            -- найти последнюю финансовую блокировку старше месяца
            INNER JOIN
                (
                    SELECT
                        client_id,
                        MAX(`date`) as max_date
                    FROM
                        {$importantEventsTableName}
                    WHERE
                        event = :setZeroBalance
                        AND `date` < :dateTimeString
                    GROUP BY
                        client_id
                ) important_events_set_zero_balance
            ON
                clients.id = important_events_set_zero_balance.client_id
                
            -- убедиться, что финансовая блокировка после последней установки не снималась
            LEFT JOIN
                (
                    SELECT
                        client_id,
                        MAX(`date`) as max_date
                    FROM
                        {$importantEventsTableName}
                    WHERE
                        event = :unsetZeroBalance
                    GROUP BY
                        client_id
                ) important_events_unset_zero_balance
            ON
                clients.id = important_events_unset_zero_balance.client_id
                AND important_events_unset_zero_balance.max_date > important_events_set_zero_balance.max_date
                
            WHERE
                clients.is_blocked = 0
                AND important_events_unset_zero_balance.client_id IS NULL 
SQL;
        $dataReader = $db->createCommand($selectSQL, [
            ':dateTimeString' => $dateTimeString,
            ':setZeroBalance' => $setZeroBalance,
            ':unsetZeroBalance' => $unsetZeroBalance,
        ])
            ->query();

        foreach ($dataReader as $row) {
            /** @var ClientAccount $client */
            $client = ClientAccount::findOne($row['id']);

            if ($client->billingCounters->getRealtimeBalance() > 0) {
                // какая-то ошибка! баланс положительный, но находится месяц в финансовой блокировке
                // @todo записать в лог
                continue;
            }

            echo $client->id . ' ';
            $client->is_blocked = 1;
            $client->save();
        }
    }
}
