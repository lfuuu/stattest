<?php
namespace app\classes\grid\account;

use app\classes\grid\account\internetshop\maintenance\ActingFolder;
use app\classes\grid\account\internetshop\maintenance\TrashFolder;
use app\models\ClientGridBussinesProcess;
use app\models\ContractType;
use Yii;


class InternetShopMaintenance extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::INTERNET_SHOP;
    }

    public function getBusinessProcessId()
    {
        return ClientGridBussinesProcess::INTERNET_SHOP_MAINTENANCE;
    }

    public function getFolders()
    {
        return [
            ActingFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}