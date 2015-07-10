<?php

use app\classes\Utils;
use app\models\ClientAccount;
use app\models\Organization;

define('CLIENTS_SECRET','ZyG,GJr:/J4![%qhA,;^w^}HbZz;+9s34Y74cOf7[El)[A.qy5_+AR6ZUh=|W)z]y=*FoFs`,^%vt|6tM>E-OX5_Rkkno^T.');
define('UDATA_SECRET','}{)5PTkkaTx]>a{U8_HA%6%eb`qYHEl}9:aXf)@F2Tx$U=/%iOJ${9bkfZq)N:)W%_*Kkz.C760(8GjL|w3fK+#K`qdtk_m[;+Q;@[PHG`%U1^Qu');

function printdbg($param,$s=''){
        echo "<br><pre>$s=";print_r($param);echo "</pre><br>";
};

function tabledbg($array, $title=false)
{
    if($title !== false)
    {
        echo "<div><fieldset><legend>".$title."</legend>";
    }
    echo "<table cellpadding=3  border=1 " .
        "style=\"border: 1px solid gray; border-collapse: collapse; color: black;\">";

    $keys = array_keys($array);
    $firstValue = $array[$keys[0]];

    echo "<tr><td>&nbsp;</td>";
    foreach ($firstValue as $key =>$value) {
        echo "<td><b>".$key."</b></td>";
    }
    echo "</tr>";

    foreach ($array as $key => $value) {
        echo "<tr><td><b>".$key."</b></td>";
        foreach ($value as $subvalue) {
            echo "<td>";
            if(is_array($subvalue))
            {
                tabledbg($subvalue, "Array");
            }else{
                echo $subvalue;
            }
            echo "</td>";

        }
        echo "</tr>";
    }
    echo "</table>";
    if($title !== false)
    {
        echo "</fieldset></div>";
    }

}

function printdbgu($param, $s="")
{
    ob_start();
    print_r($param);
    $s = ob_get_contents();
    ob_end_clean();

    echo "<br><pre>(<i> printdbg _utf8() </i>) ".$s."=";
    echo htmlspecialchars_($s);
    echo "</pre><br>";

}

function print1Cerror(&$e)
{
    $a = explode("|||",$e->getMessage());
    trigger_error2("<br><font style='color: black;'>1C: <font style='font-weight: normal;'>".$a[0]."</font></font>");
    trigger_error2("<font style='color: black; font-weight: normal;font-size: 8pt;'>".$a[1]."</font>");
}

function trigger_array($p,$s='') {
    trigger_error2($s.'<pre>'.htmlspecialchars_(print_r($p,1)).'</pre>');
}
function trigger_string($p) {
    trigger_error2(htmlspecialchars_(print_r($p,1)));
       return $p;
}
function str_protect($str){
    global $db;
    if(is_array($str)) return $str;
    //вроде как, те 2 строчки лишние
    $str=str_replace("\\","\\\\",$str);
    $str=str_replace("\"","\\\"",$str);
    return $db->escape($str);
};
function str_normalize($str){
    $str=str_replace(array("\r","&"),array("","&amp;"),$str);
    $str=str_replace(array("<",">"),array("&lt;","&gt;"),$str);
    $str=str_replace("\n","<br>",$str);
    return $str;
};
function digits($str){
    return preg_replace('/[^0-9]/','',$str);
}
function get_param_integer($name,$default = 0,$allowSession = true) {
    if (isset($_GET[$name])){
        $t=$_GET[$name];
    } else if (isset($_POST[$name])){
        $t=$_POST[$name];
    } else if (isset($_COOKIES[$name])){
        $t=$_COOKIES[$name];
    } else if ($allowSession && isset($_SESSION[$name])){
        $t=$_SESSION[$name];
    } else {
        return $default;
    }
    return intval($t);
}
function get_param_raw($name,$default = '',$allowSession = true) {
    if (isset($_GET[$name])){
        $t=$_GET[$name];
    } else if (isset($_POST[$name])){
        $t=$_POST[$name];
    } else if (isset($_COOKIES[$name])){
        $t=$_COOKIES[$name];
    } else if ($allowSession && isset($_SESSION[$name])){
        $t=$_SESSION[$name];
    } else {
        return $default;
    }
    return $t;
};
function get_param_protected($name,$default = '',$allowSession = true) {
    if (isset($_GET[$name])){
        $t=$_GET[$name];
    } else if (isset($_POST[$name])){
        $t=$_POST[$name];
    } else if (isset($_COOKIES[$name])){
        $t=$_COOKIES[$name];
    } else if ($allowSession && isset($_SESSION[$name])){
        $t=$_SESSION[$name];
    } else {
        return $default;
    }

    return str_protect($t);
};

function array_print($arr,$format){
    $s=$format;
    while (preg_match("/%([^%]*)%/",$s,$m)){
        $R[]=$m[1];
        $s=str_replace("%".$m[1]."%","",$s);
    };
    $p=0;
    foreach ($arr as $i=>$val){
        if ($arr[$i]){
            $p++;
            $s=$format;
            for ($j=0;$j<count($R);$j++){
                switch ($R[$j]){
                case "+":$s=str_replace("%+%",$p,$s); break;
                case "": $s=str_replace("%%","%",$s); break;
                default: $s=str_replace("%".$R[$j]."%",$val[$R[$j]],$s);
                };
            };
            echo $s;
        };
    };
};
function array_print_double($arr,$format,$preformats){
    $s=$format;
    while (preg_match("/%([^%]*)%/",$s,$m)){
        $R[]=$m[1];
        $s=str_replace("%".$m[1]."%","",$s);
    };
    $p=0;

    foreach ($arr as $i=>$val){
        if ($arr[$i]){
            foreach ($preformats as $k=>$v){
                $p=$val[$v[0]];
                $val[$k]=array_format($val[$v[0]],$v[1]);
            }
            $p++;
            $s=$format;
            for ($j=0;$j<count($R);$j++){
                switch ($R[$j]){
                case "+":$s=str_replace("%+%",$p,$s); break;
                case "": $s=str_replace("%%","%",$s); break;
                default: $s=str_replace("%".$R[$j]."%",$val[$R[$j]],$s);
                }
            }
            echo $s;
        }
    }
}

