<?php
namespace app\classes\grid\account;


use app\classes\grid\account\itoutsoursing\maintenance\ConnectingFolder;
use app\classes\grid\account\itoutsoursing\maintenance\IncomingFolder;
use app\classes\grid\account\itoutsoursing\maintenance\NegotiationsFolder;
use app\classes\grid\account\itoutsoursing\maintenance\OnServiceFolder;
use app\classes\grid\account\itoutsoursing\maintenance\VerificationFolder;
use app\classes\grid\account\itoutsoursing\maintenance\FailureFolder;
use app\classes\grid\account\itoutsoursing\maintenance\SuspendedFolder;
use app\classes\grid\account\itoutsoursing\maintenance\TrashFolder;
use app\models\BusinessProcess;
use app\models\Business;
use Yii;


class ITOutsoursingMaintenance extends AccountGrid
{
    public function getBusiness()
    {
        return Business::ITOUTSOURSING;
    }

    public function getBusinessProcessId()
    {
        return BusinessProcess::ITOUTSOURSING_MAINTENANCE;
    }

    public function getFolders()
    {
        return [
            IncomingFolder::create($this),
            NegotiationsFolder::create($this),
            VerificationFolder::create($this),
            ConnectingFolder::create($this),
            OnServiceFolder::create($this),
            SuspendedFolder::create($this),
            FailureFolder::create($this),
            TrashFolder::create($this),
        ];
    }

}