<?php
namespace app\classes\bill;

use Yii;

class WelltimeBiller extends Biller
{
    protected function processPeriodical()
    {
        $tariff = $this->usage->tariff;

        $this->addPackage(
            BillerPackagePeriodical::create($this, 2001)
                ->setPeriodType($tariff->period)
                ->setIsAlign($tariff->period == self::PERIOD_MONTH)
                ->setIsPartialWriteOff(false)
                ->setAmount($this->usage->amount)
                ->setName($tariff->description)
                ->setPrice($tariff->price)
        );
    }

}