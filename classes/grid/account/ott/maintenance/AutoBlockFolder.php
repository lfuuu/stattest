<?php
namespace app\classes\grid\account\ott\maintenance;

use app\models\BusinessProcessStatus;

/**
 * Class AutoBlockFolder
 */
class AutoBlockFolder extends \app\classes\grid\account\telecom\maintenance\AutoBlockFolder
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