<?php

namespace app\modules\uu\resourceReader;

use app\helpers\DateTimeZoneHelper;
use app\models\VirtpbxStat;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;
use Yii;
use yii\base\BaseObject;

abstract class VpbxResourceReader extends BaseObject implements ResourceReaderInterface
{
    protected $fieldName = '';

    protected $accountTariffId = null;

    /**
     * @var array
     */
    protected $cache = [];

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
        if ($this->accountTariffId !== $accountTariff->id) {
            $this->setDateToValue($accountTariff, $dateTime);
        }

        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);

        /** @var $virtpbxStat VirtpbxStat */
        $virtpbxStat = array_key_exists($date, $this->cache) ?
            $this->cache[$date] : null;

        if (!$virtpbxStat) {
            Yii::error(sprintf('VpbxResourceReader. Нет данных по ресурсу %s. AccountTariffId = %d, дата = %s.', $this->fieldName, $accountTariff->id, $date));
            return new Amounts;
        }

        return new Amounts($virtpbxStat->{$this->fieldName}, 0);
    }

    /**
     * Построить кэш по этой услуге
     *
     * @param AccountTariff $accountTariff
     * @param DateTimeImmutable $dateTime
     */
    protected function setDateToValue(AccountTariff $accountTariff, DateTimeImmutable $dateTime)
    {
        $this->accountTariffId = $accountTariff->id;

        $date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);

        $this->cache =
            VirtpbxStat::find()
            ->where([
                'usage_id' => $this->accountTariffId,
            ])
            ->andWhere(['>=', 'date', $date])
            ->indexBy('date')
            ->all();
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