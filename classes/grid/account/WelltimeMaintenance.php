<?php

namespace app\classes\grid\account;

use app\models\BusinessProcess;
use app\models\Business;

class WelltimeMaintenance extends AccountGrid
{
    /* Использование трейта, который генерирует массив объектов GenericFolder, исходя из текущего контекста BusinessProcessStatus */
    use GenericFolderTrait;

    public function getBusiness()
    {
        return Business::WELLTIME;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::WELLTIME_MAINTENANCE;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager',
            'region',
        ];
    }
}