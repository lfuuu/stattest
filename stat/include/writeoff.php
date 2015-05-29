<?php

use app\classes\BillContract;

global $writeoff_services;
$writeoff_services=array("usage_ip_ports","usage_voip","bill_monthlyadd", "usage_virtpbx", "usage_extra","usage_welltime", "emails","usage_sms");

function Underscore2Caps($s) {
    return preg_replace_callback("/_(.)/",create_function('$a','return strtoupper($a[1]);'),$s);
}
class ServiceFactory {
    public static function Get($service, $bill) {
        $s="Service".Underscore2Caps('_'.$service['service']);
        return new $s($service,$bill);
    }
}

abstract class ServicePrototype {
    public $service;
    public $tarif_current;
    public $tarif_previous;
    public $tarifs;
    public $devices;
    public $date_from,$date_to,$date_from_prev,$date_to_prev,$bill_no;
    public $client;
    protected $tarif_std = 1;

    public function __construct($service,$bill) {
        $this->service=$service;
        if (is_array($bill)) {
            $this->client = $bill;
        } else {
            $this->client = $bill->Client();
        }
        if(is_object($bill))
            $this->bill_no = $bill->GetNo();
        elseif(is_array($bill) && isset($bill['bill_no']))
            $this->bill_no = $bill['bill_no'];
        if ($service['service']=='usage_ip_ports') {
            $this->devices = get_cpe_history ($service['service'],$service['id']);
        } else $this->devices=array();
        if (!$this->tarif_std) $this->LoadTarif();
    }
    public function SetDate($date_from,$date_to,$date_from_prev = 0,$date_to_prev = 0){
        $this->date_from = max($date_from,strtotime($this->service['actual_from']));
        $this->date_to = min($date_to,strtotime($this->service['actual_to']));
        if($this->date_to<$this->date_from){
            $this->date_to=0;
            $this->date_from=0;
        }
        $this->date_from_prev = max($date_from_prev,strtotime($this->service['actual_from']));
        $this->date_to_prev = min($date_to_prev,strtotime($this->service['actual_to']));
        if($this->date_to_prev<$this->date_from_prev){
            $this->date_to_prev=0;
            $this->date_from_prev=0;
        }

        if($this->tarif_std==1){    //берутся ли тарифы из log_tarif_.. и tarifs_..?
            if($this->date_from){
                $this->tarifs = get_tarif_history($this->service['service'],$this->service['id'],'FROM_UNIXTIME('.$this->date_from.')');
                foreach($this->tarifs as $t)
                    if($t['is_current'])
                        $this->tarif_current = $t;
                    elseif($t['is_previous'])
                        $this->tarif_previous = $t;
                if(!$this->tarif_previous)
                    $this->tarif_previous = $this->tarif_current;
            }elseif($this->date_from_prev){
                $this->tarifs = get_tarif_history($this->service['service'],$this->service['id'],'FROM_UNIXTIME('.$this->date_from_prev.')');
                foreach($this->tarifs as $t)
                if($t['is_current'])
                    $this->tarif_previous = $t;
            }
        }
    }

    public function getServicePreBillAmount()
    {
        return 
            isset($this->service['amount']) && isset($this->tarif_current['price']) ? 
            $this->service['amount']*$this->GetDatePercent()*$this->tarif_current['price'] : 
            0;
    }

    public function LoadTarif() {}    //тело в потомке, если нужно
    public function SetMonth($month) {
        $d=getdate($month);
        $d2=$d;
        $d2['mon']--;
        if($d2['mon']==0){
            $d2['mon']=12;
            $d2['year']--;
        }
        return $this->SetDate(
            mktime(0,0,0,$d['mon'],1,$d['year']),
            mktime(0,0,0,$d['mon'],cal_days_in_month(CAL_GREGORIAN, $d['mon'], $d['year']),$d['year']),
            mktime(0,0,0,$d2['mon'],1,$d2['year']),
            mktime(0,0,0,$d2['mon'],cal_days_in_month(CAL_GREGORIAN, $d2['mon'], $d2['year']),$d2['year'])
        );
    }
    public function GetLinesConnect() {
        $R=array();
        if(false)
        foreach ($this->devices as $d) {
            $t=$d['vendor'].' '.$d['model'];
            if ($d['serial']) $t.=' (серийный номер '.$d['serial'].')';
            $R[]=array($this->tarif_current['currency'],'Залог за '.$t,1,$d['deposit_sum'.$this->tarif_current['currency']],'zalog','tech_cpe',$d['id'],$this->service['actual_from'],$this->service['actual_from']);
        }
        return $R;
    }
    public function GetDatePercent($date_from=null, $date_to = null) 
    {
        if($date_from === null) $date_from = $this->date_from;
        if($date_to === null) $date_to = $this->date_to;

        if (!$date_from || !$date_to) return 0;

        $d1=getdate($date_from);
        $d2=getdate($date_to);

        if (isset($this->tarif_current['period']) && $this->tarif_current['period']=='once') return 1;
        if (isset($this->tarif_current['period']) && $this->tarif_current['period']=='year') {
            $v=$d2['year']-$d1['year'];
            if ($d1['yday']>=$d2['yday']) {
                $v--;
                $ydays=337+cal_days_in_month(CAL_GREGORIAN, 2, $d1['year']);
                $v+=(1+$ydays-$d1['yday'])/$ydays;
                $ydays=337+cal_days_in_month(CAL_GREGORIAN, 2, $d2['year']);
                $v+=($d2['yday'])/$ydays;
            } else {
                $ydays=337+cal_days_in_month(CAL_GREGORIAN, 2, $d2['year']);
                $v+=(1+$d2['yday']-$d1['yday'])/$ydays;
            }
            return $v;
        } else {
            $v=($d2['mday']-$d1['mday']+1)/cal_days_in_month(CAL_GREGORIAN, $d1['mon'], $d1['year']);
            if ($d1['year']==$d2['year'] && $d1['mon']==$d2['mon']) return $v;
        }
        return 1;
    }
    public function GetDatePercentPrev() {
        if (!$this->date_from_prev || !$this->date_to_prev) return 0;
        $d1=getdate($this->date_from_prev);
        $d2=getdate($this->date_to_prev);
        //нужно учесть период услуги
        $v=($d2['mday']-$d1['mday']+1)/cal_days_in_month(CAL_GREGORIAN, $d1['mon'], $d1['year']);
        if ($d1['year']==$d2['year'] && $d1['mon']==$d2['mon']) return $v;
        return 1;
    }
    public function GetLinesMonth() {
        return array();
    }

}
class ServiceUsageIpPorts extends ServicePrototype {
    public function GetLinesConnect(){
        $R = ServicePrototype::GetLinesConnect();
        $R[] = array(
            $this->tarif_current['currency'],
            'Подключение к интернет по тарифу '.$this->tarif_current['name'],
            1,
            $this->tarif_current['pay_once'],
            'service',
            $this->service['service'],
            $this->service['id'],
            $this->service['actual_from'],
            $this->service['actual_to']
        );
        return $R;
    }
    private function calcIC(){
        global $db;
        $P = array('OR');
        $db->Query("
            select
                *
            from
                usage_ip_routes
            where
                port_id=".$this->service['id']
        );

        while($r=$db->NextRecord()){
            list($ip,$sum) = netmask_to_ip_sum($r['net']);
            if($sum && $ip){
                $t = array(
                    'AND',
                    'time>="'.$r['actual_from'].'"',
                    'time<="'.$r['actual_to'].'"'
                );
                if($sum<=128){
                    $s='ip_int IN (';
                    for($i=0;$i<$sum;$i++)
                        $s.=($i?',':'').($ip+$i);
                    $t[] = $s.')';
                }else{
                    $t[] = 'ip_int>='.$ip;
                    $t[] = 'ip_int<='.($ip+$sum-1);
                }
                $P[] = $t;
            }
        }
        if($this->service['id']==4465){
            return array('in_r'=>4000000000,'in_r2'=>4000000000,'in_f'=>4000000000,'out_r'=>0,'out_r2'=>0,'out_f'=>0);
        }
        if(count($P)==1)
            return array('in_r'=>0,'in_r2'=>0,'in_f'=>0,'out_r'=>0,'out_r2'=>0,'out_f'=>0);
        $W=array(
            'AND',
            'time>=FROM_UNIXTIME('.$this->date_from_prev.')',
            'time<FROM_UNIXTIME('.$this->date_to_prev.'+86400)',
            'router="rubicon"',
            $P,
        );
        return $db->GetRow(
            'select
                sum(in_r)/1048576 as in_r,
                sum(out_r)/1048576 as out_r,
                sum(in_r2)/1048576 as in_r2,
                sum(out_r2)/1048576 as out_r2,
                sum(in_f)/1048576 as in_f,
                sum(out_f)/1048576 as out_f
            from
                traf_flows_1d
            where
                '.MySQLDatabase::Generate($W)
        );
    }
    private function calcV(){
        global $db;
        $P = array('OR');
        $db->Query('
            select
                *
            from
                tech_cpe
            where
                service="'.$this->service['service'].'"
            and
                id_service="'.$this->service['id'].'"'
        );
        while($r=$db->NextRecord()){
            $P[] = array(
                'AND',
                'ip_int=INET_ATON("'.$r['ip'].'")',
                'datetime>="'.$r['actual_from'].'"',
                'datetime<="'.$r['actual_to'].'"'
            );
        }
        if(count($P)==1)
            return array('in'=>0,'out'=>0);
        $W = array(
            'AND',
            'datetime >= FROM_UNIXTIME('.$this->date_from_prev.')',
            'datetime < FROM_UNIXTIME('.$this->date_to_prev.'+86400)',
            $P
        );
        return $db->GetRow(
            'select
                sum(transfer_rx)/1048576 as `in`,
                sum(transfer_tx)/1048576 as `out`
            from
                mod_traf_1d
            where
                '.MySQLDatabase::Generate($W)
        );
    }

