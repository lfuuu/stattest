<?php

use app\classes\DateTimeWithUserTimezone;
use app\classes\uu\model\AccountTariffLog;
use app\helpers\DateTimeZoneHelper;

class m160922_132338_alter_account_tariff_log_utc extends \app\classes\Migration
{
    public function up()
    {
        $table = AccountTariffLog::tableName();
        $this->alterColumn($table, 'actual_from', $this->timestamp());
        $utcTimezone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC);

        $activeQuery = AccountTariffLog::find();
        /** @var AccountTariffLog $accountTariffLog */
        foreach($activeQuery->each() as $accountTariffLog) {
            $clientTimezone = $accountTariffLog->accountTariff->clientAccount->getTimezone();
            (new DateTimeImmutable($accountTariffLog->actual_from, $clientTimezone))
            ->setTimezone($utcTimezone)
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }
    }

    public function down()
    {
        $table = AccountTariffLog::tableName();
        $this->alterColumn($table, 'actual_from', $this->date());
    }
}