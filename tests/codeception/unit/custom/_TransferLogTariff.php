<?php

namespace tests\codeception\unit\custom;

use app\models\usages\UsageInterface;

trait _TransferLogTariff
{

    protected function checkLogTariffAfter(UsageInterface $from, UsageInterface $to)
    {
        $this->assertNotNull($from->getLogTariff(UsageInterface::MIDDLE_DATE), 'See object "toUsage" after transfer');
        $this->assertNotNull($to->getLogTariff(UsageInterface::MIDDLE_DATE), 'See object "toUsage" after transfer');
    }

}