    public function GetLinesMonth(){
        $R = ServicePrototype::GetLinesMonth();

        if($this->date_from && $this->date_to){

            $itemName = (
                $this->tarif_current["type"] == "C" ? 
                $this->tarif_current['name'].' с '.date('d',$this->date_from).' по '.mdate('d месяца',$this->date_to) :
                'Абонентская плата за доступ в интернет (подключение '.$this->service['id'].', тариф '.$this->tarif_current['name'].') с '.date('d',$this->date_from).' по '.mdate('d месяца',$this->date_to));

            if ($this->tarif_current['pay_month'] > 0 && $this->service['amount'] > 0) {
                $R[] = array(
                    $this->tarif_current['currency'],
                    $itemName,
                    $this->GetDatePercent()*$this->service['amount'],
                    $this->tarif_current['pay_month'],
                    'service',
                    $this->service['service'],
                    $this->service['id'],
                    date('Y-m-d', $this->date_from),
                    date('Y-m-d', $this->date_to)
                );
            }
        }


        if($this->date_from_prev && $this->date_to_prev){
            $TrafficOver=array();
            if($this->tarif_previous['type']=='I'){
                $S = $this->calcIC();
                $S['in'] = $S['in_r'] + $S['in_r2'] + $S['in_f'];
                $S['out']= $S['out_r'] + $S['out_r2'] + $S['out_f'];
                $mb=max($S['in'],$S['out']);
                if($mb > $this->tarif_previous['mb_month']*$this->GetDatePercentPrev()){
                    $TrafficOver[] = array(
                        ($S['in']>$S['out']?'входящего':'исходящего').' трафика',
                        $mb-$this->tarif_previous['mb_month']*$this->GetDatePercentPrev(),
                        $this->tarif_previous['pay_mb'],
                    );
                }
            }elseif($this->tarif_previous['type']=='C'){
                $S=$this->calcIC();
                if($this->tarif_previous['type_count']=='r2_f'){
                    $S['in_f'] += $S['in_r2'];
                    $S['in_r2'] = 0;
                    $N = array('бесплатного входящего трафика','','платного входящего трафика');
                }elseif($this->tarif_previous['type_count'] == 'all_f'){
                    $S['in_f'] += $S['in_r'] + $S['in_r2'];
                    $S['in_r'] = 0;
                    $S['in_r2'] = 0;
                    $N = array('','','платного входящего трафика');
                }else{
                    $N = array('входящего трафика "Россия"','входящего трафика "Россия-2"','входящего трафика "Иностранный"');
                }
                if($this->tarif_previous['pay_r'] && $S['in_r']>$this->tarif_previous['month_r']*$this->GetDatePercentPrev()){
                    $TrafficOver[] = array(
                        $N[0],
                        $S['in_r']-$this->tarif_previous['month_r']*$this->GetDatePercentPrev(),
                        $this->tarif_previous['pay_r'],
                    );
                }
                if($this->tarif_previous['pay_r2'] && $S['in_r2']>$this->tarif_previous['month_r2']*$this->GetDatePercentPrev()){
                    $TrafficOver[] = array(
                        $N[1],
                        $S['in_r2']-$this->tarif_previous['month_r2']*$this->GetDatePercentPrev(),
                        $this->tarif_previous['pay_r2'],
                    );
                }
                if($this->tarif_previous['pay_f'] && $S['in_f']>$this->tarif_previous['month_f']*$this->GetDatePercentPrev()){
                    $TrafficOver[] = array(
                        $N[2],
                        $S['in_f']-$this->tarif_previous['month_f']*$this->GetDatePercentPrev(),
                        $this->tarif_previous['pay_f'],
                    );
                }
            }elseif($this->tarif_previous['type']=='V'){
                $S = $this->calcV();
                $mb = max($S['in'],$S['out']);
                if($mb>$this->tarif_previous['mb_month']*$this->GetDatePercentPrev()){
                    $TrafficOver[] = array(
                        ($S['in']>$S['out']?'входящего':'исходящего').' трафика',
                        $mb-$this->tarif_previous['mb_month']*$this->GetDatePercentPrev(),
                        $this->tarif_previous['pay_mb'],
                    );
                }
            }
            foreach($TrafficOver as $T){
                $R[] = array(
                    $this->tarif_previous['currency'],
                    'Превышение лимита '.
                    $T[0].
                    ', включенного в абонентскую плату (подключение '.
                    $this->service['id'].
                    ', тариф '.
                    $this->tarif_previous['name'].
                    ') с '.date('d',$this->date_from_prev).' по '.mdate('d месяца',$this->date_to_prev),
                    $T[1],
                    $T[2],
                    'service',
                    $this->service['service'],
                    $this->service['id'],
                    date('Y-m-d',$this->date_from_prev),
                    date('Y-m-d',$this->date_to_prev),
                );
            }
        }

        if($this->client["bill_rename1"] == "yes")
        foreach($R as &$l)
        {
            if(strpos($l[1], "бонентская плата за доступ в интернет") !== false)
            {
                $clientId = $this->service["client_id"];
                if($clientId)
                {
                    if($str = BillContract::getBillItemString($clientId, $this->date_from))
                    {
                        $l[1] = "Оказанные услуги по предоставлению доступа в интернет ".mb_substr($l[1], mb_strpos($l[1], "(", 0, 'utf-8'), null, 'utf-8');
                        $l[1] .= $str;
                    }
                }

            }
        }
        return $R;
    }

