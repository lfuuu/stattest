<?php

namespace tests\codeception\unit\custom;

use app\models\usages\UsageInterface;

trait _TransferLogTariff
{

    protected function checkLogTariffAfter(UsageInterface $from, UsageInterface $to)
    {
        $this->assertNotNull($from->logTariff, 'See object "toUsage" after transfer');
        $this->assertNotNull($to->logTariff, 'See object "toUsage" after transfer');
    }

}