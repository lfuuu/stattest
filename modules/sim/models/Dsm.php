<?php

namespace app\modules\sim\models;
use app\modules\nnp\models\Number;

/**
 * Class Dsm
 * Класс Dsm (Data State Model) - предназначен для хранения состояния при работе с сим-картами
 */
class Dsm
{
    // С неопределенным номером
    const ENV_WITH_RAW_NUMBER = 'raw_number';
    // С использованием первого свободного номера с выбранного склада
    const ENV_WITH_WAREHOUSE = 'warehouse_status';

    /**
     * @var Card
     */
    public $origin;

    /**
     * @var VirtualCard
     */
    public $virtual;

    /**
     * @var int
     */
    public $rawNumber;

    /**
     * @var Number
     */
    public $unassignedNumber;

    /**
     * @var int
     */
    public $warehouseId;

    /**
     * @var array
     */
    public $errorMessages = [];

    /**
     * Метод, разрешающий синхронизацию
     *
     * @return bool
     */
    public function isSynchronizable()
    {
        return $this->virtual || $this->unassignedNumber;
    }
}