    public function getServicePreBillAmount()
    {
        return $this->tarif_current['pay_month']*$this->GetDatePercent();
    }
}


function cmp_calc_voip_by_dest($a, $b)
{
    $a = ($a < 4 ? $a + 10 : $a);
    $b = ($b < 4 ? $b + 10 : $b);
    if ($a == $b) return 0;
    return ($a < $b) ? -1 : 1;
}

class ServiceUsageVoip extends ServicePrototype {
    public function GetLinesConnect(){
        $R=ServicePrototype::GetLinesConnect();
        $p=($this->service['no_of_lines']-1)*$this->tarif_current['once_line'];
        if ($p<0) $p=0;
        $p+=$this->tarif_current['once_number'];
        $R[]=array($this->tarif_current['currency'],'Подключение к IP-телефонии по тарифу '.$this->tarif_current['name'],1,$p,'service',$this->service['service'],$this->service['id'],$this->service['actual_from'],$this->service['actual_to']);
        return $R;
    }
    private function get_traffic_categories_list(){
        return array();
    }

    private function calc($is7800){
    global $pg_db;
        $d = getdate($this->date_from_prev);

        /** @var \app\models\ClientAccount $clientAccount */
        $clientAccount = \app\models\ClientAccount::findOne($this->client['id']);

        $from = new DateTime($d['year'].'-'.$d['mon'].'-01', $clientAccount->timezone);
        $from->setTimezone(new DateTimeZone('UTC'));
        $from = $from->format('Y-m-d H:i:s');

        $toYear = $d['year'];
        $toMon = $d['mon'] + 1;
        if ($toMon > 12) {
            $toMon = 1;
            $toYear += 1;
        }

        $to = new DateTime($toYear.'-'.$toMon.'-01', $clientAccount->timezone);
        $to->setTimezone(new DateTimeZone('UTC'));
        $to = $to->format('Y-m-d H:i:s');

        $W = " number_service_id = '".$this->service['id']."' ";
        $W .= " and connect_time >= '".$from."' ";
        $W .= " and connect_time < '".$to."' ";
        $W .= " and abs(cost) > 0.00001 ";

        $res = $pg_db->AllRecords($q='
            select
                case destination_id <= 0 when true then
                    case mob when true then 5 else 4 end
                else destination_id end rdest,
                cast( - sum(cost) as NUMERIC(10,2)) as price
            from
                calls_raw.calls_raw
            where '.$W.'
            group by rdest
            having abs(cast( - sum(cost) as NUMERIC(10,2))) > 0');


        $groups = $this->tarif_previous['dest_group'];
        
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
        if($this->tarif_previous["dest_group"] > 0)
        {
            $lines["100"] = array('price' => 0);
        }else{
            if($this->tarif_previous["minpayment_local_mob"])
                $lines["5"] = array('price' => 0);

            if($this->tarif_previous["minpayment_russia"])
                $lines["1"] = array('price' => 0);

            if($this->tarif_previous["minpayment_intern"])
                $lines["2"] = array('price' => 0);

            if($this->tarif_previous["minpayment_sng"])
                $lines["3"] = array('price' => 0);
        }

        foreach ($res as $r) {
            $dest = $r['rdest'];
            if (strpos($groups, $dest) !== FALSE) $dest = '100';
            if ((int)$this->tarif_previous['minpayment_group'] +
                (int)$this->tarif_previous['minpayment_local_mob'] +
                (int)$this->tarif_previous['minpayment_russia'] +
                (int)$this->tarif_previous['minpayment_intern'] +
                (int)$this->tarif_previous['minpayment_sng']  == 0) $dest = '900';

            if (!isset($lines[$dest])) $lines[$dest] = array('price'=>0);
            $lines[$dest]['price'] += $r['price'];
        }

        if ($is7800)
            if (!$lines)
                $lines["900"] = array("price" => 0);

        uksort($lines, "cmp_calc_voip_by_dest");

        return $lines;
    }

    public function GetLinesMonth(){
        $R=ServicePrototype::GetLinesMonth();

        $is7800 = substr($this->service["E164"], 0, 4) == "7800";

        if($this->date_from && $this->date_to){
            if ($this->tarif_current['month_number'] > 0) {
                $R[] = array(
                    $this->tarif_current['currency'],
                    'Абонентская плата за телефонный номер ' . $this->service['E164'] .
                    ' с ' . date('d', $this->date_from) . ' по ' . mdate('d месяца', $this->date_to),
                    $this->GetDatePercent(),
                    $this->tarif_current['month_number'],
                    'service',
                    $this->service['service'],
                    $this->service['id'],
                    date('Y-m-d', $this->date_from),
                    date('Y-m-d', $this->date_to)
                );
            }
            if($this->service['no_of_lines']>1){
                $c=intval($this->service['no_of_lines']-1);
                if ($this->tarif_current['month_line'] > 0) {
                    $R[] = array(
                        $this->tarif_current['currency'],
                        'Абонентская плата за ' . $c . ' телефонн' . rus_fin($c, 'ую', 'ые', 'ых') .
                        ' лин' . rus_fin($c, 'ию', 'ии', 'ий') . ' к номеру ' . $this->service['E164'],
                        $this->GetDatePercent() * ($this->service['no_of_lines'] - 1),
                        $this->tarif_current['month_line'],
                        'service',
                        $this->service['service'],
                        $this->service['id'],
                        date('Y-m-d', $this->date_from),
                        date('Y-m-d', $this->date_to)
                    );
                }
            }
        }


        if($this->date_from_prev && $this->date_to_prev){
            $percentByDate = $this->GetDatePercent($this->date_from_prev, $this->date_to_prev);

            $minpayment_local_mob   = $percentByDate * $this->tarif_previous['minpayment_local_mob'];
            $minpayment_russia      = $percentByDate * $this->tarif_previous['minpayment_russia'];
            $minpayment_intern      = $percentByDate * $this->tarif_previous['minpayment_intern'];
            $minpayment_sng         = $percentByDate * $this->tarif_previous['minpayment_sng'];
            $month_min_payment      = $percentByDate * $this->tarif_previous["month_min_payment"];
            $minpayment_group       = $percentByDate * $this->tarif_previous["minpayment_group"];

            $lines=$this->calc($is7800);

            foreach ($lines as $dest => $r){
                $price = $r['price'];
                $name = '';
                $percent = 1;
                if ($dest == '4'){
                    $name = 'Превышение лимита, включенного в абонентскую плату по номеру %NUM% (местные вызовы) %PERIOD%';
                }elseif($dest == '5'){

                    if ($minpayment_local_mob > $price)
                    {
                        $price = $minpayment_local_mob;
                        $percent = $percentByDate;
                        $name = 'Минимальный платеж за звонки на местные мобильные с номера %NUM% %PERIOD%';
                    }else
                        $name = 'Плата за звонки на местные мобильные с номера %NUM% %PERIOD%';
                    
                }elseif($dest == '1'){

                    if ($minpayment_russia > $price)
                    {
                        $price = $minpayment_russia;
                        $percent = $percentByDate;
                        $name = 'Минимальный платеж за междугородные звонки с номера %NUM% %PERIOD%';
                    }else
                        $name = 'Плата за междугородные звонки с номера %NUM% %PERIOD%';

                }elseif($dest == '2'){

                    if ($minpayment_intern > $price)
                    {
                        $price = $minpayment_intern;
                        $percent = $percentByDate;
                        $name = 'Минимальный платеж за звонки в дальнее зарубежье с номера %NUM% %PERIOD%';
                    }else
                        $name = 'Плата за звонки в дальнее зарубежье с номера %NUM% %PERIOD%';

                }elseif($dest == '3'){

                    if ($minpayment_sng > $price)
                    {
                        $price = $minpayment_sng;
                        $percent = $percentByDate;
                        $name = 'Минимальный платеж за звонки в ближнее зарубежье с номера %NUM% %PERIOD%';
                    }else
                        $name = 'Плата за звонки в ближнее зарубежье с номера %NUM% %PERIOD%';

                }elseif($dest == '100'){

                    $group = array();
                    if (strpos($this->tarif_previous['dest_group'], '5') !== FALSE) $group[]='местные мобильные';
                    if (strpos($this->tarif_previous['dest_group'], '1') !== FALSE) $group[]='междугородные';
                    if (strpos($this->tarif_previous['dest_group'], '2') !== FALSE) $group[]='дальнее зарубежье';
                    if (strpos($this->tarif_previous['dest_group'], '3') !== FALSE) $group[]='ближнее зарубежье';
                    $group = implode(', ', $group);
                    if ($minpayment_group > $price)
                    {
                        $price = $minpayment_group;
                        $percent = $percentByDate;
                        $name = "Минимальный платеж за набор ($group) с номера %NUM% %PERIOD%";
                    }else
                        $name = "Плата за звонки в наборе ($group) с номера %NUM% %PERIOD%";

                }elseif($dest == '900'){

                    if ($is7800)
                    {
                        if ($month_min_payment > $price)
                        {
                            $price = $month_min_payment;
                            $percent = $percentByDate;
                            $name = "Минимальный платеж за звонки по номеру %NUM% %PERIOD%";
                        } else {
                            $name = "Плата за звонки по номеру %NUM% %PERIOD%";
                        }
                    } else {
                        $name = "Плата за звонки по номеру %NUM% (местные, междугородные, международные) %PERIOD%";
                    }

                }

                $name = str_replace('%NUM%', $this->service['E164'], $name);
                $name = str_replace('%PERIOD%', 'с '.date('d',$this->date_from_prev).' по '.mdate('d месяца',$this->date_to_prev), $name);

                if ($price > 0) {
                    $R[] = array(
                        $this->tarif_previous['currency'],
                        $name,
                        $percent,
                        $price,
                        'service',
                        $this->service['service'],
                        $this->service['id'],
                        date('Y-m-d', $this->date_from_prev),
                        date('Y-m-d', $this->date_to_prev),
                    );
                }
            }            
        }

        if($this->client["bill_rename1"] == "yes")
        foreach($R as &$l)
        {
            //Абонентская плата за телефонный номер 74956385213 с 01 по 31 января 
            //Оказанные услуги за телефонный номер 74956385213 с 01 по 30 июня, согласно Договора ??? 4743-08 от 01.10.2008 г. 

            if(strpos($l[1], "бонентская плата за") !== false)
            {
                $contractStr = BillContract::getBillItemString($this->service["client_id"], $this->date_from);
                if($contractStr)
                {
                    $l[1] = "Оказанные услуги за ".mb_substr($l[1], mb_strpos($l[1], "за ", 0, 'utf-8')+3, null, 'utf-8').$contractStr;
                }

            }

            if(strpos($l[1], "Плата за звонки по номеру") !== false)
            {
                $l[1] = str_replace("Плата", "Оказанные услуги", $l[1]).BillContract::getBillItemString($this->service["client_id"], $this->date_from_prev);
            }

            if(strpos($l[1], "Услуга местного завершения вызо") !== false)
            {
                $l[1] .= BillContract::getBillItemString($this->service["client_id"], $this->date_from);
            }

        }
        return $R;
    }

    public function getServicePreBillAmount()
    {
        $val = $this->tarif_current['month_number']*$this->GetDatePercent();
        if($this->service['no_of_lines']>1) {
            $val += $this->tarif_current['month_line']*$this->GetDatePercent()*($this->service['no_of_lines']-1);
        }
        return $val;
    }
}


class ServiceBillMonthlyadd extends ServicePrototype {
    public function GetLinesMonth() {
        if(!$this->date_from || !$this->date_to)
            return array();
        $R=ServicePrototype::GetLinesMonth();
        if ($this->service['price'] > 0) {
            $R[] = array(
                0 => $this->service['currency'],
                1 => $this->service['description'] .
                    ' с ' .
                    date('d', $this->date_from) .
                    ' по ' .
                    mdate('d месяца', $this->date_to),
                2 => $this->service['amount'] * $this->GetDatePercent(),
                3 => $this->service['price'],
                4 => 'service',
                5 => $this->service['service'],
                6 => $this->service['id'],
                7 => date('Y-m-d', $this->date_from),
                8 => date('Y-m-d', $this->date_to)
            );
        }
        return $R;
    }

}

class ServiceUsageExtra extends ServicePrototype {
    var $tarif_std = 0;
    public function LoadTarif() {
        global $db;
        $this->tarif_current=$db->GetRow('select * from tarifs_extra where id='.$this->service['tarif_id']);
    }
    public function SetMonth($month) {
        if ($this->tarif_current['period']=='year') {
            $d1=getdate(strtotime($this->service['actual_from']));
            $d2=getdate($month);
            if ($d1['mon']==$d2['mon']) {
                $d2=getdate(strtotime($this->service['actual_from'])-3600*24);
                $d2['year']++;
                return $this->SetDate(
                            mktime(0,0,0,$d1['mon'],$d1['mday'],$d1['year']),
                            mktime(0,0,0,$d2['mon'],$d2['mday'],$d2['year']));
            } else return;
        } elseif ($this->tarif_current['period']=='3mon' || $this->tarif_current['period']=='6mon') {

            $servMonthPeriod = $this->tarif_current['period']=='3mon' ? 3 : 6;

            $d1Time = strtotime($this->service['actual_from']);
            $d1=getdate($d1Time);

            $d2=getdate($month);
            $m1 = $d1['mon'] + $d1['year']*12;
            $m2 = $d2['mon'] + $d2['year']*12;

            $monthDiff = $m2-$m1;
            if (($monthDiff%$servMonthPeriod)==0) {

                if($monthDiff > 0)
                    $d1Time = strtotime("+".$monthDiff." month",$d1Time);

                $d2Time = strtotime("+".$servMonthPeriod." month -1day",$d1Time);

                $d1=getdate($d1Time);
                $d2=getdate($d2Time);

                return $this->SetDate(
                        mktime(0,0,0,$d1['mon'],$d1['mday'],$d1['year']),
                        mktime(0,0,0,$d2['mon'],$d2['mday'],$d2['year']));
            } else return;


        } elseif ($this->tarif_current['period']=='once') {
            $d1=getdate(strtotime($this->service['actual_from']));
            $d2=getdate($month);
            if ($d1['year']==$d2['year'] && $d1['mon']==$d2['mon']) {
                return $this->SetDate(
                            mktime(0,0,0,$d1['mon'],$d1['mday'],$d1['year']),
                            mktime(0,0,0,$d1['mon'],$d1['mday'],$d1['year']));
            } else return;
        } else return parent::SetMonth($month);
    }
    
