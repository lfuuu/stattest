<?php
namespace app\classes\bill;

use Yii;

class ExtraBiller extends Biller
{
    public function process()
    {
        $tariff = $this->usage->tariff;
        if ($tariff === null) {
            return $this;
        }

        $template = '{name}';

        if ($tariff->param_name) {
            $template = str_replace('%', $this->usage->param_value, $template);
        }

        $template .= $this->getPeriodTemplate($tariff->period);

        if ($this->clientAccount->bill_rename1 == 'yes') {
            $template .= $this->getContractInfo();
        }

        $this->addPackage(
            BillerPackagePeriodical::create($this)
                ->setPeriodType($tariff->period)
                ->setIsAlign($tariff->period == self::PERIOD_MONTH)
                ->setIsPartialWriteOff(false)
                ->setAmount($this->usage->amount)
                ->setName($tariff->description)
                ->setTemplate($template)
                ->setPrice($tariff->price)
        );

        return $this;
    }

}