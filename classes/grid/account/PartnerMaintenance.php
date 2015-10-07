<?php
namespace app\classes\grid\account;

use app\classes\grid\account\partner\maintenance\NegotiationsFolder;
use app\classes\grid\account\partner\maintenance\ActingFolder;
use app\classes\grid\account\partner\maintenance\ManualBillFolder;
use app\classes\grid\account\partner\maintenance\SuspendedFolder;
use app\classes\grid\account\partner\maintenance\TerminatedFolder;
use app\classes\grid\account\partner\maintenance\FailureFolder;
use app\classes\grid\account\partner\maintenance\TrashFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class PartnerMaintenance extends AccountGrid
{
    public function getBusiness()
    {
        return Business::PARTNER;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::PARTNER_MAINTENANCE;
    }

    public function getFolders()
    {
        return [
            NegotiationsFolder::create($this),
            ActingFolder::create($this),
            ManualBillFolder::create($this),
            SuspendedFolder::create($this),
            TerminatedFolder::create($this),
            FailureFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}