    public function GetLinesMonth(){
        global $db;

        if(!$this->date_from || !$this->date_to)
            return array();
        $R=ServicePrototype::GetLinesMonth();
        if ($this->tarif_current['price'] > 0 && $this->service['amount'] > 0) {
            $v = array(
                $this->tarif_current['currency'],
                $this->tarif_current['description'],
                $this->service['amount'] * $this->GetDatePercent(),
                $this->tarif_current['price'],
                'service',
                $this->service['service'],
                $this->service['id'],
                date('Y-m-d', $this->date_from),
                date('Y-m-d', $this->date_to)
            );


            // itpark renames
            if (
                $this->tarif_current["status"] == "itpark"
            ) {

                static $cache = array();

                $clientId = $this->service["client_id"];

                if (isset($cache[$clientId])) {
                    $contracts = $cache[$clientId];
                } else {
                    $contracts = array();
                    foreach ($db->AllRecords($q = "
                        select 
                            unix_timestamp(contract_date) as date,
                            if(contract_no regexp '^[0-9]+', concat('А/',contract_no),contract_no) c_no,
                            comment
                        from 
                            client_document
                        where 
                                client_id = '" . $clientId . "'
                            and is_active =1 
                            and contract_date <= '" . date("Y-m-d", $this->date_from) . "'
                        
                        having 
                            (
                                c_no like 'А/%' 
                                and comment like '%оговор%'
                            ) or c_no not like 'А/%'
                            
                        order by id
                            ") as $l) {

                        $type = "arenda";

                        if (strpos($l["c_no"], "П/") !== false) {
                            $type = "post";
                        } elseif (strpos($l["c_no"], "Т/") !== false || strpos($l["c_no"], "T/") !== false) {
                            $type = "parking";
                        }


                        $contracts[$type] = $l;
                    }

                    $cache[$clientId] = $contracts;
                }


                if (
                    strpos($this->tarif_current["description"], "ренда") !== false
                    && strpos($this->tarif_current["description"], "помещений") !== false
                ) {
                    global $db;

                    if (isset($contracts["arenda"])) {
                        $c = $contracts["arenda"]["c_no"];
                        $v[1] .= " по Договору " .
                            (strpos($c, "A/") !== false || strpos($c, "А/") !== false ? "" : "А/") . $c . " от " . mdate('d месяца Y', $contracts["arenda"]["date"]) . "г.";
                    }
                }

                if (

                    strpos($this->tarif_current["description"], "очтовое") !== false
                    && strpos($this->tarif_current["description"], "бслуживание") !== false
                ) {
                    if (isset($contracts["post"])) {
                        $v[1] .= " по Договору " . $contracts["post"]["c_no"] . " от " . mdate('d месяца Y', $contracts["post"]["date"]) . "г.";
                    }
                }

                if (

                    strpos($this->tarif_current["description"], "онтрол") !== false
                    && strpos($this->tarif_current["description"], "территории") !== false
                ) {
                    if (isset($contracts["parking"])) {
                        $v[1] .= " по Договору " . $contracts["parking"]["c_no"] . " от " . mdate('d месяца Y', $contracts["parking"]["date"]) . "г.";
                    }
                }
            }


            if ($this->tarif_current['param_name'])
                $v[1] = str_replace('%', $this->service['param_value'], $v[1]);
            if ($this->tarif_current['period'] == 'once') {
                $v[1] .= ', ' . mdate('d', $this->date_from);
            } elseif ($this->tarif_current['period'] == 'month') {
                $v[1] .= ' с ' . mdate('d', $this->date_from) . ' по ' . mdate('d месяца', $this->date_to);
            } elseif ($this->tarif_current['period'] == 'year') {
                $v[1] .= ' с ' . mdate('d месяца Y', $this->date_from) . ' по ' . mdate('d месяца Y', $this->date_to);
            }

            if ($this->client["bill_rename1"] == "yes") {
                $v[1] .= BillContract::getBillItemString($this->service["client_id"], $this->date_from);
            }

            $R[] = $v;
        }
        return $R;
    }
}

class ServiceUsageWelltime extends ServicePrototype {
    var $tarif_std = 0;
    public function LoadTarif() {
        global $db;
        $this->tarif_current=$db->GetRow('select * from tarifs_extra where id='.$this->service['tarif_id']);
    }
    public function SetMonth($month) {
        if ($this->tarif_current['period']=='year') {
            $d1=getdate(strtotime($this->service['actual_from']));
            $d2=getdate($month);
            if ($d1['mon']==$d2['mon']) {
                $d2=getdate(strtotime($this->service['actual_from'])-3600*24);
                $d2['year']++;
                return $this->SetDate(
                            mktime(0,0,0,$d1['mon'],$d1['mday'],$d1['year']),
                            mktime(0,0,0,$d2['mon'],$d2['mday'],$d2['year']));
            } else return;
        } elseif ($this->tarif_current['period']=='3mon' || $this->tarif_current['period']=='6mon') {

            $servMonthPeriod = $this->tarif_current['period']=='3mon' ? 3 : 6;

            $d1Time = strtotime($this->service['actual_from']);
            $d1=getdate($d1Time);

            $d2=getdate($month);
            $m1 = $d1['mon'] + $d1['year']*12;
            $m2 = $d2['mon'] + $d2['year']*12;

            $monthDiff = $m2-$m1;
            if (($monthDiff%$servMonthPeriod)==0) {

                if($monthDiff > 0)
                    $d1Time = strtotime("+".$monthDiff." month",$d1Time);

                $d2Time = strtotime("+".$servMonthPeriod." month -1day",$d1Time);

                $d1=getdate($d1Time);
                $d2=getdate($d2Time);

                return $this->SetDate(
                        mktime(0,0,0,$d1['mon'],$d1['mday'],$d1['year']),
                        mktime(0,0,0,$d2['mon'],$d2['mday'],$d2['year']));
            } else return;


        } elseif ($this->tarif_current['period']=='once') {
            $d1=getdate(strtotime($this->service['actual_from']));
            $d2=getdate($month);
            if ($d1['year']==$d2['year'] && $d1['mon']==$d2['mon']) {
                return $this->SetDate(
                            mktime(0,0,0,$d1['mon'],$d1['mday'],$d1['year']),
                            mktime(0,0,0,$d1['mon'],$d1['mday'],$d1['year']));
            } else return;
        } else return parent::SetMonth($month);
    }

