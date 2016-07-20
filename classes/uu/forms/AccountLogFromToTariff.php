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
    public $dateFrom = null;

    /** @var DateTimeImmutable $dateTo */
    public $dateTo = null;

    /** @var TariffPeriod $tariffPeriod */
    public $tariffPeriod = null;

    /** @var bool $isFirst нужно для списывания платы за подключение номера  */
    public $isFirst = null;

    /**
     * Вернуть уникальный Id
     * Поле id хоть и уникальное, но не подходит для поиска нерассчитанных данных при тарификации
     * @return string
     */
    public function getUniqueId()
    {
        return $this->dateFrom->format('Y-m-d') . '_' . ($this->tariffPeriod ? $this->tariffPeriod->id : '');
    }
}