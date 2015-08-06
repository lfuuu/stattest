<?php
namespace app\classes\grid\account;

use app\classes\grid\account\partner\maintenance\ClosedFolder;
use app\classes\grid\account\partner\maintenance\ActingFolder;
use app\classes\grid\account\partner\maintenance\NegotationsFolder;
use app\models\ClientGridBussinesProcess;
use app\models\ContractType;
use Yii;


class PartnerMaintenance extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::PARTNER;
    }

    public function getBusinessProcessId()
    {
        return ClientGridBussinesProcess::PARTNER_MAINTENANCE;
    }

    public function getFolders()
    {
        return [
            NegotationsFolder::create($this),
            ActingFolder::create($this),
            ClosedFolder::create($this),
        ];
    }

}