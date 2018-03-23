<?php
namespace app\classes\grid\account;

use app\classes\grid\account\telecom\maintenance\AutoBlockFolder;
use app\classes\grid\account\telecom\maintenance\AutoBlockCreditFolder;
use app\classes\grid\account\telecom\maintenance\AutoBlockDayLimitFolder;
use app\classes\grid\account\telecom\maintenance\AutoBlock800Folder;
use app\classes\grid\account\telecom\maintenance\BlockBillPayOverdueFolder;
use app\classes\grid\account\telecom\maintenance\ConnectingFolder;
use app\classes\grid\account\telecom\maintenance\DisconnectedDebtFolder;
use app\classes\grid\account\telecom\maintenance\DisconnectedFolder;
use app\classes\grid\account\telecom\maintenance\DuplicateFolder;
use app\classes\grid\account\telecom\maintenance\FailureFolder;
use app\classes\grid\account\telecom\maintenance\OrderServiceFolder;
use app\classes\grid\account\telecom\maintenance\TechFailureFolder;
use app\classes\grid\account\telecom\maintenance\TrashFolder;
use app\classes\grid\account\telecom\maintenance\UnlinkFolder;
use app\classes\grid\account\telecom\maintenance\WlInnonetFolder;
use app\classes\grid\account\telecom\maintenance\WorkFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class TelecomMaintenance extends AccountGrid
{
    public function getBusiness()
    {
        return Business::TELEKOM;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::TELECOM_MAINTENANCE;
    }

    public function getFolders()
    {
        return [
            OrderServiceFolder::create($this),
            ConnectingFolder::create($this),
            WorkFolder::create($this),
            DisconnectedFolder::create($this),
            DisconnectedDebtFolder::create($this),
            //AutoBlockFolder::create($this),
            AutoBlockCreditFolder::create($this),
            AutoBlockDayLimitFolder::create($this),
            BlockBillPayOverdueFolder::create($this),
            AutoBlock800Folder::create($this),
            TrashFolder::create($this),
            TechFailureFolder::create($this),
            FailureFolder::create($this),
            DuplicateFolder::create($this),
            WlInnonetFolder::create($this),
        ];
    }

}