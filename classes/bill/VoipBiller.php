<?php
namespace app\classes\bill;

use app\classes\Assert;
use app\models\LogTarif;
use app\models\TariffVoip;
use Yii;

class VoipBiller extends Biller
{
    public function process()
    {
        $logTariff = $this->getLogTariff();
        if ($logTariff === null) {
            return $this;
        }

        $tariff = $this->getTariff($logTariff);

        $price = ($this->usage->no_of_lines - 1) * $tariff->once_line;
        if ($price < 0) {
            $price = 0;
        }
        $price += $tariff->once_number;

        $template = 'Подключение к IP-телефонии по тарифу {name}';

        $this->addPackage(
            BillerPackageConnecting::create($this)
                ->setName($tariff->name)
                ->setTemplate($template)
                ->setPrice($price)
        );


        $is7800 = substr($this->usage->E164, 0, 4) == "7800";

        if ($tariff->month_number > 0) {
            $template = 'Абонентская плата за телефонный номер {name}' . $this->getPeriodTemplate(self::PERIOD_MONTH);

            if ($this->clientAccount->bill_rename1 == 'yes') {
                if (strpos($template, "бонентская плата за") !== false) {
                    $contractInfo = $this->getContractInfo();
                    if ($contractInfo) {
                        $template = "Оказанные услуги за " . mb_substr($template, mb_strpos($template, "за ", 0, 'utf-8') + 3, null, 'utf-8') . $contractInfo;
                    }
                }
            }

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setPeriodType($tariff->period)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setName($this->usage->E164)
                    ->setTemplate($template)
                    ->setPrice($tariff->month_number)
            );
        }

