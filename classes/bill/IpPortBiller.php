<?php
namespace app\classes\bill;

use app\classes\Assert;
use app\models\LogTarif;
use app\models\TariffInternet;
use app\models\TechCpe;
use app\models\UsageIpRoutes;
use Yii;

class IpPortBiller extends Biller
{
    public function process()
    {
        $tariff = $this->getTariff();
        if ($tariff === null) {
            return $this;
        }

        $template = 'Подключение к интернет по тарифу {name}';
        $this->addPackage(
            BillerPackageConnecting::create($this)
                ->setName($tariff->name)
                ->setTemplate($template)
                ->setPrice($tariff->pay_once)
        );

        $template =
            $tariff->type == "C"
                ? $tariff->name . $this->getPeriodTemplate(self::PERIOD_MONTH)
                : 'Абонентская плата за доступ в интернет (подключение '.$this->usage->id.', тариф {name})' . $this->getPeriodTemplate(self::PERIOD_MONTH);

        $this->addPackage(
            BillerPackagePeriodical::create($this)
                ->setPeriodType(self::PERIOD_MONTH)
                ->setIsAlign(true)
                ->setIsPartialWriteOff(false)
                ->setName($tariff->name)
                ->setTemplate($template)
                ->setAmount($this->usage->amount)
                ->setPrice($tariff->pay_month)
        );

        $template =
            'Превышение лимита {name}, включенного в абонентскую плату (подключение '.
            $this->usage->id . ', тариф '. $tariff->name . ')' .
            $this->getPeriodTemplate(self::PERIOD_MONTH);

        if ($tariff->type=='I') {
            $S = $this->calcIC();
            $S['in'] = $S['in_r'] + $S['in_r2'] + $S['in_f'];
            $S['out']= $S['out_r'] + $S['out_r2'] + $S['out_f'];
            $mb = max($S['in'],$S['out']);
            $this->addPackage(
                BillerPackageResource::create($this)
                    ->setName(($S['in']>$S['out']?'входящего':'исходящего').' трафика')
                    ->setTemplate($template)
                    ->setFreeAmount($tariff->mb_month)
                    ->setAmount($mb)
                    ->setPrice($tariff->pay_mb)
            );
        } elseif ($tariff->type=='C') {
            $S = $this->calcIC();
            if ($tariff->type_count=='r2_f') {
                $S['in_f'] += $S['in_r2'];
                $S['in_r2'] = 0;
                $N = array('бесплатного входящего трафика','','платного входящего трафика');
            } elseif ($tariff->type_count == 'all_f') {
                $S['in_f'] += $S['in_r'] + $S['in_r2'];
                $S['in_r'] = 0;
                $S['in_r2'] = 0;
                $N = array('','','платного входящего трафика');
            } else {
                $N = array('входящего трафика "Россия"','входящего трафика "Россия-2"','входящего трафика "Иностранный"');
            }
            $this->addPackage(
                BillerPackageResource::create($this)
                    ->setName($N[0])
                    ->setTemplate($template)
                    ->setFreeAmount($tariff->month_r)
                    ->setAmount($S['in_r'])
                    ->setPrice($tariff->pay_r)
            );
            $this->addPackage(
                BillerPackageResource::create($this)
                    ->setName($N[1])
                    ->setTemplate($template)
                    ->setFreeAmount($tariff->month_r2)
                    ->setAmount($S['in_r2'])
                    ->setPrice($tariff->pay_r2)
            );
            $this->addPackage(
                BillerPackageResource::create($this)
                    ->setName($N[2])
                    ->setTemplate($template)
                    ->setFreeAmount($tariff->month_f)
                    ->setAmount($S['in_f'])
                    ->setPrice($tariff->pay_f)
            );
        } elseif ($tariff->type=='V') {
            $S = $this->calcV();
            $mb = max($S['in'],$S['out']);
            $this->addPackage(
                BillerPackageResource::create($this)
                    ->setName(($S['in']>$S['out']?'входящего':'исходящего').' трафика')
                    ->setTemplate($template)
                    ->setFreeAmount($tariff->mb_month)
                    ->setAmount($mb)
                    ->setPrice($tariff->pay_mb)
            );
        }

        return $this;
    }

