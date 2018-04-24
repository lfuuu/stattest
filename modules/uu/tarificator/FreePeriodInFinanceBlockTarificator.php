<?php

namespace app\modules\uu\tarificator;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Period;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use Yii;

/**
 * Не списывать абонентку и минималку за ВТОРОЙ и последующие периоды при финансовой блокировке
 */
class FreePeriodInFinanceBlockTarificator extends Tarificator
{
    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @throws \yii\db\Exception
     */
    public function tarificate($accountTariffId = null)
    {
        $db = Yii::$app->db;
        $accountTariffTableName = AccountTariff::tableName();
        $tariffPeriodTableName = TariffPeriod::tableName();
        $tariffTableName = Tariff::tableName();
        $importantEventsTableName = ImportantEvents::tableName();
        $clientAccountTableName = ClientAccount::tableName();

        // найти ЛС, у которых сейчас фин.блокировка
        // если менее 2х дней назад - не учитывать
        // если более 2х дней назад - только для посуточных тарифов
        // если более 2х календарных месяцев назад - для помесячных тарифов
        // для тарифов по годам можно не проверять - менеджер такое раньше сам заблокирует/отключит
        $sql = <<<SQL
            CREATE TEMPORARY TABLE set_zero_tmp
            SELECT
                client_id,
                MAX(`date`) <= :min_month_date AS is_month
            FROM
            (
                SELECT
                    client_id,
                    `date`,
                    (
                        SELECT
                             MIN(unset_zero.`date`)
                        FROM
                            {$importantEventsTableName} unset_zero
                        WHERE
                            unset_zero.client_id = set_zero.client_id
                            AND unset_zero.event = :unsetZeroBalance
                            AND unset_zero.`date` >= set_zero.`date`
                    ) AS unset_zero_date
                FROM
                    {$importantEventsTableName} set_zero
                INNER JOIN
                    {$clientAccountTableName} client
                    ON set_zero.client_id = client.id
                   
                WHERE
                    set_zero.event = :setZeroBalance 
                    AND set_zero.`date` <= :min_day_date
                    AND client.is_blocked = 1
            ) t
            
            WHERE
                unset_zero_date IS NULL
            GROUP BY
                client_id
SQL;

        $count = $db->createCommand($sql, [
            ':min_day_date' => (new DateTimeImmutable())
                ->modify('-2 days')// 2, чтобы гарантировать для любой таймзоны
                ->format(DateTimeZoneHelper::DATETIME_FORMAT),
            ':min_month_date' => (new DateTimeImmutable())
                ->modify('first day of previous month')
                ->modify('+1 day')// чтобы гарантировать для любой таймзоны
                ->format(DateTimeZoneHelper::DATETIME_FORMAT),
            ':setZeroBalance' => ImportantEventsNames::ZERO_BALANCE,
            ':unsetZeroBalance' => ImportantEventsNames::UNSET_ZERO_BALANCE,
        ])
            ->execute();
        $this->out('ЛС в фин.блокировке = ' . $count . PHP_EOL);

        $params = [
            ':day' => Period::ID_DAY,
            ':month' => Period::ID_MONTH,
            ':min_day_date' => (new DateTimeImmutable())
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            ':min_month_date' => (new DateTimeImmutable())
                ->modify('first day of this month')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
        ];

        // сбросить абонентскую плату за периоды блокировки
        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $sql = <<<SQL
            UPDATE
                set_zero_tmp,
                {$accountTariffTableName} account_tariff,
                {$accountLogPeriodTableName} account_log_period,
                {$tariffPeriodTableName} tariff_period,
                {$tariffTableName} tariff
            SET
                account_log_period.price = 0
            WHERE
                set_zero_tmp.client_id = account_tariff.client_account_id
                AND account_tariff.id = account_log_period.account_tariff_id
                AND 
                    (
                        (tariff_period.charge_period_id = :day AND account_log_period.date_from >= :min_day_date)
                        OR 
                        (set_zero_tmp.is_month AND tariff_period.charge_period_id = :month AND account_log_period.date_from >= :min_month_date)
                    )
                AND account_log_period.tariff_period_id = tariff_period.id
                AND tariff_period.tariff_id = tariff.id
                AND tariff.is_charge_after_blocking = 0
SQL;
        $count = $db->createCommand($sql, $params)->execute();
        $this->out('Абонентка сброшена = ' . $count . PHP_EOL);

        // сбросить минимальную плату за периоды блокировки
        $accountLogMinTableName = AccountLogMin::tableName();
        $sql = <<<SQL
            UPDATE
                set_zero_tmp,
                {$accountTariffTableName} account_tariff,
                {$accountLogMinTableName} account_log_min,
                {$tariffPeriodTableName} tariff_period,
                {$tariffTableName} tariff
            SET
                account_log_min.price = 0
            WHERE
                set_zero_tmp.client_id = account_tariff.client_account_id
                AND account_tariff.id = account_log_min.account_tariff_id
                AND 
                    (
                        (tariff_period.charge_period_id = :day AND account_log_min.date_from >= :min_day_date)
                        OR 
                        (set_zero_tmp.is_month AND tariff_period.charge_period_id = :month AND account_log_min.date_from >= :min_month_date)
                    )
                AND account_log_min.tariff_period_id = tariff_period.id
                AND tariff_period.tariff_id = tariff.id
                AND tariff.is_charge_after_blocking = 0
SQL;
        $count = $db->createCommand($sql, $params)->execute();
        $this->out('Минималка сброшена = ' . $count . PHP_EOL);

        // убрать за собой
        $sql = <<<SQL
            DROP TEMPORARY TABLE set_zero_tmp
SQL;
        $db->createCommand($sql)->execute();
    }
}
