<?php
namespace app\classes\grid\account;

use app\classes\grid\account\operator\formal\ActingFolder;
use app\classes\grid\account\operator\formal\AutoBlockedFolder;
use app\classes\grid\account\operator\formal\BlockedFolder;
use app\classes\grid\account\operator\formal\IncomingFolder;
use app\classes\grid\account\operator\formal\NegotiationsFolder;
use app\classes\grid\account\operator\formal\SuspendedFolder;
use app\classes\grid\account\operator\formal\TechFailureFolder;
use app\classes\grid\account\operator\formal\TerminatedFolder;
use app\classes\grid\account\operator\formal\TestingFolder;
use app\classes\grid\account\operator\formal\TrashFolder;
use app\models\ClientGridBussinesProcess;
use app\models\ContractType;
use Yii;


class OperatorFormal extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::OPERATOR;
    }

    public function getBusinessProcessId()
    {
        return ClientGridBussinesProcess::OPERATOR_FORMAL;
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
            TechFailureFolder::create($this),
            AutoBlockedFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}