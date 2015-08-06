<?php
namespace app\classes\grid\account;

use app\classes\grid\account\provider\orders\ActingFolder;
use app\classes\grid\account\provider\orders\NegotationStageFolder;
use app\models\ClientGridBussinesProcess;
use app\models\ContractType;
use Yii;


class ProviderOrders extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::PROVIDER;
    }

    public function getBusinessProcessId()
    {
        return ClientGridBussinesProcess::PROVIDER_ORDERS;
    }

    public function getFolders()
    {
        return [
            ActingFolder::create($this),
            NegotationStageFolder::create($this),
        ];
    }

}