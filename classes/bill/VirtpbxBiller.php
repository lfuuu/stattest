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
    private $tariffRange;

    protected function beforeProcess()
    {
        $this->tariffRange = $this->getTariffRange();
    }

    protected function processPeriodical()
    {
        foreach ($this->tariffRange as $range) {
            $tariff = $range['tariff'];

            $template = 'vpbx_service';
            $template_data = [
                'tariff' => $tariff->description
            ];

            $this->addPackage(
                BillerPackagePeriodical::create($this)
                    ->setActualPeriod($range['from'], $range['to'])
                    ->setPeriodType($tariff->period)
                    ->setIsAlign(true)
                    ->setIsPartialWriteOff(false)
                    ->setAmount($this->usage->amount)
                    ->setPrice($tariff->price)
                    ->setTemplate($template)
                    ->setTemplateData($template_data)
            );
        }
    }

    protected function processResource()
    {
        foreach ($this->tariffRange as $range) {

            $stats = $this->getVpbxStat($range['from'], $range['to'], $range['tariff']);

            if ($stats['sum_space'] > 0) {
                $price = $stats['overrun_per_gb'];
                $amount = $stats['sum_space']/$stats['overrun_per_gb'];
                if ($price > 0) {
                    $template = 'vpbx_over_disk_usage';

                    $this->addPackage(
                        BillerPackageResource::create($this)
                            ->setPeriodType($range['tariff']->period)
                            ->setActualPeriod($range['from'], $range['to'])
                            ->setAmount($amount)
                            ->setPrice($price)
                            ->setTemplate($template)
                    );
                }
            }

            if ($stats['sum_number'] > 0) {
                $price = $stats['overrun_per_port'];
                $amount = $stats['sum_number']/$stats['overrun_per_port'];

                if ($price > 0) {
                    $template = 'vpbx_over_ports_count';

                    $this->addPackage(
                        BillerPackageResource::create($this)
                            ->setPeriodType($range['tariff']->period)
                            ->setActualPeriod($range['from'], $range['to'])
                            ->setAmount($amount)
                            ->setPrice($price)
                            ->setTemplate($template)
                    );
                }
            }

            if ($stats['sum_ext_dids'] > 0) {
                $price = $stats['ext_did_monthly_payment'];
                $amount = $stats['sum_ext_dids'] / $stats['ext_did_monthly_payment'];

                if ($price > 0) {
                    $template = 'vpbx_over_ext_did_count';

                    $this->addPackage(
                        BillerPackageResource::create($this)
                            ->setPeriodType($range['tariff']->period)
                            ->setActualPeriod($range['from'], $range['to'])
                            ->setAmount($amount)
                            ->setPrice($price)
                            ->setTemplate($template)
                    );
                }
            }
        }
    }

    private function getVpbxStat(DateTime $from, DateTime $to, TariffVirtpbx $tariff)
    {
        $totals = array(
            //'amount_number' => 0,
            //'amount_space' => 0,
            'sum_number' => 0,
            'sum_space' => 0,
            'sum_ext_dids' => 0,
            'overrun_per_gb' => 0,
            'overrun_per_port' => 0,
            'ext_did_count' => 0,
        );

        $vpbxStatList =
            Virtpbx::find()
                ->select('use_space,numbers,ext_did_count')
                ->andWhere(['client_id' => $this->clientAccount->id])
                ->andWhere(['usage_id' => $this->usage->id])
                ->andWhere('date >= :from', [':from' => $from->format('Y-m-d')])
                ->andWhere('date <= :to', [':to' => $to->format('Y-m-d')])
                ->asArray()
                ->all();

        foreach ($vpbxStatList as $vpbxStat) {
            //$tarif_info = TarifVirtpbx::getTarifByClient($this->clientAccount->id, $v->mdate);
            $useNumbers = $vpbxStat['numbers'];
            $useSpaceMb = Utils::bytesToMb($vpbxStat['use_space']);

            if ($useSpaceMb > $tariff->space) {
                $spaceForBill = ceil(($useSpaceMb - $tariff->space)/1024);
                //$totals['amount_space'] += $spaceForBill;

                $sumForSpace = ($spaceForBill * $tariff->overrun_per_gb) / $from->format('t');
                $totals['sum_space'] += $sumForSpace;
            }

            if ($useNumbers > $tariff->num_ports) {
                $numbersForBill = $useNumbers - $tariff->num_ports;
                //$totals['amount_number'] += $numbersForBill;

                $sumForNumbers = ($numbersForBill * $tariff->overrun_per_port) / $from->format('t');
                $totals['sum_number'] += $sumForNumbers;
            }

            if ($vpbxStat['ext_did_count'] > $tariff->ext_did_count) {
                $totals['sum_ext_dids'] +=
                    (
                        ($vpbxStat['ext_did_count'] - $tariff->ext_did_count)
                            *
                        $tariff->ext_did_monthly_payment
                    ) / $from->format('t');
            }

            $totals['overrun_per_gb'] = $tariff->overrun_per_gb;
            $totals['overrun_per_port'] = $tariff->overrun_per_port;
            $totals['ext_did_monthly_payment'] = $tariff->ext_did_monthly_payment;
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
                    'tariff' => TariffVirtpbx::findOne($lastTariffId)
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
                'tariff' => TariffVirtpbx::findOne($lastTariffId)
            ];
        }

        return $range;
    }

}