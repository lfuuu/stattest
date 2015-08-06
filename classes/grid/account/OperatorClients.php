<?php
namespace app\classes\grid\account;

use app\classes\grid\account\operator\clients\JiraSoftFolder;
use app\classes\grid\account\operator\clients\ActingFolder;
use app\classes\grid\account\operator\clients\AutoBlockedFolder;
use app\classes\grid\account\operator\clients\BlockedFolder;
use app\classes\grid\account\operator\clients\IncomingFolder;
use app\classes\grid\account\operator\clients\NegotiationsFolder;
use app\classes\grid\account\operator\clients\SuspendedFolder;
use app\classes\grid\account\operator\clients\TechFailureFolder;
use app\classes\grid\account\operator\clients\TerminatedFolder;
use app\classes\grid\account\operator\clients\TestingFolder;
use app\classes\grid\account\operator\clients\TrashFolder;
use app\models\ClientGridBussinesProcess;
use app\models\ContractType;
use Yii;


class OperatorClients extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::OPERATOR;
    }

    public function getBusinessProcessId()
    {
        return ClientGridBussinesProcess::OPERATOR_CLIENTS;
    }

    public function getFolders()
    {
        return [
            IncomingFolder::create($this),
            NegotiationsFolder::create($this),
            TestingFolder::create($this),
            ActingFolder::create($this),
            JiraSoftFolder::create($this),
            SuspendedFolder::create($this),
            TerminatedFolder::create($this),
            BlockedFolder::create($this),
            TechFailureFolder::create($this),
            AutoBlockedFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}