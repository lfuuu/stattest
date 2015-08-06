<?php
namespace app\classes\grid\account;

use app\classes\grid\account\welltime\maintenance\CommissioningFolder;
use app\classes\grid\account\welltime\maintenance\FailureFolder;
use app\classes\grid\account\welltime\maintenance\MaintenanceFolder;
use app\classes\grid\account\welltime\maintenance\MaintenanceFreeFolder;
use app\classes\grid\account\welltime\maintenance\SuspendedFolder;
use app\classes\grid\account\welltime\maintenance\TrashFolder;
use app\models\ClientGridBussinesProcess;
use app\models\ContractType;
use Yii;


class WelltimeMaintenance extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::WELLTIME;
    }

    public function getBusinessProcessId()
    {
        return ClientGridBussinesProcess::WELLTIME_MAINTENANCE;
    }

    public function getFolders()
    {
        return [
            CommissioningFolder::create($this),
            MaintenanceFolder::create($this),
            MaintenanceFreeFolder::create($this),
            SuspendedFolder::create($this),
            FailureFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}