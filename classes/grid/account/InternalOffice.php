<?php
namespace app\classes\grid\account;

use app\classes\grid\account\internaloffice\internaloffice\ClosedFolder;
use app\classes\grid\account\internaloffice\internaloffice\InternalOfficeFolder;
use app\models\ClientGridBussinesProcess;
use app\models\ContractType;
use Yii;


class InternalOffice extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::INTERNAL_OFFICE;
    }

    public function getBusinessProcessId()
    {
        return ClientGridBussinesProcess::INTERNAL_OFFICE;
    }

    public function getFolders()
    {
        return [
            InternalOfficeFolder::create($this),
            ClosedFolder::create($this),
        ];
    }

}