<?php
namespace app\classes\grid\account;

use app\models\BusinessProcess;
use app\models\Business;


class TelecomMaintenance extends AccountGrid
{
    /* Использование трейта, который генерирует массив объектов GenericFolder, исходя из текущего контекста BusinessProcessStatus */
    use GenericFolderTrait;

    public function getBusiness()
    {
        return Business::TELEKOM;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::TELECOM_MAINTENANCE;
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
            'account_manager',
            'region',
            'legal_entity',
        ];
    }
}