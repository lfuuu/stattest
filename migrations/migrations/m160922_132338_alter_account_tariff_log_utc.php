<?php

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\helpers\DateTimeZoneHelper;

class m160922_132338_alter_account_tariff_log_utc extends \app\classes\Migration
{
    public function up()
    {
        $table = AccountTariffLog::tableName();
        $this->addColumn($table, 'actual_from_utc', $this->dateTime());
        $this->alterColumn($table, 'insert_time', $this->dateTime());

        // конвертировать из таймзоны клиента (actual_from) и дефолтной таймзоны (insert_time) в UTC
        $utcTimezone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC);
        $defaultTimezone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
        $activeQuery = AccountTariffLog::find()->where(['>=', 'account_tariff_id', AccountTariff::DELTA]);
        /** @var AccountTariffLog $accountTariffLog */
        foreach ($activeQuery->each() as $accountTariffLog) {
            $clientTimezone = $accountTariffLog->accountTariff->clientAccount->getTimezone();

            $actualFrom = (new DateTimeImmutable($accountTariffLog->actual_from, $clientTimezone))
                ->setTimezone($utcTimezone)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);

            $insertTime = (new DateTimeImmutable($accountTariffLog->insert_time, $defaultTimezone))
                ->setTimezone($utcTimezone)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);

//            printf('%s %s %s' . PHP_EOL, $accountTariffLog->accountTariff->clientAccount->timezone_name, $accountTariffLog->actual_from, $actualFrom);

            // обязательно не через модель, иначе сработают триггеры
            AccountTariffLog::updateAll(['actual_from_utc' => $actualFrom, 'insert_time' => $insertTime], ['id' => $accountTariffLog->id]);
        }

        $this->dropColumn($table, 'actual_from');
    }

    public function down()
    {
        $table = AccountTariffLog::tableName();
        $this->addColumn($table, 'actual_from', $this->date());
        $this->alterColumn($table, 'insert_time', $this->timestamp());

        // конвертировать из UTC в таймзону клиента (actual_from) и дефолтную таймзону (insert_time)
        $utcTimezone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC);
        $defaultTimezone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
        $activeQuery = AccountTariffLog::find()->where(['>=', 'account_tariff_id', AccountTariff::DELTA]);
        /** @var AccountTariffLog $accountTariffLog */
        foreach ($activeQuery->each() as $accountTariffLog) {
            $clientTimezone = $accountTariffLog->accountTariff->clientAccount->getTimezone();

            $actualFrom = (new DateTimeImmutable($accountTariffLog->actual_from, $utcTimezone))
                ->setTimezone($clientTimezone)
                ->format(DateTimeZoneHelper::DATE_FORMAT);

            $insertTime = (new DateTimeImmutable($accountTariffLog->insert_time, $utcTimezone))
                ->setTimezone($defaultTimezone)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);

            // обязательно не через модель, иначе сработают триггеры
            AccountTariffLog::updateAll(['actual_from' => $actualFrom, 'insert_time' => $insertTime], ['id' => $accountTariffLog->id]);
        }

        $this->dropColumn($table, 'actual_from_utc');
    }
}