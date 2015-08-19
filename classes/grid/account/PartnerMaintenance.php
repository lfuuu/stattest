<?php
namespace app\classes\grid\account;

use app\classes\grid\account\partner\maintenance\ClosedFolder;
use app\classes\grid\account\partner\maintenance\ActingFolder;
use app\classes\grid\account\partner\maintenance\NegotationsFolder;
use app\models\BusinessProcess;
use app\models\ContractSubdivision;
use Yii;


class PartnerMaintenance extends AccountGrid
{
    public function getContractSubdivision()
    {
        return ContractSubdivision::PARTNER;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::PARTNER_MAINTENANCE;
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