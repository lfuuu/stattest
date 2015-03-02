<?php
namespace app\classes\bill;

use Yii;

class SmsBiller extends Biller
{
    protected function processPeriodical()
    {
        $tariff = $this->usage->tariff;

        if ($tariff->per_month_price > 0) {
            $template = 'Абонентская плата за СМС рассылки, {name}' . $this->getPeriodTemplate($tariff->period);

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setPeriodType($tariff->period)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setName($tariff->description)
                    ->setTemplate($template)
                    ->setPrice($tariff->per_month_price)
            );
        }
    }

    protected function processResource()
    {
        $tariff = $this->usage->tariff;

        $smsCount = $this->getSmsCount();
        if ($smsCount > 0 && $tariff->per_sms_price > 0)
        {
            $template = 'СМС рассылка, {name}' . $this->getPeriodTemplate($tariff->period);

            $this->addPackage(
                BillerPackageResource::create($this)
                    ->setAmount($smsCount)
                    ->setName($tariff->description)
                    ->setTemplate($template)
                    ->setPrice($tariff->per_sms_price/1.18)
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