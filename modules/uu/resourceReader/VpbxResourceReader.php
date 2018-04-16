<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\VirtpbxStat;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use Yii;
use yii\base\Object;

abstract class VpbxResourceReader extends Object implements ResourceReaderInterface
{
    protected $fieldName = '';

    /**
     * Вернуть количество потраченного ресурса
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     * @param TariffPeriod $tariffPeriod
     * @return Amounts
     */
    public function read(AccountTariff $accountTariff, DateTimeImmutable $dateTime, TariffPeriod $tariffPeriod)
    {
        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
        $virtpbxStat = VirtpbxStat::findOne([
            'date' => $date,
            'usage_id' => $accountTariff->id,
        ]);

        if (!$virtpbxStat) {
            Yii::error(sprintf('VpbxResourceReader. Нет данных по ресурсу %s. AccountTariffId = %d, дата = %s.', $this->fieldName, $accountTariff->id, $date));
            return new Amounts;
        }

        return new Amounts($virtpbxStat->{$this->fieldName}, 0);
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
        return true;
    }
}