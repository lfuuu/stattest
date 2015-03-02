<?php
namespace app\classes\bill;

use Yii;

class WelltimeBiller extends Biller
{
    protected function processPeriodical()
    {
        $tariff = $this->usage->tariff;

        $template = '{name}';

        $template .= $this->getPeriodTemplate($this->tariff->period);

        $this->addPackage(
            BillerPackagePeriodical::create($this, 2001)
                ->setPeriodType($tariff->period)
                ->setIsAlign($tariff->period == self::PERIOD_MONTH)
                ->setIsPartialWriteOff(false)
                ->setAmount($this->usage->amount)
                ->setName($tariff->description)
                ->setPrice($tariff->price)
                ->setTemplate($template)
        );
    }

}