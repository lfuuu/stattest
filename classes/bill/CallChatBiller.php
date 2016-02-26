<?php
namespace app\classes\bill;

use Yii;

class CallChatBiller extends Biller
{
    private $tariff;

    protected function beforeProcess()
    {
        $this->tariff = $this->usage->tariff;
        if ($this->tariff === null) {
            return false;
        }
    }

    protected function processPeriodical()
    {
        $template  = 'call_chat_service';
        $template_data = [
            'tariff' => $this->tariff->description,
        ];

        $this->addPackage(
            BillerPackagePeriodical::create($this)
                ->setPeriodType(self::PERIOD_MONTH)
                ->setIsAlign(true)
                ->setIsPartialWriteOff(false)
                ->setPrice($this->tariff->price)
                ->setTemplate($template)
                ->setTemplateData($template_data)
        );

    }

}