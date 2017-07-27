<?php
/**
 * Периоды, для которых нет расчета ресурсов.
 * Объект сделан только для удобства, чтобы не оперировать массивом непонятной структуры
 */

namespace app\modules\uu\classes;


use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\TariffPeriod;
use DateTimeImmutable;

class AccountLogFromToResource
{
    /** @var DateTimeImmutable $dateFrom */
    public $dateFrom = null;

    /** @var DateTimeImmutable $dateTo */
    public $dateTo = null;

    /** @var TariffPeriod $tariffPeriod */
    public $tariffPeriod = null;

    /** @var float */
    public $amountOverhead = null;

    /** @var int */
    public $account_tariff_resource_log_id = null;

    /**
     * Вернуть уникальный Id
     * Поле id хоть и уникальное, но не подходит для поиска нерассчитанных данных при тарификации
     *
     * @return string
     */
    public function getUniqueId()
    {
        return
            $this->dateFrom->format(DateTimeZoneHelper::DATE_FORMAT) .
            '_' .
            $this->dateTo->format(DateTimeZoneHelper::DATE_FORMAT) .
            '_' .
            $this->account_tariff_resource_log_id;
    }
}