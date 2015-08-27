<?php
namespace app\classes\grid\account;

use app\classes\grid\account\provider\orders\ActingFolder;
use app\classes\grid\account\provider\orders\NegotationStageFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class ProviderOrders extends AccountGrid
{
    public function getBusiness()
    {
        return Business::PROVIDER;
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