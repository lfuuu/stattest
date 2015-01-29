<?php
namespace app\classes\bill;

use app\classes\Utils;
use app\models\LogTarif;
use app\models\TariffVirtpbx;
use app\models\Virtpbx;
use Yii;
use DateTime;

class VirtpbxBiller extends Biller
{
    public function process()
    {
        $tariffRange = $this->getTariffRange();
        foreach ($tariffRange as $range) {

            $tariff = TariffVirtpbx::findOne($range['id']);

            $template = '{name}' . $this->getPeriodTemplate($tariff->period);

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setActualPeriod($range['from'], $range['to'])
                    ->setPeriodType($tariff->period)
                    ->setIsAlign(false)
                    ->setIsPartialWriteOff(false)
                    ->setAmount($this->usage->amount)
                    ->setName($tariff->description)
                    ->setTemplate($template)
                    ->setPrice($tariff->price)
            );

            $stats = $this->getVpbxStat($range['from'], $range['to'], $tariff);

            if ($stats['sum_space'] > 0) {
                $price = $stats['overrun_per_gb'];
                $amount = $stats['sum_space']/$stats['overrun_per_gb'];
                if ($price > 0) {
                    $template = 'Превышение дискового пространства ' . $this->getPeriodTemplate(Biller::PERIOD_MONTH);

                    $this->addPackage(
                        BillerPackageResource::create($this)
                            ->setActualPeriod($range['from'], $range['to'])
                            ->setTemplate($template)
                            ->setAmount($amount)
                            ->setPrice($price)
                    );
                }
            }

            if ($stats['sum_number'] > 0) {
                $price = $stats['overrun_per_port'];
                $amount = $stats['sum_number']/$stats['overrun_per_port'];

                if ($price > 0) {
                    $template = 'Превышение количества портов ' . $this->getPeriodTemplate(Biller::PERIOD_MONTH);

                    $this->addPackage(
                        BillerPackageResource::create($this)
                            ->setActualPeriod($range['from'], $range['to'])
                            ->setTemplate($template)
                            ->setAmount($amount)
                            ->setPrice($price)
                    );
                }
            }
        }

        return $this;
    }

    private function getVpbxStat(DateTime $from, DateTime $to, TariffVirtpbx $tariff)
    {
        $totals = array(
            //'amount_number' => 0,
            //'amount_space' => 0,
            'sum_number' => 0,
            'sum_space' => 0,
            'overrun_per_gb' => 0,
            'overrun_per_port' => 0
        );

        $vpbxStatList =
            Virtpbx::find()
                ->select('use_space,numbers')
                ->andWhere(['client_id' => $this->clientAccount->id])
                ->andWhere('date >= :from', [':from' => $from->format('Y-m-d')])
                ->andWhere('date <= :to', [':to' => $to->format('Y-m-d')])
                ->asArray()
                ->all();

        foreach ($vpbxStatList as $vpbxStat) {
            //$tarif_info = TarifVirtpbx::getTarifByClient($this->clientAccount->id, $v->mdate);
            $useNumbers = $vpbxStat['numbers'];
            $useSpaceMb = Utils::bytesToMb($vpbxStat['use_space']);

            if ($useSpaceMb > $tariff->space) {
                $spaceForBill = ceil(($useSpaceMb/* - $tariff->space*/)/1024);
                //$totals['amount_space'] += $spaceForBill;

                $sumForSpace = ($spaceForBill * $tariff->overrun_per_gb) / $from->format('t');
                $totals['sum_space'] += $sumForSpace;
            }

            if ($useNumbers > $tariff->num_ports) {
                $numbersForBill = $useNumbers/* - $tariff->num_ports*/;
                //$totals['amount_number'] += $numbersForBill;

                $sumForNumbers = ($numbersForBill * $tariff->overrun_per_port) / $from->format('t');
                $totals['sum_number'] += $sumForNumbers;
            }

            $totals['overrun_per_gb'] = $tariff->overrun_per_gb;
            $totals['overrun_per_port'] = $tariff->overrun_per_port;
        }

        return $totals;
    }

    private function getTariffRange()
    {
        $logTariffList =
            LogTarif::find()
                ->select('date_activation, id, id_tarif')
                ->andWhere(['service' => 'usage_virtpbx', 'id_service' => $this->usage->id])
                ->andWhere('id_tarif != 0')
                ->orderBy('date_activation asc, id desc')
                ->asArray()
                ->all();

        $tariffChanges = [];
        $lastTariffId = null;
        foreach ($logTariffList as $logTariff) {
            if (!isset($tariffChanges[$logTariff['date_activation']])) {
                if ($logTariff['id_tarif'] != $lastTariffId) {
                    $tariffChanges[$logTariff['date_activation']] = $logTariff['id_tarif'];
                    $lastTariffId = $logTariff['id_tarif'];
                }
            }
        }

        $range = [];
        $lastTariffId = null;
        $lastTariffDate = null;
        foreach ($tariffChanges as $activationDate => $tariffId) {
            $activationDate = new DateTime($activationDate , $this->timezone);
            if ($activationDate <= $this->billerActualFrom) {
                $lastTariffId = $tariffId;
                $lastTariffDate = $this->billerActualFrom;
                continue;
            }

            if ($lastTariffId === null) {
                $lastTariffId = $tariffId;
                $lastTariffDate = $this->billerActualFrom;
                continue;
            }

            if ($activationDate <= $this->billerActualTo) {
                $to = clone $activationDate;
                $to->modify('-1 second');
                $range[] = [
                    'from' => $lastTariffDate,
                    'to' => $to,
                    'id' => $lastTariffId,
                ];
                $lastTariffId = $tariffId;
                $lastTariffDate = $activationDate;
                continue;
            }
        }

        if ($lastTariffId) {
            $range[] = [
                'from' => $lastTariffDate,
                'to' => $this->billerActualTo,
                'id' => $lastTariffId,
            ];
        }

        return $range;
    }

}