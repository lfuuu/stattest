<?php
namespace app\classes\bill;

use app\classes\Assert;
use app\classes\Utils;
use app\helpers\DateTimeZoneHelper;
use app\models\LogTarif;
use app\models\TariffVoip;
use app\dao\billing\CallsDao;
use Yii;

class VoipBiller extends Biller
{
    /** @var LogTarif */
    private $logTariff = false;
    /** @var TariffVoip */
    private $tariff = false;

    private $rangeTariff = [];

    public function getTranslateFilename()
    {
        return 'biller-voip';
    }

    public function beforeProcess()
    {
        $logTariffList = [];

        /** @var LogTarif $logTariff */
        foreach(LogTarif::find()
                ->andWhere(['service' => 'usage_voip', 'id_service' => $this->usage->id])
                ->andWhere('id_tarif != 0')
                ->orderBy('date_activation, id desc')
                ->all() as $logTariff) {

            if (!isset($logTariffList[$logTariff->date_activation])) {
                $logTariffList[$logTariff->date_activation] = $logTariff;
            }
        }

        if (!$logTariffList) {
            return false;
        }

        // Фильтруем. Только реальные смены тарифов
        $filteredTariffList = [];
        $prevTariffId = null;
        /** @var LogTarif $logTariff */
        foreach($logTariffList as $activationDate => $logTariff) {
            if ($prevTariffId != $logTariff->id_tarif) {
                $filteredTariffList[$activationDate] = $logTariff;
            }
            $prevTariffId = $logTariff->id_tarif;
        }


        //Нарезаем лог тарифов. Только за период биллера
        $rangeTariff = [];
        $prevTariffId = null;
        $prevActivationDate = null;
        $prevLogTariff = null;
        foreach($filteredTariffList as $activationDate => $logTariff) {
            $activationDateDt = new \DateTime($activationDate, $this->timezone);

            //отбрасываем тарифы, начинающиеся до начала услуги.
            if ($activationDateDt <= $this->usageActualFrom) {
                $prevActivationDate = clone $this->billerPeriodFrom;
                $prevLogTariff = $logTariff;
                continue;
            }

            //отбрасываем тарифы, начинающиеся до периода билингации
            if ($activationDateDt <= $this->billerPeriodFrom) {
                $prevActivationDate = clone $this->billerPeriodFrom;
                $prevLogTariff = $logTariff;
                continue;
            }

            if ($activationDateDt > $this->billerPeriodTo) {
                if ($prevActivationDate) {
                    $rangeTariff[] = [
                        'from' => $prevActivationDate,
                        'to' => clone $this->billerPeriodTo,
                        'tariff' => TariffVoip::findOne($prevLogTariff->id_tarif),
                        'logTariff' => $prevLogTariff
                    ];
                    $prevActivationDate = null;
                    $prevLogTariff = null;
                }
                break;
            }

            if ($prevActivationDate) {
                $to = clone $activationDateDt;
                $to->modify('-1 second');

                $rangeTariff[] = [
                    'from' => $prevActivationDate,
                    'to' => $to,
                    'tariff' => TariffVoip::findOne($prevLogTariff->id_tarif),
                    'logTariff' => $prevLogTariff
                ];
            }

            $prevActivationDate = clone $activationDateDt;
            $prevLogTariff = $logTariff;
        }

        if ($prevActivationDate) {
            $to = clone $this->billerPeriodTo;

            $rangeTariff[] = [
                'from' => $prevActivationDate,
                'to' => $to,
                'tariff' => TariffVoip::findOne($prevLogTariff->id_tarif),
                'logTariff' => $prevLogTariff
            ];
        }

        if (!$rangeTariff) {
            return false;
        }

        $this->rangeTariff = $rangeTariff;
    }

    protected function processConnecting()
    {
        foreach ($this->rangeTariff as $range) {
            /** @var TariffVoip $tariff */
            $tariff = $range['tariff'];

            $price = ($this->usage->no_of_lines - 1) * $tariff->once_line;
            if ($price < 0) {
                $price = 0;
            }
            $price += $tariff->once_number;

            $template = 'voip_connection';
            $template_data = [
                'tariff' => $tariff->name
            ];

            $this->addPackage(
                BillerPackageConnecting::create($this)
                    ->setPrice($price)
                    ->setTemplate($template)
                    ->setTemplateData($template_data)
            );
        }
    }

