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
        $template  = 'extra_service';
        $template_data = [
            'tariff' => $this->tariff->description,
            'by_agreement' => ''
        ];

        if ($this->tariff->param_name) {
            $template_data['tariff'] = str_replace('%', $this->usage->param_value, $template_data['tariff']);
        }

        if ($this->clientAccount->bill_rename1 == 'yes') {
            $template_data['by_agreement'] .= $this->getContractInfo();
        }

        $this->addPackage(
            BillerPackagePeriodical::create($this)
                ->setPeriodType($this->tariff->period)
                ->setIsAlign($this->tariff->period == self::PERIOD_MONTH)
                ->setIsPartialWriteOff(false)
                ->setAmount($this->usage->amount)
                ->setPrice($this->tariff->price)
                ->setTemplate($template)
                ->setTemplateData($template_data)
        );

    }

}