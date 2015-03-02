<?php
namespace app\classes\bill;

use Yii;

class ExtraBiller extends Biller
{
    private $tariff;

    protected function beforeProcess()
    {
        if ($this->usage->code == 'welltime_backup' || $this->usage->code == 'welltime_backup_no_c') {
            return false;
        }

        $this->tariff = $this->usage->tariff;
        if ($this->tariff === null) {
            return false;
        }
    }

    protected function processPeriodical()
    {
        $template = '{name}';

        if ($this->tariff->param_name) {
            $template = str_replace('%', $this->usage->param_value, $template);
        }

        $template .= $this->getPeriodTemplate($this->tariff->period);

        if ($this->clientAccount->bill_rename1 == 'yes') {
            $template .= $this->getContractInfo();
        }

        $this->addPackage(
            BillerPackagePeriodical::create($this)
                ->setPeriodType($this->tariff->period)
                ->setIsAlign($this->tariff->period == self::PERIOD_MONTH)
                ->setIsPartialWriteOff(false)
                ->setAmount($this->usage->amount)
                ->setName($this->tariff->description)
                ->setTemplate($template)
                ->setPrice($this->tariff->price)
        );

    }

}