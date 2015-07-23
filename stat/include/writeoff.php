<?php

use app\classes\BillContract;
use app\models\ClientAccount;

global $writeoff_services;
$writeoff_services=array("usage_ip_ports","usage_voip", "usage_virtpbx", "usage_extra","usage_welltime", "emails","usage_sms");

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
    public $country;
    public $tax_rate;
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

        $this->country = \app\models\ClientContract::findOne($this->client['contract_id'])->getContragent()->country;
        $this->tax_rate = ClientAccount::findOne($this->client['id'])->getTaxRate();
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

}
class ServiceUsageIpPorts extends ServicePrototype {
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
}

class ServiceUsageVirtpbx extends ServicePrototype {
    var $tarif_std = 1;
    public function LoadTarif() {
        $this->tarif_current=get_tarif_current('usage_virtpbx',$this->service['id']);
    }
}

class ServiceUsageSms extends ServicePrototype {
    var $tarif_std = 0;
    public function LoadTarif() {
        global $db;
        $this->tarif_current=$db->GetRow('select * from tarifs_sms where id='.$this->service['tarif_id']);
    }

    public function getServicePreBillAmount()
    {
        return $this->tarif_current['per_month_price']*$this->GetDatePercent() / ((100 + $this->tax_rate) /100);
    }
}

class ServiceEmails extends ServicePrototype {
    public function GetLinesMonth(){
        global $db;
        static $service_data = array();
        if(!$this->date_from || !$this->date_to)
            return array();
        $R = [];

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
                1 => Yii::t('biller', 'email_service', [
                    'local_part' => $this->service['local_part'],
                    'domain' => $this->service['domain'],
                    'date_range' => Yii::t('biller', 'date_range_full', [$this->date_from, $this->date_to], $this->country->lang),
                    'by_agreement' => ''
                ], $this->country->lang),
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
            foreach($R as &$v) {
                $contractId = ClientAccount::findOne($this->service['client_id'])->contract_id;
                if ($contract = BillContract::getLastContract($contractId, $this->date_from))
                    $v[1] .= Yii::t('biller', 'by_agreement', [
                        'contract_no' => $contract['no'],
                        'contract_date' => $contract['date']
                    ], $this->country->lang);
            }

        return $R;
    }

    public function getServicePreBillAmount()
    {
        $R = $this->GetLinesMonth();
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
        $add1 ='A.*,A5.name_short tarif_local_mob_name,A1.name_short tarif_russia_name,A6.name_short tarif_russia_mob_name,A2.name_short tarif_intern_name,';
        $add1.='log_tarif.id_tarif_local_mob,log_tarif.id_tarif_russia,log_tarif.id_tarif_russia_mob,log_tarif.id_tarif_intern,';
        $add1.='log_tarif.dest_group,log_tarif.minpayment_group,log_tarif.minpayment_local_mob,log_tarif.minpayment_russia,log_tarif.minpayment_intern,';
        $add2 =' LEFT JOIN tarifs_voip as A ON A.id=log_tarif.id_tarif ';
        $add2.=' LEFT JOIN tarifs_voip as A5 ON A5.id=log_tarif.id_tarif_local_mob ';
        $add2.=' LEFT JOIN tarifs_voip as A1 ON A1.id=log_tarif.id_tarif_russia ';
        $add2.=' LEFT JOIN tarifs_voip as A6 ON A6.id=log_tarif.id_tarif_russia_mob ';
        $add2.=' LEFT JOIN tarifs_voip as A2 ON A2.id=log_tarif.id_tarif_intern ';
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
        $add1 ='A.*,A5.name_short tarif_local_mob_name,A1.name_short tarif_russia_name,A6.name_short tarif_russia_mob_name,A2.name_short tarif_intern_name,';
        $add1.='log_tarif.id_tarif_local_mob,log_tarif.id_tarif_russia,log_tarif.id_tarif_russia_mob,log_tarif.id_tarif_intern,';
        $add1.='log_tarif.dest_group,log_tarif.minpayment_group,log_tarif.minpayment_local_mob,log_tarif.minpayment_russia,log_tarif.minpayment_intern,';
        $add2 =' LEFT JOIN tarifs_voip as A ON A.id=log_tarif.id_tarif ';
        $add2.=' LEFT JOIN tarifs_voip as A5 ON A5.id=log_tarif.id_tarif_local_mob ';
        $add2.=' LEFT JOIN tarifs_voip as A1 ON A1.id=log_tarif.id_tarif_russia ';
        $add2.=' LEFT JOIN tarifs_voip as A6 ON A6.id=log_tarif.id_tarif_russia_mob ';
        $add2.=' LEFT JOIN tarifs_voip as A2 ON A2.id=log_tarif.id_tarif_intern ';
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