        if ($this->usage->no_of_lines > 1 && $tariff->month_line > 0) {
            $count = $this->usage->no_of_lines - 1;

            $template =
                'Абонентская плата за ' . $count .
                ' телефонн' . $this->rus_fin($count, 'ую', 'ые', 'ых') .
                ' лин' . $this->rus_fin($count, 'ию', 'ии', 'ий') . ' к номеру {name}' .
                $this->getPeriodTemplate(self::PERIOD_MONTH);

            if ($this->clientAccount->bill_rename1 == 'yes') {
                if (strpos($template, "бонентская плата за") !== false) {
                    $contractInfo = $this->getContractInfo();
                    if ($contractInfo) {
                        $template = "Оказанные услуги за " . mb_substr($template, mb_strpos($template, "за ", 0, 'utf-8') + 3, null, 'utf-8') . $contractInfo;
                    }
                }
            }

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setPeriodType($tariff->period)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setName($this->usage->E164)
                    ->setTemplate($template)
                    ->setAmount($count)
                    ->setPrice($tariff->month_line)
            );
        }

        $lines = $this->calc($is7800, $logTariff);

        foreach ($lines as $dest => $r) {

            $template = null;
            $minPayment = null;
            $minPaymentTemplate = null;

            if ($dest == '4'){
                $template = 'Превышение лимита, включенного в абонентскую плату по номеру {name} (местные вызовы)';
            }elseif($dest == '5'){
                $template = 'Плата за звонки на местные мобильные с номера {name}';
                $minPayment = $logTariff->minpayment_local_mob;
                $minPaymentTemplate = 'Минимальный платеж за звонки на местные мобильные с номера {name}';;
            }elseif($dest == '1'){
                $template = 'Плата за междугородные звонки с номера {name}';
                $minPayment = $logTariff->minpayment_russia;
                $minPaymentTemplate = 'Минимальный платеж за междугородные звонки с номера {name}';
            }elseif($dest == '2'){
                $template = 'Плата за звонки в дальнее зарубежье с номера {name}';
                $minPayment = $logTariff->minpayment_intern;
                $minPaymentTemplate = 'Минимальный платеж за звонки в дальнее зарубежье с номера {name}';
            }elseif($dest == '3'){
                $template = 'Плата за звонки в ближнее зарубежье с номера {name}';
                $minPayment = $logTariff->minpayment_sng;
                $minPaymentTemplate = 'Минимальный платеж за звонки в ближнее зарубежье с номера {name}';
            }elseif($dest == '100'){
                $group = array();
                if (strpos($logTariff->dest_group, '5') !== FALSE) $group[]='местные мобильные';
                if (strpos($logTariff->dest_group, '1') !== FALSE) $group[]='междугородные';
                if (strpos($logTariff->dest_group, '2') !== FALSE) $group[]='дальнее зарубежье';
                if (strpos($logTariff->dest_group, '3') !== FALSE) $group[]='ближнее зарубежье';
                $group = implode(', ', $group);

                $template = "Плата за звонки в наборе ($group) с номера {name}";
                $minPayment = $logTariff->minpayment_group;
                $minPaymentTemplate = "Минимальный платеж за набор ($group) с номера {name}";
            }elseif($dest == '900'){
                if ($is7800) {
                    $template = 'Плата за звонки по номеру {name}';
                    $minPayment = $tariff->month_min_payment;
                    $minPaymentTemplate = 'Минимальный платеж за звонки по номеру {name}';
                } else {
                    $template = 'Плата за звонки по номеру {name} (местные, междугородные, международные)';
                }

            }

            $template .= $this->getPeriodTemplate(self::PERIOD_MONTH);
            if ($minPaymentTemplate) {
                $minPaymentTemplate .= $this->getPeriodTemplate(self::PERIOD_MONTH);
            }

            if ($this->clientAccount->bill_rename1 == 'yes') {
                if (strpos($template, "Плата за звонки по номеру") !== false) {
                    $template = str_replace("Плата", "Оказанные услуги", $template) . $this->getContractInfo();
                }
                if ($minPaymentTemplate && strpos($minPaymentTemplate, "Плата за звонки по номеру") !== false) {
                    $minPaymentTemplate = str_replace("Плата", "Оказанные услуги", $minPaymentTemplate) . $this->getContractInfo();
                }
            }

            $this->addPackage(
                $package =
                    BillerPackageResource::create($this)
                        ->setName($this->usage->E164)
                        ->setPrice($r['price'])
                        ->setTemplate($template)
                        ->setMinPayment($minPayment)
                        ->setMinPaymentTemplate($minPaymentTemplate)
                );
        }

        return $this;
    }

    /**
     * @return LogTarif
     */
    private function getLogTariff()
    {
        return
            LogTarif::find()
                ->andWhere(['service' => 'usage_voip', 'id_service' => $this->usage->id])
                ->andWhere('date_activation < :from', [':from' => $this->billerActualFrom->format('Y-m-d')])
                ->andWhere('id_tarif != 0')
                ->orderBy('date_activation desc, id desc')
                ->limit(1)
                ->one();
    }

    /**
     * @return TariffVoip
     */
    private function getTariff(LogTarif $logTariff)
    {
        $tariff = TariffVoip::findOne($logTariff->id_tarif);

        Assert::isObject($tariff);

        return $tariff;
    }

    private function calc($is7800, LogTarif $logTarif)
    {
        $res =
            Yii::$app->get('dbPg')
                ->createCommand('
                        select
                            case dest <= 0 when true then
                                case mob when true then 5 else 4 end
                            else dest end rdest,
                            cast( sum(amount)/100.0 as NUMERIC(10,2)) as price
                        from
                            calls.calls_'.intval($this->usage->region).'
                        where
                            usage_id = :usageId
                            and time >= :from
                            and time <= :to
                            and amount > 0
                        group by rdest
                        having cast( sum(amount)/100.0 as NUMERIC(10,2)) > 0
                    ', [
                        ':usageId' => $this->usage->id,
                        ':from' => $this->billerActualFrom->format('Y-m-d H:i:s'),
                        ':to' => $this->billerActualTo->format('Y-m-d H:i:s'),
                    ]
                )
                ->queryAll();

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
