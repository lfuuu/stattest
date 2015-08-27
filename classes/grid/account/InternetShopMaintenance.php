<?php
namespace app\classes\grid\account;

use app\classes\grid\account\internetshop\maintenance\ActingFolder;
use app\classes\grid\account\internetshop\maintenance\TrashFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class InternetShopMaintenance extends AccountGrid
{
    public function getBusiness()
    {
        return Business::INTERNET_SHOP;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::INTERNET_SHOP_MAINTENANCE;
    }

    public function getFolders()
    {
        return [
            ActingFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}