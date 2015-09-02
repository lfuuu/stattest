<?php
namespace app\classes\grid\account;

use app\classes\grid\account\operator\infrastructure\ActingFolder;
use app\classes\grid\account\operator\infrastructure\AutoBlockedFolder;
use app\classes\grid\account\operator\infrastructure\BlockedFolder;
use app\classes\grid\account\operator\infrastructure\IncomingFolder;
use app\classes\grid\account\operator\infrastructure\NegotiationsFolder;
use app\classes\grid\account\operator\infrastructure\SuspendedFolder;
use app\classes\grid\account\operator\infrastructure\FailureFolder;
use app\classes\grid\account\operator\infrastructure\TerminatedFolder;
use app\classes\grid\account\operator\infrastructure\TestingFolder;
use app\classes\grid\account\operator\infrastructure\TrashFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class OperatorInfrastructure extends AccountGrid
{
    public function getBusiness()
    {
        return Business::OPERATOR;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::OPERATOR_INFRASTRUCTURE;
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
            AutoBlockedFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}