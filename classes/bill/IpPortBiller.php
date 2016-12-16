<?php
namespace app\classes\bill;

use app\helpers\DateTimeZoneHelper;
use app\models\flows\TraffFlow1d;
use app\models\LogTarif;
use app\models\TariffInternet;
use app\models\UsageTechCpe;
use app\models\UsageIpRoutes;
use Yii;
use yii\db\Expression;
use yii\db\Query;

class IpPortBiller extends Biller
{
    /** @var TariffInternet */
    private $tariff;

    public function getTranslateFilename()
    {
        return 'biller-ipports';
    }

    protected function beforeProcess()
    {
        $this->tariff = $this->getTariff();
        if ($this->tariff === null) {
            return false;
        }
    }

    protected function processConnecting()
    {
        $template = 'ipports_connection';
        $template_data = [
            'tariff' => $this->tariff->name
        ];

        $this->addPackage(
            BillerPackageConnecting::create($this)
                ->setPrice($this->tariff->pay_once)
                ->setTemplate($template)
                ->setTemplateData($template_data)
        );
    }

    protected function processPeriodical()
    {
        $template =
            $this->tariff->type == "C"
                ? 'ipports_service'
                : 'ipports_monthly_fee';
        $template_data = [
            'tariff' => $this->tariff->name,
            'service_id' => $this->usage->id,
            'by_agreement' => ''
        ];

        if ($this->clientAccount->bill_rename1 == 'yes') {
            $template = 'ipports_monthly_fee_custom';
            $template_data['by_agreement'] = $this->getContractInfo();
        }

        $this->addPackage(
            BillerPackagePeriodical::create($this)
                ->setPeriodType(self::PERIOD_MONTH)
                ->setIsAlign(true)
                ->setIsPartialWriteOff(false)
                ->setAmount($this->usage->amount)
                ->setPrice($this->tariff->pay_month)
                ->setTemplate($template)
                ->setTemplateData($template_data)
        );
    }

    protected function processResource()
    {
        $template = 'ipports_overlimit';
        $template_data = [
            'service_id' => $this->usage->id,
            'tariff' => $this->tariff->name
        ];

        if ($this->tariff->type == 'I' || $this->tariff->type == 'C') {
            $S = $this->calcIC();

            $template_data['overlimit'] = Yii::t(
                $this->getTranslateFilename(),
                ($S['in_bytes'] > $S['out_bytes'] ? 'ipports_in_traffic' : 'ipports_out_traffic'),
                [],
                $this->clientAccount->contragent->country->lang
            );

            $this->addPackage(
                BillerPackageResource::create($this)
                    ->setPeriodType(self::PERIOD_MONTH)// Need for localization
                    ->setFreeAmount($this->tariff->mb_month)
                    ->setAmount(max($S['in_bytes'], $S['out_bytes']))
                    ->setPrice($this->tariff->pay_mb)
                    ->setTemplate($template)
                    ->setTemplateData($template_data)
            );
        } elseif ($this->tariff->type == 'V') {
            //Nothing. Not need.
        }
    }

    private function calcIC()
    {
        $routes =
            UsageIpRoutes::find()
                ->andWhere(['port_id' => $this->usage->id])
                ->asArray()
                ->all();

        $validedRouters = [];
        foreach ($routes as $r) {
            list($ip, $sum) = $this->netmask_to_ip_sum($r['net']);
            if ($sum && $ip) {
                $validedRouters[] = $r;
            }
        }

        if (!$validedRouters) {
            return [
                'in_bytes' => 0,
                'out_bytes' => 0
            ];
        }

        $stat = TraffFlow1d::getStatistic($this->billerActualFrom, $this->billerActualTo, TraffFlow1d::STAT_TOTAL_ONLY, $validedRouters);

        //в мегабайтах
        $stat['in_bytes'] /= 1024*1024;
        $stat['out_bytes'] /= 1024*1024;

        return $stat;
    }

    /**
     * @return TariffInternet
     */
    private function getTariff()
    {
        $logTariff =
            LogTarif::find()
                ->andWhere(['service' => 'usage_ip_ports', 'id_service' => $this->usage->id])
                ->andWhere('date_activation <= :from', [':from' => $this->billerActualFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                ->andWhere('id_tarif != 0')
                ->orderBy('date_activation desc, id desc')
                ->one();

        if (!$logTariff) {
            return null;
        }

        // если основной тариф - тестовый, то мы ищем первый не тестовый тариф
        if ($logTariff->internetTariff->isTest()) {
            $logTariff =
                LogTarif::find()
                    ->andWhere(['service' => 'usage_ip_ports', 'id_service' => $this->usage->id])
                    ->andWhere('date_activation > :from', [':from' => $this->billerActualFrom->format(DateTimeZoneHelper::DATE_FORMAT)])
                    ->andWhere('id_tarif != 0')
                    ->leftJoin(['tv' => TariffInternet::tableName()], 'tv.id = id_tarif')
                    ->andWhere(['not', ['tv.status' => TariffInternet::STATUS_TEST]])
                    ->orderBy('date_activation desc, id desc')
                    ->one();

            // тариф не найден ИЛИ дата активаии тарифа не входит в текущий период выставления счета
            if ($logTariff === null || $logTariff->date_activation >= $this->billerActualTo->format(DateTimeZoneHelper::DATE_FORMAT)) {
                return false;
            }

            $this->billerPeriodFrom = $this->usageActualFrom = $this->billerActualFrom =
                new \DateTime($logTariff->date_activation, clone $this->usageActualFrom->getTimezone());
        }

        return $logTariff->internetTariff;
    }

    private function netmask_to_ip_sum($mask)
    {
        if (!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/", $mask, $m)) {
            return;
        }
        $sum = 1;
        if (isset($m[6]) && $m[6]) {
            for ($i = $m[6]; $i < 32; $i++) {
                $sum *= 2;
            }
        }
        return array(256 * (256 * (256 * $m[1] + $m[2]) + $m[3]) + $m[4], $sum);
    }
}
