<?php
namespace app\classes\bill;

use app\classes\Assert;
use app\models\LogTarif;
use app\models\TariffVoip;
use Yii;

class VoipBiller extends Biller
{
    /** @var LogTarif */
    private $logTariff = false;
    /** @var TariffVoip */
    private $tariff = false;

    public function getTranslateFilename()
    {
        return 'biller-voip';
    }

    public function beforeProcess()
    {
        $this->logTariff =
            LogTarif::find()
                ->andWhere(['service' => 'usage_voip', 'id_service' => $this->usage->id])
                ->andWhere('date_activation <= :from', [':from' => $this->billerActualFrom->format('Y-m-d')])
                ->andWhere('id_tarif != 0')
                ->orderBy('date_activation desc, id desc')
                ->limit(1)
                ->one();
        if ($this->logTariff === null) {
            return false;
        }

        $this->tariff = TariffVoip::findOne($this->logTariff->id_tarif);
        Assert::isObject($this->tariff);
    }

    protected function processConnecting()
    {
        $price = ($this->usage->no_of_lines - 1) * $this->tariff->once_line;
        if ($price < 0) {
            $price = 0;
        }
        $price += $this->tariff->once_number;

        $template = 'voip_connection';
        $template_data = [
            'tariff' => $this->tariff->name
        ];

        $this->addPackage(
            BillerPackageConnecting::create($this)
                ->setPrice($price)
                ->setTemplate($template)
                ->setTemplateData($template_data)
        );
    }

    protected function processPeriodical()
    {
        if ($this->tariff->month_number > 0) {
            $template = 'voip_monthly_fee_per_number';
            $template_data = [
                'service' => $this->usage->E164,
                'by_agreement' => ''
            ];

            if ($this->clientAccount->bill_rename1 == 'yes') {
                $template = 'voip_monthly_fee_per_number_custom';
                $template_data['by_agreement'] = $this->getContractInfo();
            }

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setPeriodType($this->tariff->period)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setPrice($this->tariff->month_number)
                    ->setTemplate($template)
                    ->setTemplateData($template_data)
            );
        }

