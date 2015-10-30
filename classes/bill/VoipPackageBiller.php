<?php
namespace app\classes\bill;

use Yii;
use app\models\TariffVoipPackage;

class VoipPackageBiller extends Biller
{

    /** @var TariffVoipPackage */
    private $tariff = false;

    public function getTranslateFilename()
    {
        return 'biller-voip';
    }

    protected function processPeriodical()
    {
        $package = BillerPackagePeriodical::create($this)
            ->setPrice($this->usage->tariff->periodical_fee)
            //->setMinPaymentTemplate($this->usage->tariff->min_payment)
            //->setMinPaymentTemplate()
            ->setIsAlign(true)
            ->setIsPartialWriteOff(false)
            ->setPeriodType(self::PERIOD_MONTH)
            ->setTemplate('voip_package_fee')
            ->setTemplateData([
                'tariff' => $this->usage->tariff->name,
                'service' => $this->usage->usageVoip->E164,
            ]);
        $this->addPackage($package);
    }

    /*
    protected function processResource()
    {
        $this->addPackage(
            BillerPackageResource::create($this)
                ->setPrice($this->usage->tariff->periodical_fee)
                ->setMinPayment($this->usage->tariff->min_payment)
                ->setMinPaymentTemplate('voip_package_minpay')
                ->setPeriodType(self::PERIOD_MONTH)// Need for localization
                ->setTemplate('voip_package_payment')
                ->setTemplateData([
                    'tariff' => $this->usage->tariff->name,
                    'service' => $this->usage->usageVoip->E164,
                ])
        );
    }
     */
}
