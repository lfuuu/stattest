<?php
namespace app\classes\grid\account;

use app\classes\grid\account\provider\orders\ActingFolder;
use app\classes\grid\account\provider\orders\NegotationStageFolder;
use app\models\BusinessProcess;
use app\models\ContractSubdivision;
use Yii;


class ProviderOrders extends AccountGrid
{
    public function getContractSubdivision()
    {
        return ContractSubdivision::PROVIDER;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::PROVIDER_ORDERS;
    }

    public function getFolders()
    {
        return [
            ActingFolder::create($this),
            NegotationStageFolder::create($this),
        ];
    }

}