function array_format($arr,$format){
    $r="";
    $s=$format;
    while (preg_match("/%([^%]*)%/",$s,$m)){
        $R[]=$m[1];
        $s=str_replace("%".$m[1]."%","",$s);
    };
    $p=0;
    foreach ($arr as $i=>$val){
        if ($arr[$i]){
            $p++;
            $s=$format;
            for ($j=0;$j<count($R);$j++){
                switch ($R[$j]){
                case "+":$s=str_replace("%+%",$p,$s); break;
                case "": $s=str_replace("%%","%",$s); break;
                default: $s=str_replace("%".$R[$j]."%",$val[$R[$j]],$s);
                };
            };
            $r.=$s;
        };
    };
    return $r;
};

function getmicrotime(){
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
};

function time_get($measure_id=0){
    global $G;
    return (getmicrotime()-$G['time_start_'.$measure_id]);
}

function time_finish($measure_id=0){
    global $G;
    if (!isset($G['time_finish_'.$measure_id])) $G['time_finish_'.$measure_id]=getmicrotime();
    return ($G['time_finish_'.$measure_id]-$G['time_start_'.$measure_id]);
}

function time_start($measure_id=0){
    global $G;
    $G['time_start_'.$measure_id]=getmicrotime();
    if (isset($G['time_finish_'.$measure_id])) unset($G['time_finish_'.$measure_id]);
}


function _tocorrect($v1,$v2){
    if (strlen($v1)!=strlen($v2)) return 1;
    return 0;
}

function netmask_to_net_sum($mask,&$ip,&$sum,&$ip_max){
    if (!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/",$mask,$m)) return;

    $ip="{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}";
    $sum=1;
    if (!isset($m[6]) || !$m[6]) $m[6]=32;
    for ($i=$m[6];$i<32;$i++){
        $sum*=2;
    }
    $n=$m;
    $n[4]+=($sum-1);
    $k=4;
    if ($n[4]>=256) {$n[3]+=(int)($n[4]/256); $n[4]=$n[2]%256; $k=3;}
    if ($n[3]>=256) {$n[2]+=(int)($n[3]/256); $n[3]=$n[2]%256; $k=2;}
    if ($n[2]>=256) {$n[1]+=(int)($n[2]/256); $n[2]=$n[2]%256; $k=1;}
    $ip_max="{$n[1]}.{$n[2]}.{$n[3]}.{$n[4]}";
}

function netmask_to_ip_sum($mask){
    if (!preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/",$mask,$m)) return;
    $sum=1;
    if (isset($m[6]) && $m[6]) for ($i=$m[6];$i<32;$i++) $sum*=2;
    return array(256*(256*(256*$m[1]+$m[2])+$m[3])+$m[4], $sum);
}

function day_norm($m,$d,$y){
    $i=0; while (!checkdate($m,$d,$y) && ($i<5)) {$d--; $i++; }
    return $d;
}

function password_gen($len = 15, $isStrong = true){
    mt_srand((double) microtime() * 1000000);
    if ($isStrong)
    {
        $pass = preg_replace('/[^a-zA-Z0-9]/', '', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand())));
    } else {
        $pass = md5(mt_rand().mt_rand().mt_rand().mt_rand().mt_rand());
    }
    return substr($pass,0,$len);
}


function _provide_sort_thefunc($a,$b){
  global $fld,$sso,$fld2;
  if ($a[$fld]==$b[$fld]) {
    $v=($a[$fld2]>$b[$fld2])?1:-1;
  } else {
    $v=($a[$fld]>$b[$fld])?1:-1;
  }
  return ($sso?$v:(-$v));
}
function provide_sort(&$array,$sort,$so,$f,$f2){
    global $fld,$fld2,$sso;

    $fld=$f[1]; $fld2=$f2; $sso=$so;
    if (isset($f[$sort])) $fld=$f[$sort];
    uasort($array,'_provide_sort_thefunc');
    unset($fld); unset($fld2); unset($sso);
}
function add_ip(&$arr, $ip,$client=''){
    if (preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/",$ip,$m) && ($m[1]!=0)){
        if (!isset($m[6]) || ($m[6]==32)) {
            $arr["{$m[1]}.{$m[2]}.{$m[3]}.{$m[4]}"]=$client;
        } else {
            $arr["{$m[1]}.{$m[2]}.{$m[3]}.".($m[4]+1)]=$client;
            $arr["{$m[1]}.{$m[2]}.{$m[3]}.".($m[4]+2)]=$client;
        }
    }
}

function bytes_to_kb($v){
    $v=round($v/102.4)/10;
    return $v;
}

function convert_date($date){

$date_=explode("-",$date);
$date=$date_[2].".".$date_[1].".".$date_[0]."г.";
return $date;
}
function round_dig($row)
{
    $out=array();
    foreach($row as $key=>$item)
    {
        if (is_numeric($item)) $item=round($item,2);
        $out[$key]=$item;

    };
    return $out;
}

