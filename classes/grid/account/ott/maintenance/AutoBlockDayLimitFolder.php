<?php
namespace app\classes\grid\account\ott\maintenance;

use app\models\BusinessProcessStatus;

/**
 * Class AutoBlockDayLimitFolder
 */
class AutoBlockDayLimitFolder extends \app\classes\grid\account\telecom\maintenance\AutoBlockDayLimitFolder
{
    /**
     * Получение статуса бизнес процесса
     *
     * @return int
     */
    protected function getBusinessProcessStatus()
    {
        return BusinessProcessStatus::OTT_MAINTENANCE_WORK;
    }
}