    public function GetLinesMonth(){
        if(!$this->date_from || !$this->date_to)
            return array();
        $R=ServicePrototype::GetLinesMonth();
        if ($this->tarif_current['price'] > 0) {
            $v = array(
                $this->tarif_current['currency'],
                $this->tarif_current['description'],
                $this->service['amount'] * $this->GetDatePercent(),
                $this->tarif_current['price'],
                'service',
                $this->service['service'],
                $this->service['id'],
                date('Y-m-d', $this->date_from),
                date('Y-m-d', $this->date_to)
            );
            if($this->tarif_current['period']=='once'){
                $v[1] .= ', '.mdate('d',$this->date_from);
            }elseif($this->tarif_current['period']=='month'){
                $v[1].=' с '.mdate('d',$this->date_from).' по '.mdate('d месяца',$this->date_to);
            } elseif ($this->tarif_current['period']=='year') {
                $v[1].=' с '.mdate('d месяца Y',$this->date_from).' по '.mdate('d месяца Y',$this->date_to);
            }
            $R[]=$v;
        }
        return $R;
    }
}

class ServiceUsageVirtpbx extends ServicePrototype {
    var $tarif_std = 1;
    public function LoadTarif() {
        global $db;
        //$this->tarif_current=$db->GetRow('select * from tarifs_virtpbx where id='.$this->service['tarif_id']);
        $this->tarif_current=get_tarif_current('usage_virtpbx',$this->service['id']);
    }

