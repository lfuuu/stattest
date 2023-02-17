<?php

namespace app\classes\grid\account\telecom\maintenance\b2c;

use app\models\BusinessProcessStatus;

class AutoBlock800Folder extends \app\classes\grid\account\telecom\maintenance\AutoBlock800Folder
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