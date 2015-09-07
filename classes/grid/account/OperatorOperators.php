<?php
namespace app\classes\grid\account;

use app\classes\grid\account\operator\operators\ActingFolder;
use app\classes\grid\account\operator\operators\AutoBlockedFolder;
use app\classes\grid\account\operator\operators\BlockedFolder;
use app\classes\grid\account\operator\operators\IncomingFolder;
use app\classes\grid\account\operator\operators\ManualBillFolder;
use app\classes\grid\account\operator\operators\NegotiationsFolder;
use app\classes\grid\account\operator\operators\SuspendedFolder;
use app\classes\grid\account\operator\operators\FailureFolder;
use app\classes\grid\account\operator\operators\TerminatedFolder;
use app\classes\grid\account\operator\operators\TestingFolder;
use app\classes\grid\account\operator\operators\TrashFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class OperatorOperators extends AccountGrid
{
    public function getBusiness()
    {
        return Business::OPERATOR;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::OPERATOR_OPERATORS;
    }

    public function getFolders()
    {
        return [
            IncomingFolder::create($this),
            NegotiationsFolder::create($this),
            TestingFolder::create($this),
            ActingFolder::create($this),
            ManualBillFolder::create($this),
            SuspendedFolder::create($this),
            TerminatedFolder::create($this),
            BlockedFolder::create($this),
            FailureFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}
