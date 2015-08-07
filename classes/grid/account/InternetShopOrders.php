<?php
namespace app\classes\grid\account;

use app\classes\grid\account\internetshop\orders\OrdersFolder;
use app\models\BusinessProcess;
use app\models\ContractType;
use Yii;


class InternetShopOrders extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::INTERNET_SHOP;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::INTERNET_SHOP_ORDERS;
    }

    public function getFolders()
    {
        return [
            OrdersFolder::create($this),
        ];
    }

}