    private function calcIC()
    {
        $filter = '';

        $routes =
            UsageIpRoutes::find()
                ->andWhere(['port_id' => $this->usage->id])
                ->asArray()
                ->all();

        foreach ($routes as $r) {
            list($ip,$sum) = $this->netmask_to_ip_sum($r['net']);
            if($sum && $ip){
                $filterR = 'time>="'.$r['actual_from'].'" and time<="'.$r['actual_to'].'"';
                if($sum<=128){
                    $s='ip_int IN (';
                    for($i=0;$i<$sum;$i++)
                        $s.=($i?',':'').($ip+$i);
                    $s .= ')';
                    $filterR .= ' and ' . $s;
                }else{
                    $filterR .= ' and ip_int>='.$ip;
                    $filterR .= ' and ip_int<='.($ip+$sum-1);
                }
                if ($filter) {
                    $filter .= ' or (' . $filterR . ') ';
                } else {
                    $filter = ' (' . $filterR . ') ';
                }
            }
        }

        if ($this->usage->id == 4465) {
            return array('in_r'=>4000000000,'in_r2'=>4000000000,'in_f'=>4000000000,'out_r'=>0,'out_r2'=>0,'out_f'=>0);
        }

        if (!$filter) {
            return array('in_r' => 0, 'in_r2' => 0, 'in_f' => 0, 'out_r' => 0, 'out_r2' => 0, 'out_f' => 0);
        }

        return
            Yii::$app->db->createCommand("
                    select
                        sum(in_r)/1048576 as in_r,
                        sum(out_r)/1048576 as out_r,
                        sum(in_r2)/1048576 as in_r2,
                        sum(out_r2)/1048576 as out_r2,
                        sum(in_f)/1048576 as in_f,
                        sum(out_f)/1048576 as out_f
                    from
                        traf_flows_1d
                    where
                        time >= :from
                        and time <= :to
                        and router='rubicon'
                        " . ($filter ? ' and (' . $filter . ')' : '') . "
                ",
                [
                    ':from' => $this->billerActualFrom->format('Y-m-d'),
                    ':to' => $this->billerActualTo->format('Y-m-d'),
                ]
            )
            ->queryOne();
    }

    private function calcV()
    {
        $filter = '';
        $techCpe =
            TechCpe::find()
                ->andWhere(['service' => 'usage_ip_ports', 'id_service' => $this->usage->id])
                ->asArray()
                ->all();
        foreach ($techCpe as $r) {
            $filterR = 'ip_int=INET_ATON("'.$r['ip'].'") and datetime>="'.$r['actual_from'].'" and datetime<="'.$r['actual_to'].'"';
            if ($filter) {
                $filter .= ' or (' . $filterR . ') ';
            } else {
                $filter = ' (' . $filterR . ') ';
            }
        }

        if(!$filter) {
            return array('in' => 0, 'out' => 0);
        }

        return
            Yii::$app->db->createCommand("
                    select
                        sum(transfer_rx)/1048576 as `in`,
                        sum(transfer_tx)/1048576 as `out`
                    from
                        mod_traf_1d
                    where
                      datetime >= :from
                      and datetime <= :to
                        " . ($filter ? ' and (' . $filter . ')' : '') . "
                ",
                [
                    ':from' => $this->billerActualFrom->format('Y-m-d'),
                    ':to' => $this->billerActualTo->format('Y-m-d'),
                ]
            )
            ->queryOne();
    }

    /**
     * @return TariffInternet
     */
    private function getTariff()
    {
        $logTariff =
            LogTarif::find()
                ->andWhere(['service' => 'usage_ip_ports', 'id_service' => $this->usage->id])
                ->andWhere('date_activation < :to', [':to' => $this->billerActualTo->format('Y-m-d')])
                ->andWhere('id_tarif != 0')
                ->orderBy('date_activation desc, id desc')
                ->limit(1)
                ->one();

        if (!$logTariff) {
            return null;
        }

        return TariffInternet::findOne($logTariff->id_tarif);
    }

    private function netmask_to_ip_sum($mask){
        if (!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/",$mask,$m)) return;
        $sum=1;
        if (isset($m[6]) && $m[6]) for ($i=$m[6];$i<32;$i++) $sum*=2;
        return array(256*(256*(256*$m[1]+$m[2])+$m[3])+$m[4], $sum);
    }
}