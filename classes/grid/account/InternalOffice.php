<?php
namespace app\classes\grid\account;

use app\classes\grid\account\internaloffice\internaloffice\ClosedFolder;
use app\classes\grid\account\internaloffice\internaloffice\InternalOfficeFolder;
use app\models\BusinessProcess;
use app\models\ContractSubdivision;
use Yii;


class InternalOffice extends AccountGrid
{
    public function getContractSubdivision()
    {
        return ContractSubdivision::INTERNAL_OFFICE;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::INTERNAL_OFFICE;
    }

    public function getFolders()
    {
        return [
            InternalOfficeFolder::create($this),
            ClosedFolder::create($this),
        ];
    }

}