    public function SetMonth($month) {
        return parent::SetMonth($month);
    }

    public function GetLinesMonth(){
        
        $R=ServicePrototype::GetLinesMonth();
        if($this->date_from && $this->date_to && $this->tarif_current['price'] > 0){
            $v=array(
                $this->tarif_current['currency'],
                $this->tarif_current['description'],
                $this->service['amount']*$this->GetDatePercent(),
                $this->tarif_current['price'],
                'service',
                $this->service['service'],
                $this->service['id'],
                date('Y-m-d',$this->date_from),
                date('Y-m-d',$this->date_to)
            );

            //by month
            $v[1].=' с '.mdate('d',$this->date_from).' по '.mdate('d месяца',$this->date_to);

            $R[]=$v;
        }
        if($this->date_from_prev && $this->date_to_prev){
            list($data, $overrun_prev_month) = VirtpbxStat::getVpbxStatDetails($this->client['id'], $this->service['id'], $this->date_from_prev, $this->date_to_prev);

            if ($overrun_prev_month['sum_space'] > 0)
            {
                $price = $overrun_prev_month['overrun_per_gb'];
                if ($this->client['nds_zero'])
                {
                    $amount = $overrun_prev_month['sum_space']/$overrun_prev_month['overrun_per_gb'];
                } else {
                    $amount = $overrun_prev_month['sum_space']/($overrun_prev_month['overrun_per_gb']*1.18);
                }
                if ($price > 0) {
                    $v = array(
                        $this->tarif_current['currency'],
                        'Превышение дискового пространства с ' . mdate('d', $this->date_from_prev) . ' по ' . mdate('d месяца', $this->date_to_prev),
                        $amount,
                        $price,
                        'service',
                        $this->service['service'],
                        $this->service['id'],
                        date('Y-m-d', $this->date_from_prev),
                        date('Y-m-d', $this->date_to_prev)
                    );
                    $R[] = $v;
                }
            }
            if ($overrun_prev_month['sum_number'] > 0)
            {
                $price = $overrun_prev_month['overrun_per_port'];
                if ($this->client['nds_zero'])
                {
                    $amount = $overrun_prev_month['sum_number']/$overrun_prev_month['overrun_per_port'];
                } else {
                    $amount = ($overrun_prev_month['sum_number']/($overrun_prev_month['overrun_per_port']*1.18));
                }

                if ($price > 0) {
                    $v = array(
                        $this->tarif_current['currency'],
                        'Превышение количества портов с ' . mdate('d', $this->date_from_prev) . ' по ' . mdate('d месяца', $this->date_to_prev),
                        $amount,
                        $price,
                        'service',
                        $this->service['service'],
                        $this->service['id'],
                        date('Y-m-d', $this->date_from_prev),
                        date('Y-m-d', $this->date_to_prev)
                    );
                    $R[] = $v;
                }
            }
        }
        return $R;
    }

}

class ServiceUsageSms extends ServicePrototype {
    var $tarif_std = 0;
    public function LoadTarif() {
        global $db;
        $this->tarif_current=$db->GetRow('select * from tarifs_sms where id='.$this->service['tarif_id']);
    }

    public function SetMonth($month) {
        return parent::SetMonth($month);
    }

