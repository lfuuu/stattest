<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\billing\DataRaw;
use app\models\mtt_raw\MttRaw;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use DateTimeZone;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\db\Expression;

/**
 * Class InternetResourceReader
 * @package app\modules\uu\resourceReader
 *
 * @property bool $isMonthPricePerUnit
 */
class InternetResourceReader extends BaseObject implements ResourceReaderInterface
{
    /**
     * Вернуть количество потребленного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @param TariffPeriod $tariffPeriod
     * @return Amounts
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
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


        $dataQuery = DataRaw::find()
            ->select([
                'price' => new Expression('SUM(cost)'),
                'qty' => new Expression('((SUM(quantity)::float)/1024/1024)')
            ])
            ->where([
                'account_id' => $accountTariff->client_account_id,
                'number_service_id' => $accountTariff->prev_account_tariff_id,
            ])
            ->andWhere(['>=', 'charge_time', $dateTimeUtc->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->andWhere(['<', 'charge_time', $dateTimeUtc->modify('+1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->groupBy(['number_service_id']);

        $data = $dataQuery->asArray()->one();

        if (!$data) {
            // нет данных
            return new Amounts(0, 0);
        }


        return new Amounts(abs($data['price']), 0);
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