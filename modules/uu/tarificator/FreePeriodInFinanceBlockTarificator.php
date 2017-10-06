<?php

namespace app\modules\uu\tarificator;

use app\models\ClientAccount;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\usages\UsageInterface;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use Yii;

/**
 * Не списывать абонентку и минималку при финансовой блокировке
 * Ибо ЛС все равно не может пользоваться услугами
 *
 *  Надо следить, чтобы не возникала ситуация:
 * - списали абонентку в минус
 * - клиент ушел в финансовую блокировку
 * - отменили абонентку
 * - клиент вышел из финансовой блокировки
 * - дальше либо сказка про белого бычка, либо клиент пользуется услугой бесплатно
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

        // найти все даты установки фин.блокировки
        // для каждой найти дату ее снятия (минимальную больше заданной), но ее может и не быть (тогда ее считаем максимальной)
        $sql = <<<SQL
            CREATE TEMPORARY TABLE set_unset_zero_tmp
            SELECT
                set_zero.client_id AS client_id,
                DATE(DATE_ADD(set_zero.`date`, INTERVAL 1 DAY)) AS date_set_zero,
                COALESCE(
                    (
                        SELECT
                             MIN(DATE(unset_zero.`date`))
                        FROM
                            {$importantEventsTableName} unset_zero
                        WHERE
                            unset_zero.client_id = set_zero.client_id
                            AND unset_zero.event = :unsetZeroBalance
                            AND unset_zero.`date` >= set_zero.`date`
                    ),
                    :max_date
                ) AS date_unset_zero
                
            FROM
                {$importantEventsTableName} set_zero
               
            WHERE
                set_zero.event = :setZeroBalance 
            
            GROUP BY
                set_zero.client_id,
                set_zero.`date`
SQL;

        $count = $db->createCommand($sql, [
            ':max_date' => UsageInterface::MAX_POSSIBLE_DATE,
            ':setZeroBalance' => ImportantEventsNames::ZERO_BALANCE,
            ':unsetZeroBalance' => ImportantEventsNames::UNSET_ZERO_BALANCE,
        ])->execute();
        $this->out('Периодов фин.блокировки = ' . $count . PHP_EOL);

        // сбросить абонентскую плату за периоды блокировки
        $accountLogPeriodTableName = AccountLogPeriod::tableName();
        $sql = <<<SQL
            UPDATE
                set_unset_zero_tmp,
                {$accountTariffTableName} account_tariff,
                {$accountLogPeriodTableName} account_log_period,
                {$tariffPeriodTableName} tariff_period,
                {$tariffTableName} tariff
            SET
                account_log_period.price = 0
            WHERE
                set_unset_zero_tmp.client_id = account_tariff.client_account_id
                AND account_tariff.id = account_log_period.account_tariff_id
                AND account_log_period.date_from > set_unset_zero_tmp.date_set_zero
                AND account_log_period.date_to < set_unset_zero_tmp.date_unset_zero
                AND account_log_period.tariff_period_id = tariff_period.id
                AND tariff_period.tariff_id = tariff.id
                AND tariff.is_charge_after_blocking = 0
SQL;
        $count = $db->createCommand($sql)->execute();
        $this->out('Абонентка сброшена = ' . $count . PHP_EOL);

        // сбросить минимальную плату за периоды блокировки
        $accountLogMinTableName = AccountLogMin::tableName();
        $sql = <<<SQL
            UPDATE
                set_unset_zero_tmp,
                {$accountTariffTableName} account_tariff,
                {$accountLogMinTableName} account_log_min,
                {$tariffPeriodTableName} tariff_period,
                {$tariffTableName} tariff
            SET
                account_log_min.price = 0
            WHERE
                set_unset_zero_tmp.client_id = account_tariff.client_account_id
                AND account_tariff.id = account_log_min.account_tariff_id
                AND account_log_min.date_from > set_unset_zero_tmp.date_set_zero
                AND account_log_min.date_to < set_unset_zero_tmp.date_unset_zero
                AND account_log_min.tariff_period_id = tariff_period.id
                AND tariff_period.tariff_id = tariff.id
                AND tariff.is_charge_after_blocking = 0
SQL;
        $count = $db->createCommand($sql)->execute();
        $this->out('Минималка сброшена = ' . $count . PHP_EOL);

        // убрать за собой
        $sql = <<<SQL
            DROP TEMPORARY TABLE set_unset_zero_tmp
SQL;
        $db->createCommand($sql)->execute();
    }
}