    public function GetLinesMonth(){

        global $db;

        if(!$this->date_from || !$this->date_to)
            return array();


        $R=ServicePrototype::GetLinesMonth();

        if ($this->tarif_current["per_month_price"] > 0)
        {
            $v=array(
                    $this->tarif_current['currency'],
                    "Абонентская плата за СМС рассылки, ".$this->tarif_current['description'],
                    1*$this->GetDatePercent(),
                    $this->tarif_current['per_month_price']/1.18,
                    'service',
                    $this->service['service'],
                    $this->service['id'],
                    date('Y-m-d',$this->date_from),
                    date('Y-m-d',$this->date_to)
                    );

            $v[1].=' с '.mdate('d',$this->date_from).' по '.mdate('d месяца',$this->date_to);
            $R[]=$v;
        }

        $count = $db->GetValue("SELECT sum(`count`)  FROM `sms_stat` where sender = ".$this->client["id"]." and date_hour between '".date("Y-m-d", $this->date_from_prev)."' and '".date("Y-m-d", $this->date_to_prev)."'");
        if ($count > 0 && $this->tarif_current['per_sms_price'] > 0)
        {
            $v=array(
                    $this->tarif_current['currency'],
                    "СМС рассылка, ".$this->tarif_current['description'],
                    $count,
                    $this->tarif_current['per_sms_price']/1.18,
                    'service',
                    $this->service['service'],
                    $this->service['id'],
                    date('Y-m-d',$this->date_from_prev),
                    date('Y-m-d',$this->date_to_prev)
                    );

            $v[1].=' с '.mdate('d',$this->date_from_prev).' по '.mdate('d месяца',$this->date_to_prev);
            $R[]=$v;
        }



        return $R;
    }

