<?php
namespace app\classes\grid\account\ott\maintenance;

use app\models\BusinessProcessStatus;

/**
 * Class AutoBlockCreditFolder
 */
class AutoBlockCreditFolder extends \app\classes\grid\account\telecom\maintenance\AutoBlockCreditFolder
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