<?php
namespace app\classes\grid\account\ott\maintenance;

use app\models\BusinessProcessStatus;

/**
 * Class AutoBlock800Folder
 */
class AutoBlock800Folder extends \app\classes\grid\account\telecom\maintenance\AutoBlock800Folder
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