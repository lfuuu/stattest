<?php
/**
 * Периоды, для которых нет расчета тарификатора.
 * Объект сделан только для удобства, чтобы не оперировать массивом непонятной структуры
 */

namespace app\classes\uu\forms;


use app\classes\uu\model\TariffPeriod;
use DateTimeImmutable;

class AccountLogFromToTariff
{
    /** @var DateTimeImmutable $dateFrom */
    protected $dateFrom = null;

    /** @var DateTimeImmutable $dateTo */
    protected $dateTo = null;

    /** @var TariffPeriod $dateTo */
    protected $tariffPeriod = null;

    /**
     * @return DateTimeImmutable
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param DateTimeImmutable $dateFrom
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param DateTimeImmutable $dateTo
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;
    }

    /**
     * @return TariffPeriod
     */
    public function getTariffPeriod()
    {
        return $this->tariffPeriod;
    }

    /**
     * @param TariffPeriod $tariffPeriod
     */
    public function setTariffPeriod($tariffPeriod)
    {
        $this->tariffPeriod = $tariffPeriod;
    }
}