    protected function processPeriodical()
    {
        foreach ($this->rangeTariff as $range) {
            /** @var TariffVoip $tariff */
            $tariff = $range['tariff'];

            if ($tariff->month_number > 0) {
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
                        ->setPeriodType($tariff->period)
                        ->setIsAlign(true)
                        ->setIsPartialWriteOff(false)
                        ->setPrice($tariff->month_number)
                        ->setTemplate($template)
                        ->setTemplateData($template_data)
                        ->setActualPeriod($range['from'], $range['to'])
                );
            }

            if ($this->usage->no_of_lines > 1 && $tariff->month_line > 0) {
                $count = $this->usage->no_of_lines - 1;

                $template = 'voip_monthly_fee_per_line';
                $template_data = [
                    'lines_number' => $count,
                    'plural_first' => Utils::rus_plural($count, 'ую', 'ые', 'ых'),
                    'plural_second' => Utils::rus_plural($count, 'ию', 'ии', 'ий'),
                    'service' => $this->usage->E164
                ];

                if ($this->clientAccount->bill_rename1 == 'yes') {
                    $template = 'voip_monthly_fee_per_line_custom';
                    $template_data['by_agreement'] = $this->getContractInfo();
                }

                $this->addPackage(
                    BillerPackagePeriodical::create($this)
                        ->setPeriodType($tariff->period)
                        ->setIsAlign(true)
                        ->setIsPartialWriteOff(false)
                        ->setAmount($count)
                        ->setPrice($tariff->month_line)
                        ->setTemplate($template)
                        ->setTemplateData($template_data)
                        ->setActualPeriod($range['from'], $range['to'])
                );
            }
        }
    }

    protected function processResource()
    {
        $is7800 = substr($this->usage->E164, 0, 4) == "7800";

        foreach($this->rangeTariff as $range) {
            /** @var TariffVoip $tariff */
            /** @var  LogTarif $logTariff */
            $tariff = $range['tariff'];
            $logTariff = $range['logTariff'];

            $rangeFromMutable = clone $range['from'];
            $rangeToMutable = clone $range['to'];

            $lines = $this->calc($is7800, $logTariff, $rangeFromMutable, $rangeToMutable);

            foreach ($lines as $dest => $r) {

                $template = null;
                $minPayment = null;
                $minPaymentTemplate = null;

                if ($dest == '4') {
                    $template = 'voip_overlimit';
                } elseif ($dest == '5') {
                    $template = 'voip_local_mobile_call_payment';
                    $minPayment = $logTariff->minpayment_local_mob;
                    $minPaymentTemplate = 'voip_local_mobile_call_minpay';
                } elseif ($dest == '1') {
                    $template = 'voip_long_distance_call_payment';
                    $minPayment = $logTariff->minpayment_russia;
                    $minPaymentTemplate = 'voip_long_distance_call_minpay';
                } elseif ($dest == '2') {
                    $template = 'voip_international_call_payment';
                    $minPayment = $logTariff->minpayment_intern;
                    $minPaymentTemplate = 'voip_international_call_minpay';
                } elseif ($dest == '100') {
                    $group = array();
                    if (strpos($logTariff->dest_group, '5') !== false) {
                        $group[] = Yii::t(
                            $this->getTranslateFilename(),
                            'voip_group_local',
                            [],
                            $this->clientAccount->contragent->country->lang
                        );
                    }
                    if (strpos($logTariff->dest_group, '1') !== false) {
                        $group[] = Yii::t(
                            $this->getTranslateFilename(),
                            'voip_group_long_distance',
                            [],
                            $this->clientAccount->contragent->country->lang
                        );
                    }
                    if (strpos($logTariff->dest_group, '2') !== false) {
                        $group[] = Yii::t(
                            $this->getTranslateFilename(),
                            'voip_group_international',
                            [],
                            $this->clientAccount->contragent->country->lang
                        );
                    }

                    $template_data['group'] = implode(', ', $group);

                    $template = 'voip_group_payment';
                    $minPayment = $logTariff->minpayment_group;
                    $minPaymentTemplate = 'voip_group_minpay';
                } elseif ($dest == '900') {
                    if ($is7800) {
                        $template = 'voip_calls_payment';
                        $minPayment = $tariff->month_min_payment;
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
                    BillerPackageResource::create($this)
                        ->setPrice($r['price'])
                        ->setMinPayment($minPayment)
                        ->setMinPaymentTemplate($minPaymentTemplate)
                        ->setPeriodType(self::PERIOD_MONTH)// Need for localization
                        ->setTemplate($template)
                        ->setTemplateData($template_data)
                        ->setActualPeriod($range['from'], $range['to'])
                );
            }
        }
    }

    private function calc($is7800, LogTarif $logTarif, \DateTime $from, \DateTime $to)
    {
        $from->setTimezone(new \DateTimeZone('UTC'));
        $to->setTimezone(new \DateTimeZone('UTC'));

        $res = CallsDao::calcByDest($this->usage, $from, $to);

        $groups = $logTarif->dest_group;

        /*
           [dest_group] => 0
           [minpayment_group] => 0      // 100

           [minpayment_local_mob] => 0  // 5
           [minpayment_russia] => 1500  // 1
           [minpayment_intern] => 0     // 2

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
        }

        foreach ($res as $r) {
            $dest = $r['rdest'];
            if (strpos($groups, $dest) !== false) {
                $dest = '100';
            }
            if ((int)$logTarif->minpayment_group +
                (int)$logTarif->minpayment_local_mob +
                (int)$logTarif->minpayment_russia +
                (int)$logTarif->minpayment_intern == 0
            ) {
                $dest = '900';
            }

            if (!isset($lines[$dest])) {
                $lines[$dest] = array('price' => 0);
            }
            $lines[$dest]['price'] += $r['price'];
        }

        if ($is7800 && !$lines) {
            $lines["900"] = array("price" => 0);
        }

        uksort($lines, '\app\classes\bill\cmp_calc_voip_by_dest');

        return $lines;
    }

}

function cmp_calc_voip_by_dest($a, $b)
{
    $a = ($a < 4 ? $a + 10 : $a);
    $b = ($b < 4 ? $b + 10 : $b);
    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