    public function getServicePreBillAmount()
    {
        return $this->tarif_current['per_month_price']*$this->GetDatePercent()/1.18;
    }
}

class ServiceEmails extends ServicePrototype {
    public function GetLinesMonth(){
        global $db;
        static $service_data = array();
        if(!$this->date_from || !$this->date_to)
            return array();
        $R = ServicePrototype::GetLinesMonth();

        if(!isset($service_data[$this->service['client']])){
            $a = $db->getRow($q='
                select
                    id
                from
                    emails
                where
                    client="'.trim($this->service['client']).'"
                and
                    actual_from<=FROM_UNIXTIME('.$this->date_from.')
                and
                    actual_to>=FROM_UNIXTIME('.$this->date_to.')
            ');

            $b = $db->getRow($q='
                select
                    1 as dt
                from
                    bill_monthlyadd as U
                where
                    description LIKE "Виртуальный почтовый сервер%"
                and
                    U.actual_from<=FROM_UNIXTIME('.$this->date_from.')
                and
                    U.actual_to>=FROM_UNIXTIME('.$this->date_to.')
                AND
                    U.client="'.$this->service['client'].'"
            ');

            if(!$b) // тут косяк. T.code не всегда адекватные
                $b = $db->getRow($q='
                    select
                        MAX(
                            1+
                            LEAST(actual_to,DATE("'.date('Y-m-d', $this->date_to).'"))-
                            GREATEST(actual_from,DATE("'.date('Y-m-d', $this->date_from).'"))
                        )/
                        (1+(DATE("'.date('Y-m-d', $this->date_to).'") - DATE("'.date('Y-m-d', $this->date_from).'")) ) as dt
                    from
                        usage_extra as U
                    INNER JOIN
                        tarifs_extra as T
                    ON
                        T.id = U.tarif_id
                    where
                        T.code = "mailserver"
                    and
                        U.actual_from<=FROM_UNIXTIME('.$this->date_to.')
                    AND
                        U.client="'.$this->service['client'].'"
                ');
            
            if($b && ($b['dt']>0.08)){
                $b = $b['dt']+0.05;
            }else
                $b=0;
            $service_data[$this->service['client']]
                =
            array('has_server'=>$b,'email_id'=>$a['id']);
        }

        $p = $service_data[$this->service['client']];
        if($p['has_server']>$this->getDatePercent())
            return $R;

        $price = $this->client['currency']=='RUB' ? 27 : 1;
        if($p['email_id']==$this->service['id'])
            $price=0;

        if ($price > 0) {
            $R[] = array(
                0 => $this->client['currency'],
                1 => 'Поддержка почтового ящика ' .
                    $this->service['local_part'] .
                    '@' .
                    $this->service['domain'] .
                    ' с ' .
                    date('d', $this->date_from) .
                    ' по ' .
                    mdate('d месяца', $this->date_to),
                2 => $this->getDatePercent(),
                3 => $price,
                4 => 'service',
                5 => $this->service['service'],
                6 => $this->service['id'],
                7 => date('Y-m-d', $this->date_from),
                8 => date('Y-m-d', $this->date_to)
            );
        }

        if($this->client["bill_rename1"] == "yes")
            foreach($R as &$v)
                $v[1] .= BillContract::getBillItemString($this->service["client_id"], $this->date_from);

        return $R;
    }

    public function getServicePreBillAmount()
    {
        $R = self::GetLinesMonth();
        if (!empty($R) && isset($R[0][2]) && isset($R[0][3]))
        {
            return $R[0][2]*$R[0][3];
        } 

        return 0;
    }
}

function get_all_services($client,$client_id,$filter_connecting=0,$S = array()) {        //S - ЛЮЯЯХБ ХЯЙКЧВЕМХИ.
    global $db,$writeoff_services;
    $R=array();

    foreach($writeoff_services as $service)
        if(!isset($S[$service])){
            $db->Query($q="
                select
                    A.*
                from
                    ".$service." as A
                where
                    ".($filter_connecting?"(A.status = 'connecting') and ":"")."
                    (A.client='{$client}')
                    ".($service == "usage_extra" ? "and  A.code not in ('welltime_backup','welltime_backup_no_c')" : "")."
                group by
                    A.id
            ");
            while($r=$db->NextRecord(MYSQL_ASSOC)){
                $r['service']=$service;
                $r['client_id']=$client_id;
                $R[]=$r;
            }
        }
    return $R;
}

function get_tarif_history($service,$param,$date_quoted = 'NOW()'){
    global $db;
    if ($service=="usage_ip_ports") {
        $add1='A.*,';
        $add2=' LEFT JOIN tarifs_internet as A ON A.id=log_tarif.id_tarif';
    } elseif ($service=="usage_voip"){
        $add1 ='A.*,A5.name_short tarif_local_mob_name,A1.name_short tarif_russia_name,A6.name_short tarif_russia_mob_name,A2.name_short tarif_intern_name,A3.name_short tarif_sng_name,';
        $add1.='log_tarif.id_tarif_local_mob,log_tarif.id_tarif_russia,log_tarif.id_tarif_russia_mob,log_tarif.id_tarif_intern,log_tarif.id_tarif_sng,';
        $add1.='log_tarif.dest_group,log_tarif.minpayment_group,log_tarif.minpayment_local_mob,log_tarif.minpayment_russia,log_tarif.minpayment_intern,log_tarif.minpayment_sng,';
        $add2 =' LEFT JOIN tarifs_voip as A ON A.id=log_tarif.id_tarif ';
        $add2.=' LEFT JOIN tarifs_voip as A5 ON A5.id=log_tarif.id_tarif_local_mob ';
        $add2.=' LEFT JOIN tarifs_voip as A1 ON A1.id=log_tarif.id_tarif_russia ';
        $add2.=' LEFT JOIN tarifs_voip as A6 ON A6.id=log_tarif.id_tarif_russia_mob ';
        $add2.=' LEFT JOIN tarifs_voip as A2 ON A2.id=log_tarif.id_tarif_intern ';
        $add2.=' LEFT JOIN tarifs_voip as A3 ON A3.id=log_tarif.id_tarif_sng ';
    } elseif ($service=="domains") {
        $add1='A.*,';
        $add2=' LEFT JOIN tarifs_hosting as A ON A.id=log_tarif.id_tarif';
    } elseif ($service=="usage_virtpbx") {
        $add1='A.*,';
        $add2=' LEFT JOIN tarifs_virtpbx as A ON A.id=log_tarif.id_tarif';
    } else {
                $add1 = '';
        $add2 = '';
    }

    $R=$db->AllRecords($q='
        select
            '.$add1.'log_tarif.id_user,
            log_tarif.ts,
            log_tarif.id_tarif,
            log_tarif.comment,
            log_tarif.date_activation,
            user_users.user,
            (date_activation<='.$date_quoted.') as is_actual,
            (date_activation<='.$date_quoted.'-INTERVAl 1 MONTH) as is_actual_last,
            0 as is_current,
            0 as is_previous,
            0 as is_next
        from
            log_tarif'.$add2.'
        LEFT JOIN
            user_users
        on
            user_users.id=log_tarif.id_user
        where
            log_tarif.service="'.$service.'"
        and
            log_tarif.id_service="'.$param.'"
        order by
            log_tarif.date_activation,
            log_tarif.ts,
            log_tarif.id
    ');

    $a=null;
    $b=null;
    $c=null;
    foreach($R as $k=>&$r){
        if($r['is_actual'])
            $a=$k;
        if($r['is_actual_last'])
            $b=$k;
        if(
            !$r['is_actual']
        &&
            (
                !$c
            ||
                $R[$c]['date_activation'] == $r['date_activation']
            )
        &&
            $a!==null
        ){
            $c=$k;
        }
    }
    if($a!==null)
        $R[$a]['is_current']=1;
    if($b!==null)
        $R[$b]['is_previous']=1;
    if($c!==null)
        $R[$c]['is_next']=1;
    return $R;
}
function get_block_history($service,$param) {
    global $db;
    return $db->AllRecords('select log_block.*,user_users.user from log_block '.
                'LEFT JOIN user_users on user_users.id=log_block.id_user '.
                'where log_block.service="'.$service.'" and log_block.id_service="'.$param.'" order by log_block.ts,log_block.id','');
}
function get_cpe_history($service,$param) {
    global $db;
    return $db->AllRecords('
        select
            tech_cpe.*,
            type,
            vendor,
            model,
            IF(actual_from<=NOW() and actual_to>=NOW(),1,0) as actual
        from
            tech_cpe
        INNER JOIN
            tech_cpe_models
        ON
            tech_cpe_models.id = tech_cpe.id_model
        where
            tech_cpe.service = "'.$service.'"
        and
            tech_cpe.id_service = "'.$param.'"
        order by
            id','');
}

//текущий тариф
function get_tarif_current($service,$param){
    global $db;
    if($service=="usage_ip_ports"){
        $add1='A.*,';
        $add2='LEFT JOIN
            tarifs_internet as A
        ON
            A.id=log_tarif.id_tarif';
    }elseif($service=="usage_voip"){
        $add1 ='A.*,A5.name_short tarif_local_mob_name,A1.name_short tarif_russia_name,A6.name_short tarif_russia_mob_name,A2.name_short tarif_intern_name,A3.name_short tarif_sng_name,';
        $add1.='log_tarif.id_tarif_local_mob,log_tarif.id_tarif_russia,log_tarif.id_tarif_russia_mob,log_tarif.id_tarif_intern,log_tarif.id_tarif_sng,';
        $add1.='log_tarif.dest_group,log_tarif.minpayment_group,log_tarif.minpayment_local_mob,log_tarif.minpayment_russia,log_tarif.minpayment_intern,log_tarif.minpayment_sng,';
        $add2 =' LEFT JOIN tarifs_voip as A ON A.id=log_tarif.id_tarif ';
        $add2.=' LEFT JOIN tarifs_voip as A5 ON A5.id=log_tarif.id_tarif_local_mob ';
        $add2.=' LEFT JOIN tarifs_voip as A1 ON A1.id=log_tarif.id_tarif_russia ';
        $add2.=' LEFT JOIN tarifs_voip as A6 ON A6.id=log_tarif.id_tarif_russia_mob ';
        $add2.=' LEFT JOIN tarifs_voip as A2 ON A2.id=log_tarif.id_tarif_intern ';
        $add2.=' LEFT JOIN tarifs_voip as A3 ON A3.id=log_tarif.id_tarif_sng ';
    }elseif($service=="usage_virtpbx"){
        $add1='A.*,';
        $add2='LEFT JOIN
            tarifs_virtpbx as A
        ON
            A.id=log_tarif.id_tarif';
    }else{
        $add1="";
        $add2="";
    }
    $r = $db->GetRow($q = '
        select
            '.$add1.'
            log_tarif.id_user,
            log_tarif.ts,
            log_tarif.id_tarif,
            log_tarif.comment,
            log_tarif.date_activation
        from
            log_tarif
        '.$add2.'
        where
            service="'.$service.'"
        and
            id_service="'.$param.'"
        and
            date_activation<=NOW()
        and
            id_tarif!=0
        order by
            date_activation desc,
            ts desc,
            id desc
        limit 1');

    
    if(!$r)
        return null;
    return $r;
}
//будущий тариф
function get_tarif_next($service,$param) {
    global $db;
    if ($service=="usage_ip_ports") {
        $add1='A.*,';
        $add2=' LEFT JOIN tarifs_internet as A ON A.id=log_tarif.id_tarif';
    } elseif ($service=="usage_voip"){
        $add1='A.*,';
        $add2=' LEFT JOIN tarifs_voip as A ON A.id=log_tarif.id_tarif';
    } else {
        $add1="";
        $add2="";    
    }
    $r = $db->GetRow('select '.$add1.'log_tarif.id_user,log_tarif.ts,log_tarif.id_tarif,log_tarif.comment,log_tarif.date_activation from log_tarif'.$add2.' where service="'.$service.'" and id_service="'.$param.'" and date_activation>NOW() and id_tarif!=0 order by date_activation asc,ts desc limit 1');
    if (!$r) return null;
    return $r;
}
?>
