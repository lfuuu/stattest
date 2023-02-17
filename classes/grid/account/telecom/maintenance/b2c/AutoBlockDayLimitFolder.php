<?php
namespace app\classes\grid\account\telecom\maintenance\b2c;

use app\models\BusinessProcessStatus;

class AutoBlockDayLimitFolder extends \app\classes\grid\account\telecom\maintenance\AutoBlockDayLimitFolder
{
    /**
     * Получение статуса бизнес процесса
     *
     * @return int
     */
    protected function getBusinessProcessStatus()
    {
        return BusinessProcessStatus::TELEKOM_MAINTENANCE_B2C_WORK;
    }
}