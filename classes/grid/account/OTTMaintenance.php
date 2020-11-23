<?php
namespace app\classes\grid\account;

use app\classes\grid\account\ott\maintenance\AutoBlockCreditFolder;
use app\classes\grid\account\ott\maintenance\AutoBlockDayLimitFolder;
use app\classes\grid\account\ott\maintenance\AutoBlock800Folder;
use app\classes\grid\account\ott\maintenance\ConnectingFolder;
use app\classes\grid\account\ott\maintenance\DisconnectedDebtFolder;
use app\classes\grid\account\ott\maintenance\DisconnectedFolder;
use app\classes\grid\account\ott\maintenance\DuplicateFolder;
use app\classes\grid\account\ott\maintenance\FailureFolder;
use app\classes\grid\account\ott\maintenance\OrderServiceFolder;
use app\classes\grid\account\ott\maintenance\TechFailureFolder;
use app\classes\grid\account\ott\maintenance\TrashFolder;
use app\classes\grid\account\ott\maintenance\WorkFolder;
use app\models\BusinessProcess;
use app\models\Business;


class OTTMaintenance extends AccountGrid
{
    use GenericFolderTrait;

    /**
     * @return int
     */
    public function getBusiness()
    {
        return Business::OTT;
    }

    /**
     * @return int
     */
    public function getBusinessProcessId()
    {
        return BusinessProcess::OTT_MAINTENANCE;
    }

    /**
     * @return array
     */
    /*
    public function getFolders()
    {
        return [
            OrderServiceFolder::create($this),
            ConnectingFolder::create($this),
            WorkFolder::create($this),
            DisconnectedFolder::create($this),

            DisconnectedDebtFolder::create($this),
            AutoBlockCreditFolder::create($this),
            AutoBlockDayLimitFolder::create($this),
            AutoBlock800Folder::create($this),

            TrashFolder::create($this),
            TechFailureFolder::create($this),
            FailureFolder::create($this),
            DuplicateFolder::create($this),
        ];
    }
    */
    public function getColumns()
    {
        return [
            'status',
            'id',
            'company',
            'created',
            'currency',
            'sale_channel',
            'manager',
            'account_manager',
            'region',
            'legal_entity',
        ];
    }

}