<?php
namespace app\classes\bill;

use Yii;

class WelltimeBiller extends Biller
{
    protected function processPeriodical()
    {
        $tariff = $this->usage->tariff;

        $template = 'welltime_service';
        $template_data = [
            'tariff' => $tariff->description
        ];

        $this->addPackage(
            BillerPackagePeriodical::create($this, 2001)
                ->setPeriodType($tariff->period)
                ->setIsAlign($tariff->period == self::PERIOD_MONTH)
                ->setIsPartialWriteOff(false)
                ->setAmount($this->usage->amount)
                ->setPrice($tariff->price)
                ->setTemplate($template)
                ->setTemplateData($template_data)
        );
    }

}