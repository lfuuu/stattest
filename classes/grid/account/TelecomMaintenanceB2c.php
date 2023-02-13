<?php
namespace app\classes\grid\account;

use app\models\BusinessProcess;
use app\models\Business;


class TelecomMaintenanceB2c extends AccountGrid
{
    /* Использование трейта, который генерирует массив объектов GenericFolder, исходя из текущего контекста BusinessProcessStatus */
    use GenericFolderTrait;

    public function getBusiness()
    {
        return Business::TELEKOM;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::TELECOM_MAINTENANCE_B2C;
    }

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
            'legal_entity',
        ];
    }
}