        if ($this->usage->no_of_lines > 1 && $this->tariff->month_line > 0) {
            $count = $this->usage->no_of_lines - 1;

            $template = 'voip_monthly_fee_per_line';
            $template_data = [
                'lines_number' => $count,
                'plural_first' => $this->rus_fin($count, 'ую', 'ые', 'ых'),
                'plural_second' => $this->rus_fin($count, 'ию', 'ии', 'ий'),
                'service' => $this->usage->E164
            ];

            if ($this->clientAccount->bill_rename1 == 'yes') {
                $template = 'voip_monthly_fee_per_line_custom';
                $template_data['by_agreement'] = $this->getContractInfo();
            }

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setPeriodType($this->tariff->period)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setAmount($count)
                    ->setPrice($this->tariff->month_line)
                    ->setTemplate($template)
                    ->setTemplateData($template_data)
            );
        }
    }

    protected function processResource()
    {
        $is7800 = substr($this->usage->E164, 0, 4) == "7800";

        $lines = $this->calc($is7800, $this->logTariff);

        foreach ($lines as $dest => $r) {

            $template = null;
            $minPayment = null;
            $minPaymentTemplate = null;

            if ($dest == '4'){
                $template = 'voip_overlimit';
            }elseif($dest == '5'){
                $template = 'voip_local_mobile_call_payment';
                $minPayment = $this->logTariff->minpayment_local_mob;
                $minPaymentTemplate = 'voip_local_mobile_call_minpay';
            }elseif($dest == '1'){
                $template = 'voip_long_distance_call_payment';
                $minPayment = $this->logTariff->minpayment_russia;
                $minPaymentTemplate = 'voip_long_distance_call_minpay';
            }elseif($dest == '2'){
                $template = 'voip_international_call_payment';
                $minPayment = $this->logTariff->minpayment_intern;
                $minPaymentTemplate = 'voip_international_call_minpay';
            }elseif($dest == '3'){
                $template = 'voip_sng_call_payment';
                $minPayment = $this->logTariff->minpayment_sng;
                $minPaymentTemplate = 'voip_sng_call_minpay';
            }elseif($dest == '100'){
                $group = array();
                if (strpos($this->logTariff->dest_group, '5') !== FALSE)
                    $group[] = Yii::t(
                        $this->getTranslateFilename(),
                        'voip_group_local',
                        [],
                        $this->clientAccount->contragent->country->lang
                    );
                if (strpos($this->logTariff->dest_group, '1') !== FALSE)
                    $group[] = Yii::t(
                        $this->getTranslateFilename(),
                        'voip_group_long_distance',
                        [],
                        $this->clientAccount->contragent->country->lang
                    );
                if (strpos($this->logTariff->dest_group, '2') !== FALSE)
                    $group[] = Yii::t(
                        $this->getTranslateFilename(),
                        'voip_group_international',
                        [],
                        $this->clientAccount->contragent->country->lang
                    );
                if (strpos($this->logTariff->dest_group, '3') !== FALSE)
                    $group[] = Yii::t(
                        $this->getTranslateFilename(),
                        'voip_group_sng',
                        [],
                        $this->clientAccount->contragent->country->lang
                    );
                $template_data['group'] = implode(', ', $group);

                $template = 'voip_group_payment';
                $minPayment = $this->logTariff->minpayment_group;
                $minPaymentTemplate = 'voip_group_minpay';
            }elseif($dest == '900'){
                if ($is7800) {
                    $template = 'voip_calls_payment';
                    $minPayment = $this->tariff->month_min_payment;
                    $minPaymentTemplate = 'voip_calls_minpay';
                } else {
                    $template = 'voip_group_calls_payment';
                }

            }

            $template_data = [
                'service' => $this->usage->E164,
                'by_agreement' => ''
            ];

            if ($this->clientAccount->bill_rename1 == 'yes') {
                $template_data['by_agreement'] = $this->getContractInfo();
                if ($template == 'voip_calls_payment' || $template == 'voip_group_calls_payment') {
                    $template .= '_custom';
                }
            }

            $this->addPackage(
                $package =
                    BillerPackageResource::create($this)
                        ->setPrice($r['price'])
                        ->setMinPayment($minPayment)
                        ->setMinPaymentTemplate($minPaymentTemplate)
                        ->setPeriodType(self::PERIOD_MONTH) // Need for localization
                        ->setTemplate($template)
                        ->setTemplateData($template_data)
            );
        }
    }

    private function calc($is7800, LogTarif $logTarif)
    {
        $from = clone $this->billerActualFrom;
        $from->setTimezone(new \DateTimeZone('UTC'));
        $to = clone $this->billerActualTo;
        $to->setTimezone(new \DateTimeZone('UTC'));

        $command =
            Yii::$app->get('dbPg')
                ->createCommand("
                        select
                            case destination_id <= 0 when true then
                                case mob when true then 5 else 4 end
                            else destination_id end rdest,
                            cast( - sum(cost) as NUMERIC(10,2)) as price
                        from
                            calls_raw.calls_raw
                        where
                            number_service_id = {$this->usage->id}
                            and connect_time >= '" . $from->format('Y-m-d H:i:s') . "'
                            and connect_time <= '" . $to->format('Y-m-d H:i:s') . "'
                            and abs(cost) > 0.00001
                        group by rdest
                        having abs(cast( - sum(cost) as NUMERIC(10,2))) > 0
                    "
                );

        $res = $command->queryAll();

        $groups = $logTarif->dest_group;

        /*
           [dest_group] => 0
           [minpayment_group] => 0      // 100

           [minpayment_local_mob] => 0  // 5
           [minpayment_russia] => 1500  // 1
           [minpayment_intern] => 0     // 2
           [minpayment_sng] => 0        // 3

           // other 900
         */

        //default value
        $lines = array();
        if ($logTarif->dest_group > 0) {
            $lines["100"] = array('price' => 0);
        } else {
            if ($logTarif->minpayment_local_mob) {
                $lines["5"] = array('price' => 0);
            }
            if ($logTarif->minpayment_russia) {
                $lines["1"] = array('price' => 0);
            }
            if ($logTarif->minpayment_intern) {
                $lines["2"] = array('price' => 0);
            }
            if ($logTarif->minpayment_sng) {
                $lines["3"] = array('price' => 0);
            }
        }

        foreach ($res as $r) {
            $dest = $r['rdest'];
            if (strpos($groups, $dest) !== FALSE) {
                $dest = '100';
            }
            if ((int)$logTarif->minpayment_group +
                (int)$logTarif->minpayment_local_mob +
                (int)$logTarif->minpayment_russia +
                (int)$logTarif->minpayment_intern +
                (int)$logTarif->minpayment_sng  == 0
            ) {
                $dest = '900';
            }

            if (!isset($lines[$dest])) {
                $lines[$dest] = array('price'=>0);
            }
            $lines[$dest]['price'] += $r['price'];
        }

        if ($is7800 && !$lines) {
            $lines["900"] = array("price" => 0);
        }

        uksort($lines, '\app\classes\bill\cmp_calc_voip_by_dest');

        return $lines;
    }

    private function rus_fin($v,$s1,$s2,$s3)
    {
        if ($v == 11)
            return $s3;
        if (($v % 10) == 1)
            return $s1;
        if (($v % 100) >= 11 && ($v % 100) <= 14)
            return $s3;
        if (($v % 10) >= 2 && ($v % 10) <= 4)
            return $s2;
        return $s3;
    }

}

function cmp_calc_voip_by_dest($a, $b)
{
    $a = ($a < 4 ? $a + 10 : $a);
    $b = ($b < 4 ? $b + 10 : $b);
    if ($a == $b) return 0;
    return ($a < $b) ? -1 : 1;
}
