<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\modules\callTracking\models\Log;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\BaseObject;

class AiAgentResourceReader extends BaseObject implements ResourceReaderInterface
{
    /**
     * Длительность диалогов ИИ
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @param TariffPeriod $tariffPeriod
     * @return Amounts
     * @throws \yii\db\Exception
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
        return new Amounts();

        // в БД хранится в UTC, но считать надо в зависимости от таймзоны клиента
        $clientDateTimeZone = $accountTariff->clientAccount->getTimezone();
        $utcDateTimeZone = new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT);
        $hoursDelta = (int)(
                $clientDateTimeZone->getOffset($dateTime) -
                $utcDateTimeZone->getOffset($dateTime)
            ) / 3600; // таймзона клиента в часах относительно UTC

        if ($hoursDelta >= 0) {
            $dateTimeUtc = $dateTime->modify('-' . $hoursDelta . ' hours');
        } else {
            $dateTimeUtc = $dateTime->modify('+' . abs($hoursDelta) . ' hours');
        }

        $currentDateTime = $dateTimeUtc
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $nextDateTime = (new \DateTime($currentDateTime, new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->modify('+1 day')
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $logTableName = Log::tableName();
        // Получение количества минут
        $minutes = Log::getDb()
            ->createCommand(
                "
                SELECT
                  sum(round(
                    EXTRACT (EPOCH FROM (
                      LEAST('{$nextDateTime}', stop_dt)
                        - 
                      GREATEST('{$currentDateTime}', start_dt)
                    ) :: INTERVAL) / 60
                  )) AS minutes
                FROM
                  {$logTableName}
                WHERE
                  account_tariff_id = {$accountTariff->id} AND
                  (
                    start_dt <= '{$nextDateTime}' AND stop_dt >= '{$currentDateTime}'
                  );
                "
            )->queryScalar();

        return new Amounts((int)$minutes, 0);
    }

    /**
     * Как считать PricePerUnit - указана за месяц или за день
     * true - за месяц (при ежедневном расчете надо разделить на кол-во дней в месяце)
     * false - за день (при ежедневном расчете так и оставить)
     *
     * @return bool
     */
    public function getIsMonthPricePerUnit()
    {
        return false;
    }

}