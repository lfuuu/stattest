<?php
namespace app\classes\grid\account;

use app\models\BusinessProcess;
use app\models\Business;

class InternalOffice extends AccountGrid
{
    /* Использование трейта, который генерирует массив объектов GenericFolder, исходя из текущего контекста BusinessProcessStatus */
    use GenericFolderTrait;

    public function getBusiness()
    {
        return Business::INTERNAL_OFFICE;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::INTERNAL_OFFICE;
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