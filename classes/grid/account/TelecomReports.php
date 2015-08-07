<?php
namespace app\classes\grid\account;

use app\classes\grid\account\telecom\reports\IncomeDifferentFolder;
use app\classes\grid\account\telecom\reports\IncomeFromCustomersFolder;
use app\classes\grid\account\telecom\reports\IncomeFromManagersAndUsagesFolder;
use app\classes\grid\account\telecom\reports\IncomeFromManagersFolder;
use app\classes\grid\account\telecom\reports\IncomeFromUsagesFolder;
use app\models\BusinessProcess;
use app\models\ContractType;
use Yii;


class TelecomReports extends AccountGrid
{
    public function getContractType()
    {
        return ContractType::TELEKOM;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::TELECOM_REPORTS;
    }

    public function getFolders()
    {
        return [
            IncomeFromCustomersFolder::create($this),
            IncomeFromManagersAndUsagesFolder::create($this),
            IncomeFromManagersFolder::create($this),
            IncomeFromUsagesFolder::create($this),
            IncomeDifferentFolder::create($this),
        ];
    }

}