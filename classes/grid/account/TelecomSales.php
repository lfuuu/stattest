<?php
namespace app\classes\grid\account;

use app\classes\grid\account\telecom\reports\IncomeDifferentFolder;
use app\classes\grid\account\telecom\reports\IncomeFromCustomersFolder;
use app\classes\grid\account\telecom\reports\IncomeFromManagersAndUsagesFolder;
use app\classes\grid\account\telecom\reports\IncomeFromManagersFolder;
use app\classes\grid\account\telecom\reports\IncomeFromUsagesFolder;
use app\classes\grid\account\telecom\sales\ConnectingFolder;
use app\classes\grid\account\telecom\sales\FailureFolder;
use app\classes\grid\account\telecom\sales\IncomingFolder;
use app\classes\grid\account\telecom\sales\NegotationsFolder;
use app\classes\grid\account\telecom\sales\TechFailureFolder;
use app\classes\grid\account\telecom\sales\TestingFolder;
use app\classes\grid\account\telecom\sales\TrashFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class TelecomSales extends AccountGrid
{
    public function getBusiness()
    {
        return Business::TELEKOM;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::TELECOM_SALES;
    }

    public function getFolders()
    {
        return [
            IncomingFolder::create($this),
            NegotationsFolder::create($this),
            TestingFolder::create($this),
            ConnectingFolder::create($this),
            TechFailureFolder::create($this),
            FailureFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}