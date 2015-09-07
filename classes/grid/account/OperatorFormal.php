<?php
namespace app\classes\grid\account;

use app\classes\grid\account\operator\formal\ActingFolder;
use app\classes\grid\account\operator\formal\AutoBlockedFolder;
use app\classes\grid\account\operator\formal\BlockedFolder;
use app\classes\grid\account\operator\formal\IncomingFolder;
use app\classes\grid\account\operator\formal\NegotiationsFolder;
use app\classes\grid\account\operator\formal\SuspendedFolder;
use app\classes\grid\account\operator\formal\FailureFolder;
use app\classes\grid\account\operator\formal\TerminatedFolder;
use app\classes\grid\account\operator\formal\TestingFolder;
use app\classes\grid\account\operator\formal\TrashFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class OperatorFormal extends AccountGrid
{
    public function getBusiness()
    {
        return Business::OPERATOR;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::OPERATOR_FORMAL;
    }

    public function getFolders()
    {
        return [
            IncomingFolder::create($this),
            NegotiationsFolder::create($this),
            TestingFolder::create($this),
            ActingFolder::create($this),
            SuspendedFolder::create($this),
            TerminatedFolder::create($this),
            BlockedFolder::create($this),
            FailureFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}
