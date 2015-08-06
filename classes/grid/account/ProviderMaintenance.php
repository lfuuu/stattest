<?php
namespace app\classes\grid\account;

use app\classes\grid\account\provider\maintenance\ActingFolder;
use app\classes\grid\account\provider\maintenance\ClosedFolder;
use app\classes\grid\account\provider\maintenance\GPONFolder;
use app\classes\grid\account\provider\maintenance\OnceFolder;
use app\classes\grid\account\provider\maintenance\SelfBuyFolder;
use app\classes\grid\account\provider\maintenance\ServiceFolder;
use app\classes\grid\account\provider\maintenance\VOLSFolder;
use app\models\ClientGridBussinesProcess;
use app\models\ContractType;
use Yii;


class ProviderMaintenance extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::PROVIDER;
    }

    public function getBusinessProcessId()
    {
        return ClientGridBussinesProcess::PROVIDER_MAINTENANCE;
    }

    public function getFolders()
    {
        return [
            GPONFolder::create($this),
            VOLSFolder::create($this),
            ServiceFolder::create($this),
            ActingFolder::create($this),
            ClosedFolder::create($this),
            SelfBuyFolder::create($this),
            OnceFolder::create($this),
        ];
    }

}