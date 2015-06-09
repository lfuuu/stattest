<?php
namespace app\classes\bill;

use Yii;

class SmsBiller extends Biller
{

    protected function processPeriodical()
    {
        $tariff = $this->usage->tariff;

        if ($tariff->per_month_price > 0) {
            $template = 'sms_service';
            $template_data = [
                'tariff' => $tariff->description
            ];

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setPeriodType($tariff->period)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setPrice($tariff->per_month_price)
                    ->setTemplate($template)
                    ->setTemplateData($template_data)
            );
        }
    }

    protected function processResource()
    {
        $tariff = $this->usage->tariff;

        $smsCount = $this->getSmsCount();
        if ($smsCount > 0 && $tariff->per_sms_price > 0)
        {
            $template = 'sms_service';
            $template_data = [
                'tariff' => $tariff->description
            ];

            $this->addPackage(
                BillerPackageResource::create($this)
                    ->setPeriodType($tariff->period)
                    ->setAmount($smsCount)
                    ->setPrice($tariff->per_sms_price/1.18)
                    ->setTemplate($template)
                    ->setTemplateData($template_data)
            );

        }
    }

    private function getSmsCount()
    {
        return
            Yii::$app->db->createCommand('
                SELECT sum(`count`)
                FROM `sms_stat`
                where sender = :clientAccountId
                      and date_hour between :dateFromPrev and :dateFromTo
                ',
                [
                    ':clientAccountId' => $this->clientAccount->id,
                    ':dateFromPrev' => $this->billerActualFrom->format('Y-m-d'),
                    ':dateFromTo' => $this->billerActualTo->format('Y-m-d')
                ]
            )->queryScalar();
    }

}