function data_decode($data){
  $di=substr($data,strlen($data)-1,1);
  $data=substr($data,0,strlen($data)-1);
  if (($di<'0') || ($di>'9')){
    $di=10+ord($di)-ord('a');
    if ($di>=16) $di=0;
  } else $di=ord($di)-ord('0');

  $data=base64_decode($data); //urldecode($data));
  $data2=""; $key=CLIENTS_SECRET; $l2=strlen($key);
  for ($i=0;$i<strlen($data);$i++){
    $data2.= chr((ord($data[$i])+256-ord($key[($i+$di)%$l2]))%256);
  }
  return $data2;
}
function data_encode($data){
  $d=substr(md5($data),0,1);
  if (($d<'0') || ($d>'9')){
    $di=10+ord($d)-ord('a');
    if ($di>=16) $di=0;
  } else $di=ord($d)-ord('0');
  $data2=""; $key=CLIENTS_SECRET; $l2=strlen($key);
  for ($i=0;$i<strlen($data);$i++){
    $v=(ord($data[$i])+ord($key[($i+$di)%$l2]))%256;
    $data2.=chr($v);
  }
  return urlencode(base64_encode($data2).$d);
}

function udata_decode($data){
    $di=substr($data,strlen($data)-1,1);
    $data=substr($data,0,strlen($data)-1);
    if (($di<'0') || ($di>'9')){
        $di=10+ord($di)-ord('a');
        if ($di>=16) $di=0;
    } else $di=ord($di)-ord('0');

    $data=base64_decode($data);
    $data2=""; $key=UDATA_SECRET; $l2=strlen($key);
    for ($i=0;$i<strlen($data);$i++){
        $data2.= chr((ord($data[$i])+256-ord($key[($i+$di)%$l2]))%256);
    }
    return $data2;
}
function udata_encode($data){
    $d=substr(md5($data),0,1);
    if (($d<'0') || ($d>'9')){
        $di=10+ord($d)-ord('a');
        if ($di>=16) $di=0;
    } else $di=ord($d)-ord('0');
    $data2=""; $key=UDATA_SECRET; $l2=strlen($key);
    for ($i=0;$i<strlen($data);$i++){
        $v=(ord($data[$i])+ord($key[($i+$di)%$l2]))%256;
        $data2.=chr($v);
    }
    return urlencode(base64_encode($data2).$d);
}
function udata_encode_arr($arr) {
    $s='';
    foreach ($arr as $k=>$v) {
        if ($s) $s.='|';
        $s.=$k.'='.$v;
    }
    return udata_encode($s);
}
function udata_decode_arr($data) {
    $v=explode('|',udata_decode($data));
    if (!count($v)) return null;
    $R=array(); foreach ($v as $vi) {
        $vi=explode('=',$vi);
        if (count($vi)==2) $R[$vi[0]]=$vi[1];
    }
    return $R;
}
function mask_match($ip,$mask){
    if (!(preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/",$mask,$m))) return 0;
    if (!(preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)(\/(\d+))?/",$ip,$m2))) return 0;
    $v=array();
    if (!isset($m[6]) || ($m[6]==32)){
        $v[0]=$m;
        $v[1]=$m;
    } else {
        $v[0]=$m;
        for ($i=32,$p=1;$i>$m[6];$i--) $p=$p*2;
        $m[4]+=$p-1;

        if ($m[4]>=256) {$p=floor($m[4]/256); $m[4]=$m[4]%256; $m[3]+=$p;}
        if ($m[3]>=256) {$p=floor($m[3]/256); $m[3]=$m[3]%256; $m[2]+=$p;}

        $v[1]=$m;
    }
    for ($i=1;$i<=4;$i++) if (($m2[$i]<$v[0][$i]) || ($m2[$i]>$v[1][$i])) return 0;
    return 1;
}

function dateReplaceMonth($string,$nMonth){
    $p=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
    $string=str_replace('месяца',$p[$nMonth-1],$string);
    $p=array('январе','феврале','марте','апреле','мае','июне','июле','августе','сентябре','октябре','ноябре','декабре');
    $string=str_replace('месяце',$p[$nMonth-1],$string);
    $p=array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
    $string=str_replace('Месяц',$p[$nMonth-1],$string);
    $p=array('январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
    $string=str_replace('месяц',$p[$nMonth-1],$string);
    return $string;
}

function mdate($format,$ts=0){
    if ($ts) $s=date($format,$ts); else $s=date($format);
    if ($ts) $d=getdate($ts); else $d=getdate();
    return dateReplaceMonth($s, $d['mon']);
}

class util{
    public static function pager($pref = "")
    {
        global $db, $design;
        $page = get_param_integer("page", 1);
        $count = $db->GetRow("select found_rows() as count");
        $count = $count["count"];
        $countPages = ceil($count/50);
        $url = "";
        foreach($_GET as $k => $v) {
            $url .= ($url ? "&" : "").$k."=".$v;
        }
        $url = "./?".$url;


        $start = $page > 10 ? $page-10 : 1;
        $end = $page +10;
        if($end >= $countPages) {
            $end = $countPages;
        }


        $pages = array();
        for($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if($end < $countPages) {
            $pages[] = $countPages;
        }
        $pref = ($pref ? $pref."_":"");
        $design->assign($pref."pager_all", $count);
        $design->assign($pref."pager_pages", $pages);
        $design->assign($pref."pager_url", $url);
        $design->assign($pref."pager_page", $page);
    }

    public static function pager_pg($count, $items_on_page = 50)
    {
        global $design;
        $page = get_param_integer("page", 1);
        $countPages = ceil($count/$items_on_page);
        $url = "./?" . http_build_query($_GET);

        $start = $page > 10 ? $page-10 : 1;
        $end = $page +10;
        if($end >= $countPages) {
            $end = $countPages;
        }


        $pages = array();
        for($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if($end < $countPages) {
            $pages[] = $countPages;
        }
        $design->assign("pager_pages", $pages);
        $design->assign("pager_url", $url);
        $design->assign("pager_page", $page);
        $design->assign("pager_all", $count);
        $design->assign("pager_page_size", $items_on_page);
    }
}

function get_inv_date_period($date) {
    $d=getdate($date);
    $v = mktime(0,0,0,$d['mon'],1,$d['year']);
    return $v;
}

function get_inv_date($date, $source) {
    $d = getdate($date);
    $v = mktime(0, 0, 0, $d['mon'], 1, $d['year']);
    if ($source != 1) {
        $d['mon']--;
        if (!$d['mon']) {
            $d['year']--;
            $d['mon']=12;
        }
    }
    if ($source == 3) {
        $tm = $date;
    }
    else {
        $tm = mktime(0, 0, 0, $d['mon'], cal_days_in_month(CAL_GREGORIAN, $d['mon'], $d['year']), $d['year']);
    }

    return [$tm, $v];
}

function get_inv_period($date) {
    $d = getdate($date);
    return mktime(0, 0, 0, $d['mon'], 1, $d['year']);
}

function debug_arr($V) {
    $R=array();
    foreach ($V as $vk=>$v) {
        if (isset($v['in_sum']) && $v['in_sum']==0) continue;
        foreach ($v as $k=>$val) if (is_numeric($k)) unset($v[$k]);
        $R[$vk]=$v;
    }
    echo "<pre>";
    print_r($R);
    echo "</pre>";
}
function param_load_date($prefix,$default_date,$returnString = false){
    global $design;
    $d=get_param_integer($prefix.'d',$default_date['mday']);
    $m=get_param_integer($prefix.'m',$default_date['mon']);
    $y=get_param_integer($prefix.'y',$default_date['year']);

    $i=0; while (!checkdate($m,$d,$y) && ($i<4)) {$d--; $i++;}    //ВРНАШ МЕ АШКН 30ЦН ТЕБПЮКЪ

    $design->assign($prefix.'d',$d);
    $design->assign($prefix.'m',$m);
    $design->assign($prefix.'y',$y);
    $t = mktime(0,0,0,$m,$d,$y);
    if ($returnString) $t = date('Y-m-d',$t);
    return $t;
}
function time_period($v) {
    $s = '';
    $d=floor($v/86400);
    $v=$v%86400;
    $h=floor($v/3600);
    $v=$v%3600;
    $m=floor($v/60);
    $v=$v%60;
    if ($d) {
        return sprintf('%dд %02d:%02d',$d,$h,$m);
    } elseif ($h && $m) {
        return sprintf('%02d:%02d',$h,$m);
    } elseif ($h && !$m) {
        return $h.' час'.Utils::rus_plural($h,'','а','ов');
    } else {
        return     sprintf('0:%02d',$m);
    }
    $s.=sprintf('%02d',floor($v/60)).($s?'':' мин');
    return $s;
}
function debug_table($str) {
    global $db;
    if (!defined('DEBUG_TABLE') || DEBUG_TABLE=="") return;
    $db->Query('insert into '.DEBUG_TABLE.' (ts,text) VALUES (NOW(),"'.addslashes($str).'")',0);
}
if( !function_exists('imageantialias') ) {
	function imageantialias($v1,$v2) {
		return true;
	}
}

class Percenter {
    public $Total;
    public $Value;
    private $last_percent = -1;
    public function __construct($Total = 0, $Value = 0) {
        $this->Total = $Total;
        $this->Value = $Value;
    }
    public function Progress() {
        $this->Value++;
        $this->Write();
    }
    public function Write() {
        $percent = round(100.0*$this->Value / $this->Total,2);
        if ($percent==$this->last_percent) return;
        $this->last_percent = $percent;
        echo "\r".sprintf("%6.2f%%",$percent);
    }
}

    function GetUserName($id)
    {
        global $db;

        $u = $db->GetRow("select name from user_users where id = '".$id."'");
        return $u ? $u["name"] : false;
    }

class ClientCS {
    public $P;
    public $F = array();
    public $D;

    function ClientCS ($id = null, $get_params = false) {
        if ($id) $this->F['id']=$id;
        if ($get_params) {
            if (is_array($get_params)) {
                $this->P = $get_params;
            } else {
                $L="client,currency,credit,password,address_post,address_connect,phone_connect,sale_channel," .
                        "address_post_real,bik,bank_properties," .
                        "usd_rate_percent,login,form_type,stamp,nal,id_all4net,".
                        "user_impersonate,dealer_comment,metro_id,payment_comment,previous_reincarnation,corr_acc,pay_acc,bank_name,bank_city,".
                        "price_type,voip_credit_limit,voip_disabled,voip_credit_limit_day,nds_zero,voip_is_day_calc,mail_print,mail_who,".
                        "head_company,head_company_address_jur,region,okpo,bill_rename1,is_bill_only_contract,is_bill_with_refund,".
                        "is_with_consignee,consignee,is_agent,is_upd_without_sign,timezone_name,country_id";
                $t=explode(",",$L);
                $this->P = array();
                foreach ($t as $v) $this->P[$v] = $v;
            }
            foreach ($this->P as $k=>$p) $this->F[$k]=get_param_raw($p);
            if($this->F["client"] == '') $this->F["client"] = "idNNNN";
            if($this->F["credit"] == '') $this->F["credit"] = -1;
            if($this->F["mail_print"] != "yes") $this->F["mail_print"] = "no";
            if($this->F["bill_rename1"] != "yes") $this->F["bill_rename1"] = "no";
        }
    }

    public function GetContactsFromLK($type = null) {
        global $db;
        $wh = '';
        if ($type) $wh.= ' and cc.type="'.addslashes($type).'"';
        $cc = $db->AllRecords("select cc.id, ns.status from client_contacts cc LEFT JOIN user_users u ON u.id=cc.user_id LEFT JOIN lk_notice_settings ns ON ns.client_contact_id=cc.id where cc.client_id=".$this->id. $wh." AND u.user='AutoLK' order by cc.id");
        $res = array();
        foreach ($cc as $c) $res[$c['id']] = $c['status'];
        return $res;
    }
    public function GetContracts() {
        global $db;
        $contracts = ["contract" => [], "agreement" => [], "blank" => []];

        foreach($db->AllRecords(
            "SELECT 
                client_document.*,
                user_users.user 
             from 
                client_document
             LEFT JOIN user_users ON user_users.id=client_document.user_id
             where 
                client_id=".$this->id. " 
             order by 
             client_document.id") as $c)
        {
            $contracts[$c["type"]][] = $c;
        }

        return $contracts;
    }
    public static function FetchClient($client) {
        global $db;
        if (is_array($client)) {
            $D = $client;
        } else {
            if($client instanceof \app\models\ClientAccount)
                return $client;

            $D = is_numeric($client) ? \app\models\ClientAccount::findOne($client) : \app\models\ClientAccount::find()->where(['client' => $client])->one();

        }
        return $D;
    }

    public static function Fetch($client) {
        global $db,$design;
        $c = self::FetchClient($client);
        $design->assign('contacts',$c->allContacts);
        $design->assign('contact',$c->officialContact);
        $contracts = $c->contract->allDocuments;
        if (count($contracts))
            $design->assign('contract',$contracts[count($contracts)-1]);
        $design->assign('contracts',$contracts);
        $design->assign('client',$c);
    }

    public static function FetchMain($client) {
        global $db,$design;
        $main_client = preg_replace('/^(.+)(\/)(.*)/i', '$1', $client);
        if ($main_client != $client) {
            $D = self::FetchClient($main_client);
            $design->assign('main_client',$D);
        } else $design->assign('main_client',false);
    }

    private static $db2 = null;
    public static function get_db2() {
        if (!defined("EXT_SQL_HOST") || !EXT_SQL_HOST) return null;
        if (!self::$db2) self::$db2 = new MySQLDatabase(EXT_SQL_HOST,EXT_SQL_USER,EXT_SQL_PASS,EXT_SQL_DB);
        return self::$db2;
    }
    public static function findClient($up_id) {
        $db2 = self::get_db2();
        if (!$db2) return;
        $r = $db2->getRow('select * from com_client where up_id='.$up_id);
        if (!$r) return;
        return $r['id'];
    }

    private static $flds = array();
    public static function updateProperty($cid,$property,$value,$up_id,$typeIsStr = false) {
        $db2 = self::get_db2();
        if (!$db2) return;
        if (!isset(self::$flds[$property])) {
            if (!($fld = $db2->getRow('select * from com_property_type where name="'.$property.'"'))) $fld = false;
            self::$flds[$property] = $fld;
        } else $fld = self::$flds[$property];
        if ($fld) {
            $db2->Query('delete from com_property where parent_id='.$cid.' and property_type_id='.$fld['id'].' and up_id='.$up_id);
            if ($value !== null && !($typeIsStr && $value==="")) {
                if ($property=='phone') {
                    $value = preg_replace('/[^\d]/','',$value);
                }
                $db2->QueryInsert('com_property',array('parent_id'=>$cid,'property_type_id'=>$fld['id'],'up_id'=>$up_id,'value'=>$value));
            }
        }
    }
    public function exportFull() {
        global $db;
        if (!self::get_db2()) return;
        if (!($cid=self::findClient($this->id))) {
            $cid = self::get_db2()->QueryInsert('com_client',array('name'=>$this->company,'up_id'=>$this->id));
        }
        $this->exportClient($cid);
        return $cid;
    }
    public function exportClient($cid) {
        global $db;
        if (!$cid) return false;

        $db2 = self::get_db2();
        if($db2)
            $db2->QueryUpdate('com_client','id',array('id'=>$cid,'name'=>$this->company,'group_id'=>EXT_GROUP_ID));
        self::updateProperty($cid,'address',$this->address_post,-$this->id);
        self::updateProperty($cid,'name',$this->company,-$this->id);

        if ($this->sale_channel) $r = $db->getRow('select name from sale_channels where id='.$this->sale_channel);
        self::updateProperty($cid,'sale_channel',$this->sale_channel?$r['name']:null,-$this->id);

        if (isset($this->status)) self::updateProperty($cid,'status',$this->status,-$this->id,true);
        self::updateProperty($cid,'manager',$this->manager,-$this->id,true);
        //self::updateProperty($cid,'support',$this->support,-$this->id,true);
        //self::updateProperty($cid,'telemarketing',$this->telemarketing,-$this->id,true);
        return true;
    }

    function _lf_diff($a, $b)
    {
        $a = preg_replace("/\&[^;]{3,7};/", "|", trim($a));
        $b = preg_replace("/\&[^;]{3,7};/", "|", trim($b));

        return $a == $b;
    }
    function GetDB($f) {
        global $db;
        $this->D = $db->GetRow("select * from clients where (".$f."='".addslashes($this->F[$f])."')");
        return (is_array($this->D) && count($this->D));
    }
    function __get($k) { return isset($this->F[$k])?$this->F[$k]:null; }
    function __set($k,$v) { $this->F[$k] = $v; }
    function __isset($k) { return isset($this->F[$k]); }
    function __unset($k) { unset($this->F[$k]); }

    private static function sendBillingCountersNotification($clientId)
    {
        $subj = '[stat/include/util] База биллинга телефонии не доступна';
        $c = \app\models\ClientAccount::findOne([is_numeric($clientId) ? 'id' : 'client' => ($clientId)]);
        $body = 'Клиент ' . $c->client . ' не получил информацию по биллингу';
        //mail(ADMIN_EMAIL, $subj, $body);
    }
    public static function getBillingCounters($clientId, $silent_mode = false)
    {
        global $pg_db,$db;

        $counters = array('amount_sum'=>0, 'amount_day_sum'=>0,'amount_month_sum'=>0);

        try{

            $counters_reg = $pg_db->GetRow("SELECT  CAST(amount_sum as NUMERIC(8,2)) as amount_sum,
                                                CAST(amount_day_sum as NUMERIC(8,2)) as amount_day_sum,
                                                CAST(amount_month_sum as NUMERIC(8,2)) as amount_month_sum
                                        FROM billing.counters
                                        WHERE client_id='".$clientId."'");

            if (!empty($counters_reg)) {
                $db->Query('INSERT INTO client_counters(client_id, amount_sum, amount_day_sum, amount_month_sum) VALUES ('.$clientId.', '.$counters_reg['amount_sum'].','.$counters_reg['amount_day_sum'].','.$counters_reg['amount_month_sum'].')
                            ON DUPLICATE KEY UPDATE amount_sum = '.$counters_reg['amount_sum'].', amount_day_sum = '.$counters_reg['amount_day_sum'].', amount_month_sum = '.$counters_reg['amount_month_sum']);
            } else {
                $db->Query('DELETE FROM client_counters WHERE client_id = '.$clientId);
                $counters_reg = array('amount_sum'=>0, 'amount_day_sum'=>0,'amount_month_sum'=>0);
            }

        }catch(Exception $e)
        {
            if (!$silent_mode)
            {
                trigger_error2("База биллинга телефонии не доступна");
            }
            self::sendBillingCountersNotification($clientId);
        }

        if (!isset($counters_reg)) {
            $counters_reg = $db->GetRow('SELECT * FROM client_counters WHERE client_id = ' . $clientId);
            if (empty($counters_reg)) {
                $counters_reg = array('amount_sum'=>0, 'amount_day_sum'=>0,'amount_month_sum'=>0);
            }
        }

        $counters['amount_sum'] = $counters_reg['amount_sum'];
        $counters['amount_day_sum'] = $counters_reg['amount_day_sum'];
        $counters['amount_month_sum'] = $counters_reg['amount_month_sum'];

        return $counters;
    }

    public static function getVoipPrefix($regionId = 0)
    {
        switch($regionId) {
            case '99':
                return array('499','495');
            break;
            case '97':
                return array('861');
            break;
            case '98':
                return array('812');
            break;
            case '95':
                return array('343');
            break;
            case '96':
                return array('846');
            break;
            case '94':
                return array('383');
            break;
            case '87':
                return array('863');
            break;
        }
        return array();
    }
}

function iplist_make($D) {
    $R = array();
    foreach ($D as $d) $R[] = netmask_to_ip_sum($d);
    return $R;
}
function iplist_check($R,$ip) {
    foreach ($R as $r) {
        if ($ip>=$r[0] && $ip<$r[0]+$r[1]) return true;
    }
    return false;
}

class IPList{
    public $data = array();

    public function __construct(){
        global $db;
        $db->Query('select * from tech_nets');
        while($r = $db->NextRecord())
            if($v = netmask_to_ip_sum($r['net'])){
                if($v[1]>65536)
                    die("tech_nets is WROONG!");
                for($i = $v[0];$i<$v[0]+$v[1];$i++){
                    $this->data[$i] = array('new',0,0, '', '');
                }
            }

        $our = iplist_make(array(
            '85.94.32.0/19',
            '89.235.128.0/18'
        ));
        $special = iplist_make(array(
            '85.94.50.0/23',
            '85.94.52.0/23',
            '89.235.134.0/24',
            '89.235.136.0/24',
            '85.94.33.0/24',
            '85.94.34.0/24',
            '85.94.35.0/24',
            '85.94.48.0/24',
            '89.235.152.0/22',
            '89.235.160.0/23',
            '89.235.171.0/24',
            '89.235.172.0/24',
            '89.235.173.0/24',
            '89.235.174.128/25',
            '89.235.162.0/23',
            '89.235.164.0/24',
            '89.235.167.0/24',
            '89.235.176.0/24',
            '89.235.177.0/24',
            '89.235.178.0/24',
            '89.235.179.0/24',
            '89.235.180.0/24'
        ));

        $db->Query($q="
            SELECT
                `R`.`actual_from`,
                `R`.`actual_to`,
                `R`.`net`,
                `clients`.`client`,
                `clients`.`status`,
                `clients`.`manager`,
                `R`.`id`,
                `R`.`gpon_reserv`
            FROM
                `usage_ip_routes` as `R`
            INNER JOIN
                `usage_ip_ports` as `P`
            ON
                `P`.`id`=`R`.`port_id`
            INNER JOIN
                `clients`
            ON
                `clients`.`client`=`P`.`client`
            INNER JOIN
                `tech_ports`
            ON
                `tech_ports`.`id`=`P`.`port_id`
            WHERE
                `R`.`actual_from`<=`R`.`actual_to`
            AND `R`.`actual_from`<'3000-01-01'
            AND `R`.`actual_to`<>'0000-00-00'
            AND `tech_ports`.`port_type` IN ('adsl','adsl_connect','adsl_cards','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1', 'GPON')
            order by
                `R`.`actual_to` ASC, actual_from
        ");//.MySQLDatabase::Generate($Cond));

        while($r = $db->NextRecord())
        {
            $r["actual_from"] = strtotime($r["actual_from"]);
            $r["actual_to"] = strtotime($r["actual_to"]);

            if($v = netmask_to_ip_sum($r['net'])){
                if(iplist_check($our,$v[0])){

                    if($v[1]>65536)
                        die("usage_ip_routes id=".$r['id']." is WROONG!");
                    for($i = $v[0];$i<$v[0]+$v[1];$i++){

                        //cast - если была введена сеть в техотказ, а потоом она выдалась, и их даты совапдают, то сеть из техотказа не уходит.
                        if(isset($this->data[$i]) && $this->data[$i][0] == "tech" && ($r["status"] == "work" || $r["status"] == "testing") &&
                                $this->data[$i][1]==$r['actual_to'])
                            $this->data[$i][1]--;


                        if(!isset($this->data[$i]) || $this->data[$i][1]<$r['actual_to'] /*|| $bEnter*/){
                            $st = '';
                            if($r["gpon_reserv"])
                                $st = 'gpon';
                            elseif($r['status']=='tech_deny')
                                $st = 'tech';
                            elseif($r['status']=='closed' || $r['status']=='deny' || $r['actual_to']<=(time()-3600*24*30))
                                $st = 'off';
                            if($st){

                                if ($st != 'gpon')
                                    if(iplist_check($special,$v[0]))
                                        $st = 'special';

                                $this->data[$i] = array($st,$r['actual_to'],$r['id'], $r["client"], $r["manager"]);
                            }elseif(isset($this->data[$i]))
                                unset($this->data[$i]);
                        }
                    }
                }
            }
        }
            ksort($this->data);
    }
    public function getByType(){
        $R = array();
        $V = array();
        $S = array();
        foreach($this->data as $ipk=>$ipv){
            $R[$ipv[0]][$ipk] = $ipv;
        }

        foreach($R as $t => $ta){
            $V[$t] = array();
            $S[$t] = array();
            foreach($ta as $k=>$v)
                if(isset($ta[$k])){
                    if(!isset($S[$t][$v[1]])){
                        $S[$t][$v[1]] = array();
                    }

                    $r = array(
                        'actual_to'=>$v[1],
                        'id'=>$v[2],
                        'client' => $v[3],
                        'manager' => $v[4]
                    );
                    $d = 1;
                    $md = 1;
                    while(($md<16) && ($k & (pow(2,$md)-1))==0)
                        $md++;
                    $md = pow(2,$md-1);
                    while(
                            ($t != 'gpon' && isset($ta[$k+$d]) && ($d<$md)) || 
                            ($t == 'gpon' && isset($ta[$k+$d]) && ($d<$md) && ($ta[$k+$d][2] == $v[2]))
                    ) {
                        $d++;
                    }
                    $dv = floor(log($d,2));
                    $d = pow(2,$dv);
                    $V[$t][long2ip($k)] = array(32-$dv,$r['actual_to'],$r['id'],$d, $r["client"], $r["manager"]);
                    $S[$t][$v[1]][$k] = long2ip($k);
                    for($i=0;$i<$d;$i++){
                        unset($ta[$k+$i]);
                    }
                }
        }

        // sort begin
        foreach($S as $t=>&$v){
            ksort($S[$t],SORT_NUMERIC);
            foreach($v as $time=>&$ips){
                ksort($S[$t][$time],SORT_NUMERIC);
            }
        }
        $V_buf = array();
        foreach($V as $type=>&$ips){
            if(!isset($V_buf[$type]))
                $V_buf[$type] = array();
            foreach($S[$type] as $time=>&$ips){
                foreach($S[$type][$time] as $ip){
                    $V_buf[$type][$ip] =& $V[$type][$ip];
                }
            }
        }
        unset($V);
        $V =& $V_buf;

        return $V;
    }
}


#################################################
function unix_timestamp($Ymd=null){
    if(is_null($Ymd))
        $Ymd = date('Y-m-d');
    if(!preg_match('/^([0-9]{4})\-([0-9]{1,2})\-([0-9]{1,2})$/',$Ymd,$matches))
        return -1;
    return mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
}

class all4geo
{
    static function getId($billNo, $doerId, $comment, $isTrouble = false)
    {

        return false;

        global $db;
        $doerId = $db->GetValue("select all4geo from courier where id = '".$doerId."'");
        if(!$doerId) return false;

        $orderId = $isTrouble ? "t".$billNo : $billNo;

        //doer
        $u="http://www.all4geo.com/service.php?action=update&service=49f68a5c8493ec2c0bf489821c21fc3b&courier=".$doerId."&order_id=".urlencode($orderId)."&courier_fields[comment]=".urlencode($comment);

        $pFile = fopen("/home/httpd/stat.mcn.ru/test/log.all4geo", "a+");
        fwrite($pFile, date("r").":".$u." => ".$comment."\n");
        $f = file_get_contents($u);
        fwrite($pFile, " => ".$f."\n");
        fclose($pFile);
        global $db;

        if(strpos($f,"ok") !== false){
            list($ok, $aId) = explode(",", $f);

            $db->Query($z= "update tt_troubles set doer_comment = '".$db->escape($comment)."', all4geo_id = '".$aId."' where ".($isTrouble ? " id = '".$billNo."'" : "bill_no = '".$billNo."'"));
        }else{
            $pFile = fopen("/home/httpd/stat.mcn.ru/test/log.all4geo.errors", "a+");
            fwrite($pFile, date("r").":".$u." => ".$comment." => ".$f."\n");
            fclose($pFile);
            echo "Данные сохранены, но возникла ошибка передачи данных в all4geo.";
            mail("dga@mcn.ru", "stat error: stat.all4geo", date("r").":".$u." => ".$comment." => ".$f);
            exit();
        }
    }
}

/*
class trigger{

    static private $listeners = array();
    function registerListener($event, $callback)
    {
        self::$listeners[$event][] = $callback;
    }

    function doEvent($event, $value)
    {
        $fp= fopen("/tmp/log.event", "a+");
        fwrite($fp, $event.": ".$value."\n");
        fclose($fp);

        if(isset(self::$listeners[$event]))
        {
            foreach(self::$listeners[$event] as $callback)
            {
                call_user_func($callback, $value);
            }
        }
    }
}

trigger::registerListener("stage_change", array("welltime", "sendStage"));

class welltime{
    function sendStage($troubleId)
    {
        global $db;
        $q =
                "select bill_no, st.name stage ".
                "from  tt_troubles t, tt_stages s, tt_states st ".
                "where t.id ='".($troubleId)."' and client='All4Net_new' ".
                "and s.stage_id = t.cur_stage_id and st.id = s.state_id";

        $r = $db->GetRow($q);

        $fp= fopen("/tmp/log.event", "a+");
        fwrite($fp, $q.var_export($r,true)."|".var_export($db->AllRecords($q),true)."\n");
        fclose($fp);

        if($r)
        {
            $fp= fopen("/tmp/log.event", "a+");
            fwrite($fp, "http://85.94.32.194/all4net-stages.php?bill_no=".urlencode($r["bill_no"])."&stage=".urlencode($r["stage"])."\n");
            fclose($fp);

            file_get_contents("http://85.94.32.194/all4net-stages.php?bill_no=".urlencode($r["bill_no"])."&stage=".urlencode($r["stage"]));
        }
    }
}
*/

class sender
{
    static function sendICQMsg($user, $msg)
    {
        global $db;

        $msg = str_replace("&nbsp;", " ", $msg);
        $msg = str_replace("&amp;", "&", $msg);
        $msg = str_replace(array("#171;", "#187;","&quot;"), "\"", $msg);

        $db->QueryInsert("tt_send", array(
                    "user" => $user,
                    "text" => $msg
                    )
                );

        /*
        $icq = $db->GetValue("select icq from user_users where user = '".$user."'");

        // this code to get user icq uin
        if($icq)
            send::icq($icq, $msg);
        */
    }
}

class send
{
    function icq($uin, $msg)
    {
        include_once(INCLUDE_PATH.'WebIcqLite.class.php');

        $icq = new WebIcqLite();
        if($icq->connect('661127544', 'eiS8vaimD#'))
        {
            if(!$icq->send_message($uin, iconv("utf-8", "cp1251", $msg)))
            {
                echo $icq->error;
            }else{
                echo 'Message sent';
            }
            $icq->disconnect();
        }else{
            echo $icq->error;
        }
    }

}

class event
{
    public static function go($event, $param = "", $isForceAdd = false)
    {
        if (is_array($param))
        {
            //$param = serialize($param);
            $param = json_encode($param);
        }

        $code = md5($event."|||".$param);

        $row = null;
        if (!$isForceAdd)
        {
            $row = EventQueue::first(['conditions' => ["code = ? and status not in (?, ?)", $code, "ok", "stop"]]);
        }

        if (!$row)
        {
            $row = new EventQueue();
            $row->event = $event;
            $row->param = $param;
            $row->code = $code;
        } else {
            $row->iteration = 0;
            $row->status = 'plan';
        }
        $row->save();
    }

    public static function setReject($bill, $state)
    {
        if($bill["client_id"] == 15701)
        {
            global $db;

            mail("dga@mcn.ru", "MCN заявака в отказ", "Заявка #".$db->GetValue("select concat(req_no,'/',bill_no) from newbills_add_info  where bill_no = '".$bill["bill_no"]."'")." переведенна на этап \"отказ\"","Content-Type: text/plain; charset = \"utf-8\"\nFrom: info@mcn.ru");

            mail("shop@nbn-holding.ru", "MCN заявака в отказ", "Заявка #".$db->GetValue("select concat(req_no,'/',bill_no) from newbills_add_info  where bill_no = '".$bill["bill_no"]."'")." переведенна на этап \"отказ\"","Content-Type: text/plain; charset = \"utf-8\"\nFrom: info@mcn.ru");
        }
    }
}

function htmlspecialchars_($s)
{
    // migration php 5.3 => 5.5
    return htmlspecialchars($s, ENT_COMPAT, "UTF-8");
}


