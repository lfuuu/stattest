<?
define('CLIENTS_SECRET','ZyG,GJr:/J4![%qhA,;^w^}HbZz;+9s34Y74cOf7[El)[A.qy5_+AR6ZUh=|W)z]y=*FoFs`,^%vt|6tM>E-OX5_Rkkno^T.');
define('UDATA_SECRET','}{)5PTkkaTx]>a{U8_HA%6%eb`qYHEl}9:aXf)@F2Tx$U=/%iOJ${9bkfZq)N:)W%_*Kkz.C760(8GjL|w3fK+#K`qdtk_m[;+Q;@[PHG`%U1^Qu');
#}

function get_payment_rate_by_bill($payment_date,$payment_sum = null,$bill_no = null) {
	global $db;
	if ($bill_no) {
		$r2 = $db->GetRow('select sum,currency from newbills where bill_no="'.$bill_no.'"');
		if ($r2['currency']=='RUR') return 1;
	}
	$r=$db->GetRow('select * from bill_currency_rate where date="'.$payment_date.'" and currency="USD"');
	$rate = $r['rate'];
	if ($bill_no) {
		$bill_sum = round($r2['sum'],2);
		$sum_rub=round($payment_sum,2);
		$rate_bill=round($sum_rub/$bill_sum,4);
		if (!$rate) $rate=$rate_bill;
		if (abs($rate_bill-$rate)/$rate <=0.03) $rate=$rate_bill;
	}
	return $rate;
}

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

    $s = iconv("utf-8", "koi8-r//ignore", $s);


    echo "<br><pre>(<i> printdbg _utf8() </i>) ".$s."=";
    echo htmlspecialchars($s);
    echo "</pre><br>";

}

function print1Cerror(&$e)
{
    $a = explode("|||",$e->getMessage());
    trigger_error("<br><font style='color: black;'>1C: <font style='font-weight: normal;'>".iconv("utf-8", "koi8-r//ignore", $a[0])."</font></font>");
    trigger_error("<font style='color: black; font-weight: normal;font-size: 8pt;'>".iconv("utf-8", "koi8-r//ignore", $a[1])."</font>");
}

function trigger_array($p,$s='') {
	trigger_error($s.'<pre>'.htmlspecialchars(print_r($p,1)).'</pre>');
}
function trigger_string($p) {
	trigger_error(htmlspecialchars(print_r($p,1)));
   	return $p;
}
function str_protect($str){
    if(is_array($str)) return $str;
	//вроде как, те 2 строчки лишние
	$str=str_replace("\\","\\\\",$str);
	$str=str_replace("\"","\\\"",$str);
	return mysql_escape_string($str);
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

function bytes_to_mb($val,$nround=2){
	if ($nround==2) $r=100; else
		for ($r=1,$i=0;$i<$nround;$i++) $r*=10;
	return round(($val)*$r/(1024*1024))/$r;
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
function get_rus_date($date=0){
	if ($date==0) $date=time();
	$d=getdate($date);
	$p=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	return $d['mday'].' '.$p[$d['mon']-1].' '.$d['year'].' г.';
}

function password_gen($len = 8,$addstr = ''){
	mt_srand((double) microtime() * 1000000);
	$pass=md5(mt_rand().mt_rand().mt_rand().mt_rand().mt_rand());
	if ($addstr) $pass=md5($pass.mt_rand().$addstr);
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

function mdate($format,$ts=0){
	if ($ts) $s=date($format,$ts); else $s=date($format);
	if ($ts) $d=getdate($ts); else $d=getdate();
	$p=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	$s=str_replace('месяца',$p[$d['mon']-1],$s);
	$p=array('январе','феврале','марте','апреле','мае','июне','июле','августе','сентябре','октябре','ноябре','декабре');
	$s=str_replace('месяце',$p[$d['mon']-1],$s);
	$p=array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
	$s=str_replace('Месяц',$p[$d['mon']-1],$s);
	$p=array('январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
	$s=str_replace('месяц',$p[$d['mon']-1],$s);
	return $s;
}
function rus_fin($v,$s1,$s2,$s3){
 	if($v==11)
		return $s3;
	if(($v%10)==1)
		return $s1;
	if(($v%100)>=11 && ($v%100)<=14)
		return $s3;
	if(($v%10)>=2 && ($v%10)<=4)
		return $s2;
	return $s3;
}
class util{
    public function pager($pref = "")
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
        global $db_pg, $design;
        $page = get_param_integer("page", 1);
        $countPages = ceil($count/$items_on_page);
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
        $design->assign("pager_pages", $pages);
        $design->assign("pager_url", $url);
        $design->assign("pager_page", $page);
        $design->assign("pager_all", $count);
    }
}


class Wordifier {
	private static $curBig=array('USD'=>array('доллар США','доллара США','долларов США'),'RUR'=>array('рубль','рубля','рублей'));
	private static $curSmall=array('USD'=>array('цент','цента','центов'),'RUR'=>array('копейка','копейки','копеек'));
	private static $num10 = array("","один ","два ","три ","четыре ","пять ","шесть ","семь ","восемь ","девять ",);
	private static $num10x = array("","одна ","две ","три ","четыре ","пять ","шесть ","семь ","восемь ","девять ",);
	private static $num20 = array("десять ","одиннадцать ","двенадцать ","тринадцать ","четырнадцать ","пятнадцать ","шестнадцать ","семнадцать ","восемнадцать ","девятнадцать ");
	private static $num100 = array("","","двадцать ","тридцать ","сорок ","пятьдесят ","шестьдесят ","семьдесят ","восемьдесят ","девяносто ");
	private static $num1000 = array("","сто ","двести ","триста ","четыреста ","пятьсот ","шестьсот ","семьсот ","восемьсот ","девятьсот " );
	private static $sections = array(array(' ',' ',' '),array('тысяча','тысячи','тысяч'),array("миллион","миллиона","миллионов"),array("миллиард","миллиарда","миллиардов"));

	private static function MakeSections($num,$sect){

		if($num>=1000){
			$s = Wordifier::MakeSections(floor($num/1000),$sect+1).' ';
			$num=$num%1000;
		}else
			$s='';
		$s .= Wordifier::$num1000[floor($num/100)];
		$num = $num%100;
		if($num>=10 && $num<=19){
			$s .= Wordifier::$num20[$num-10];
		}else{
			$s .= Wordifier::$num100[floor($num/10)];
			$num=$num%10;
			if($sect==1){
				$s .= Wordifier::$num10x[$num];
			}else{
				$s .= Wordifier::$num10[$num];
			}
		}
		$s .= rus_fin($num,Wordifier::$sections[$sect][0],Wordifier::$sections[$sect][1],Wordifier::$sections[$sect][2]);
		if($sect==0)
			return array($s,$num);
		else
			return $s;
	}

	public static function Make($num,$currency){
		$num = round($num,2);

        $isMinus = false;

        if($num < 0) {
            $num = abs($num); 
            $isMinus = true;
        }


		if(floor($num)==0)
			$v = array('ноль ',0);
		else
			$v = Wordifier::MakeSections(floor($num),0);
		$s=$v[0];

        if($isMinus)
        {
            $s = "минус ".$s;
        }

		$s=strtr(substr($s,0,1),"мнодтчпшсв","МНОДТЧПШСВ").substr($s,1);
		$s.=rus_fin($v[1],Wordifier::$curBig[$currency][0],Wordifier::$curBig[$currency][1],Wordifier::$curBig[$currency][2]);
		$c=round(($num-floor($num))*100);
		$s.=' '.sprintf("%02d", $c).' '.rus_fin($c,Wordifier::$curSmall[$currency][0],Wordifier::$curSmall[$currency][1],Wordifier::$curSmall[$currency][2]);
		return $s;
	}
}

function get_inv_date_period($date) {
	$d=getdate($date);
	$v = mktime(0,0,0,$d['mon'],1,$d['year']);
	return $v;
}
function get_inv_date($date,$source) {
	$d=getdate($date);
	$v = mktime(0,0,0,$d['mon'],1,$d['year']);
	if ($source!=1) {
		$d['mon']--;
		if (!$d['mon']) {$d['year']--; $d['mon']=12;}
	}
	if ($source==3) {
		$tm = $date;
	} else {
		$tm = mktime(0,0,0,$d['mon'],cal_days_in_month(CAL_GREGORIAN, $d['mon'], $d['year']),$d['year']);
	}
	return array($tm,$v);
}

function get_inv_period($date)
{
	$d=getdate($date);
	return mktime(0,0,0,$d['mon'],1,$d['year']);
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

	$i=0; while (!checkdate($m,$d,$y) && ($i<4)) {$d--; $i++;}	//ВРНАШ МЕ АШКН 30ЦН ТЕБПЮКЪ

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
		return $h.' час'.rus_fin($h,'','а','ов');
	} else {
		return 	sprintf('0:%02d',$m);
	}
	$s.=sprintf('%02d',floor($v/60)).($s?'':' мин');
	return $s;
}
function debug_table($str) {
	global $db;
	if (!defined('DEBUG_TABLE') || DEBUG_TABLE=="") return;
	$db->Query('insert into '.DEBUG_TABLE.' (ts,text) VALUES (NOW(),"'.addslashes($str).'")',0);
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

class Good {
    private static $goods = array();

    public function GetPrice($goodId, $priceTypeId)
    {
        global $db;

        list($goodId, $descrId) = explode(":", $goodId);

        if(!$descrId)
            $descrId = "00000000-0000-0000-0000-000000000000";

        $r = $db->GetRow("
                select * from g_good_price where good_id = '".$goodId."'
                and descr_id = '".$descrId."'
                and price_type_id ='".$priceTypeId."'");

        return $r["price"];
    }

    public function GetName($goodId){
        global $db;

        list($goodId, $descrId) = explode(":", $goodId);

        if(!$descrId)
            $descrId = "00000000-0000-0000-0000-000000000000";

        $r = $db->GetRow("select concat(g.name,if(d.name is not null,concat(' **',d.name) ,'')) name from g_goods g
                left join g_good_description d on (g.id = d.good_id and d.id = '".$descrId."')
                where g.id='".$goodId."'");
        return $r["name"];
    }

    private function _GetParam($goodId, $param){
        $g = self::_GetGood($goodId);
        return $g ? $g[$param] : false;
    }

    private function _GetGood($id) {
        global $db;

        if(!isset(self::$goods[$id])) {
            self::$goods[$id] = $db->GetRow("select * from g_goods where id = '".$id."'");
        }
        return self::$goods[$id];
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
	public static $statuses = array(
				'negotiations'		=> array('name'=>'в стадии переговоров','color'=>'#C4DF9B'),
				'testing' 			=> array('name'=>'тестируемый','color'=>'#6DCFF6'),
				'connecting' 		=> array('name'=>'подключаемый','color'=>'#F49AC1'),
				'work' 				=> array('name'=>'включенный','color'=>''),
				'closed' 			=> array('name'=>'отключенный','color'=>'#FFFFCC'),
				'tech_deny'			=> array('name'=>'тех. отказ','color'=>'#996666'),
				'telemarketing'		=> array('name'=>'телемаркетинг','color'=>'#A0FFA0'),
				'income'			=> array('name'=>'входящие','color'=>'#CCFFFF'),
				'deny'				=> array('name'=>'отказ','color'=>'#A0A0A0'),
				'debt'				=> array('name'=>'отключен за долги','color'=>'#C00000'),
				'double'			=> array('name'=>'дубликат','color'=>'#60a0e0'),
				'trash'				=> array('name'=>'мусор','color'=>'#a5e934'),
				'move'				=> array('name'=>'переезд','color'=>'#f590f3'),
				'already'			=> array('name'=>'есть канал','color'=>'#C4a3C0'),
				'denial'			=> array('name'=>'отказ/задаток','color'=>'#00C0C0'),
				'once'				=> array('name'=>'разовые','color'=>'silver'),
				'reserved'			=> array('name'=>'резервирование канала','color'=>'silver'),
				'blocked'			=> array('name'=>'временно заблокирован','color'=>'silver')
			);
	//вернёт название статуса
	public static function translate($status_code){
		if (!isset(self::$statuses[$status_code])) return $status_code;
		return self::$statuses[$status_code]['name'];
	}

	function ClientCS ($id = null, $get_params = false) {
		if ($id) $this->F['id']=$id;
		if ($get_params) {
			if (is_array($get_params)) {
				$this->P = $get_params;
			} else {
				$L="client,currency,currency_bill,credit,password,company,company_full,address_jur,address_post,address_connect,phone_connect,sale_channel," .
						"telemarketing,manager,support,address_post_real,bik,bank_properties,signer_name,signer_position,firma," .
						"usd_rate_percent,company_full,type,login,inn,kpp,form_type,stamp,nal,signer_nameV,signer_positionV,id_all4net,".
						"user_impersonate,dealer_comment,metro_id,payment_comment,previous_reincarnation,corr_acc,pay_acc,bank_name,bank_city,".
						"price_type,voip_credit_limit,voip_disabled,voip_credit_limit_day,nds_zero,voip_is_day_calc,mail_print,mail_who,".
                        "head_company,head_company_address_jur,region,okpo,bill_rename1,nds_calc_method";
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
	public function GetContacts($type = null,$onlyActive = false,$onlyOfficial = false) {
		global $db;
		$wh = '';
		if ($type) $wh.= ' and client_contacts.type="'.addslashes($type).'"';
		if ($onlyActive) $wh.= ' and client_contacts.is_active=1';
		if ($onlyOfficial) $wh.= ' and client_contacts.is_official=1';
		return $db->AllRecords("select client_contacts.*,user_users.user from client_contacts LEFT JOIN user_users ON user_users.id=client_contacts.user_id where client_id=".$this->id. $wh." order by client_contacts.id");
	}
	public function GetContact($onlyOfficial = true) {
		global $db;
		$V = $this->GetContacts(null,true,$onlyOfficial);
		$R = array('fax'=>array(),'phone'=>array(),'email'=>array());
		foreach ($V as $v) $R[$v['type']][] = $v;
		return $R;
	}
	public function GetContracts() {
		global $db;
		return $db->AllRecords("select client_contracts.*,user_users.user from client_contracts LEFT JOIN user_users ON user_users.id=client_contracts.user_id where client_id=".$this->id. " order by client_contracts.id");
	}
	public static function FetchClient($client) {
		global $db;
		if (is_array($client)) {
			$D = $client;
		} else {
			$f = (is_numeric($client)?'id':'client');
			$D = $db->GetRow("select *, client as client_orig from clients where ".$f."='".addslashes($client)."'");

		}
		return $D;
	}

	public static function Fetch($client,$ContractData=null) {
		global $db,$design;
		$D = self::FetchClient($client);
		$O = new self($D['id']);
		$design->assign('contacts',$O->GetContacts());
		$design->assign('contact',$O->GetContact());
		$contracts = $O->GetContracts();
		if ($ContractData===null) {
			if (count($contracts)) $design->assign('contract',$contracts[count($contracts)-1]);
		} else $design->assign('contract',$ContractData);
		$design->assign('contracts',$contracts);
		$design->assign('client',$D);
	}

	public function AddContact($type,$value,$comment,$is_official) {
		global $db,$user;
		$V = array('type'=>$type,'data'=>$value,'ts'=>array('NOW()'),'client_id'=>$this->id,'comment'=>$comment,'is_official'=>$is_official,'user_id'=>$user->Get('id'),'is_active'=>1);
		$id = $db->QueryInsert('client_contacts',$V);
		if ($cid=self::findClient($this->id)) {
			self::updateProperty($cid,$type=='email'?'mail':$type,$value,$id);
		}
	}
	public function ActivateContact($id,$active) {
		global $db,$user;
		$db->Query('update client_contacts set is_active="'.$active.'",ts=NOW(),user_id="'.$user->Get('id').'" where client_id="'.$this->id.'" and id="'.$id.'"');
		if ($cid=self::findClient($this->id)) {
			$d = $db->getRow('select type,data from client_contacts where id = '.$id);
			self::updateProperty($cid,$d['type']=='email'?'mail':$d['type'],$active?$d['data']:null,$id);
		}
	}
	public function AddContract($content,$no,$date,$date_dop, $comment) {
		global $db,$user;
		if(!$no)
			$no = $this->id.'-'.date('y');

		$V = array(
			'contract_no'=>$no,
			'contract_date'=>$date,
			'ts'=>array('NOW()'),
			'client_id'=>$this->id,
			'comment'=>$comment,
			'user_id'=>$user->Get('id')
		);
        if(trim($date_dop))
            $V["contract_dop_date"] = $date_dop;

		$db->QueryInsert('client_contracts',$V);
		$cno = $db->GetInsertId();
		self::putContractTemplate($this->id.'-'.$cno,$content);
		return $cno;
	}

    public function getClientClient(&$mix)
    {
        global $db;
        $c = $db->GetRow("select id,client from clients where '".$mix."' in (client, id) limit 1");
        if($c)
            $mix = $c["client"];
        return $mix;
    }

    public function contract_getFolder($folder = null)
    {
        $f = array(
                "MCN" => "mcn",
                "MCN-СПб" => "mcn98",
                "MCN-Краснодар" => "mcn97",
                "MCN-Самара" => "mcn96",
                "MCN-Екатеринбург" => "mcn95",
                "MCN-Новосибирск" => "mcn94",
                "MCN-Ростов-на-Дону" => "mcn87",
                "WellTime" => "welltime",
                "IT-Park" => "itpark",
                "Arhiv" => "arhiv"
                );

        return $folder === null ? $f : $f[$folder];
    }

	public static function contract_listTemplates() {
		$R = array();
        foreach (glob(STORE_PATH.'contracts/template_*.html') as $s) {
            $t = str_replace(array('template_','.html'),array('',''),basename($s));
            list($group,) = explode("_", $t);
            $R[$group][] = substr($t, strlen($group)+1);
        }

        foreach(self::contract_getFolder() as $folderName => $key )
            $_R[$folderName] = isset($R[$key]) ? $R[$key] : array();

        $R = $_R;

		return $R;
	}
	public static function getContractTemplate($v) {
		global $db,$user;
		$v = preg_replace('[^\w\d\-\_]','',$v);
		if (file_exists(STORE_PATH.'contracts/'.$v.'.html')) {
			$data = file_get_contents(STORE_PATH.'contracts/'.$v.'.html');
		} else $data = file_get_contents(STORE_PATH.'contracts/template_mcn_default.html');
		return $data;
	}
	public static function putContractTemplate($v,$data){
		global $db,$user;
		$v = preg_replace('[^\w\d\-\_]','',$v);
		file_put_contents(STORE_PATH.'contracts/'.$v.'.html',$data);
	}

	function Create($uid = null){
		global $db;
		if($this->client!=""){
			if($this->GetDB('client') || $db->GetRow("select * from user_users where user='".mysql_escape_string($this->F['client'])."'"))
				return false;	//дубликат
		}
		$q1 = '';
		$q2 = '';
		foreach($this->F as $k=>$v)
			if($k!='id' && $k!='client'){
				if($q1){
					$q1.=',';
					$q2.=',';
				}
				$q1.=$k;
				$q2.='"'.addslashes($v).'"';
			}

		$db->Query('insert into clients ('.$q1.') values ('.$q2.')');
		$this->F = array('id'=>$db->GetInsertId(), "client" => $this->client);
		return $this->post_apply($uid,true);
	}
	function Apply($uid = null) {
		global $db;
		if(!$this->GetDB('id'))
			return false;
		if($this->D['client']!=""){
			if($this->client!="" && $this->client!=$this->D['client'])
				return false;
			unset($this->F['client']);
		}elseif($this->client!=""){
			if($this->GetDB('client'))
				return false;
		}
		return $this->post_apply($uid);
	}
	function SyncAdditionCards($cl_tid,$from_main=true){
		global $db;
		$cl_main_tid = '';
		if(strrpos($cl_tid, '/')!==false){
			$cl_main_tid = substr($cl_tid,0,-2);
		}else{
			$cl_main_tid = $cl_tid;
		}
		$from_tid = ($from_main)?$cl_main_tid:$cl_tid;

		$up_query = "
			update
				clients cl
			left join
				clients clf
			on
				clf.client = '".addcslashes($from_tid, "\\'")."'
			set
				cl.company = clf.company,
				cl.comment = clf.comment,
				cl.address_jur = clf.address_jur,
				cl.company_full = clf.company_full,
				cl.address_post = clf.address_post,
				cl.address_post_real = clf.address_post_real,
				cl.inn = clf.inn,
				cl.kpp = clf.kpp,
				cl.bik = clf.bik,
				cl.bank_properties = clf.bank_properties,
				cl.address_connect = clf.address_connect,
				cl.phone_connect = clf.phone_connect,
				cl.previous_reincarnation = clf.previous_reincarnation
			where
				(
					cl.client like '".addcslashes($cl_main_tid,"\\'")."/_'
				or
					cl.client like '".addcslashes($cl_main_tid,"\\'")."'
				)
			and
				cl.client <> '".addcslashes($from_tid, "\\'")."'
		";
		$db->Query($up_query);
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
		self::updateProperty($cid,'support',$this->support,-$this->id,true);
		self::updateProperty($cid,'telemarketing',$this->telemarketing,-$this->id,true);
		return true;
	}
	private function post_apply($uid = null,$create = false) {
		global $db,$user;

		if (isset($this->F["client"]) && $this->F["client"]=='idNNNN')
			$this->F["client"] = 'id'.$this->id;

		if ($create)
			$this->exportFull();

		$s = array();
        $applyTS = "0000-00-00";
        $inFuture = false;
		$company = false;

		$deffers = array(
						"1" => array("d" => strtotime("-2 month", strtotime(date("Y-m-01"))), "v" => 0),
						"2" => array("d" => strtotime("-1 month", strtotime(date("Y-m-01"))), "v" => 0),
						"3" => array("d" => strtotime(date("Y-m-01")),                        "v" => 0),
						"4" => array("d" => strtotime("+1 month", strtotime(date("Y-m-01"))), "v" => 0)
						);


		if(get_param_raw("deferred", "")) // берем клиента без изменений с начала периода
		{
			$dd = get_param_raw("deferred_date", "");
			$applyTS = date("Y-m-01", $deffers[$dd]["d"]);

			if($dd < 4)
			{
				$this->D = ClientCS::getOnDate($this->id, $applyTS);
			}
		}

		if (count($this->F)>1) {

			if(isset($this->F["voip_disabled"]) && $this->F["voip_disabled"] == "") $this->F["voip_disabled"] = 0;
			if(isset($this->F["nds_zero"]) && $this->F["nds_zero"] == "") $this->F["nds_zero"] = 0;

			$q='';
			$s = array();
			foreach ($this->F as $k=>$v) if ($k!='id') {
				if ($q) $q.=',';
				$q.=$k.'="'.addslashes($v).'"';

				if (!isset($this->D[$k]) || !$this->_lf_diff($this->D[$k], $v))
                    $s[$k] = array("from" => $this->D[$k], "to" => $v);
			}

			if(
				(isset($this->F["company"]) && isset($this->D["company"]) && $this->F["company"] != $this->D["company"]) ||
				(isset($this->F["company_full"]) && isset($this->D["company_full"]) && $this->F["company_full"] != $this->D["company_full"])
				)
			{

				$company = array(
					"company" => array("from" => $this->D["company"], "to" => $this->F["company"]),
					"company_full" => array("from" => $this->D["company_full"], "to" => $this->F["company_full"])
					);
			}

            if($s)
            {
                if(get_param_raw("deferred", ""))
                {
                    $dd = get_param_raw("deferred_date", "");

                    if($dd == "1" || $dd == "2" || $dd == "3" || $dd == "4")
                    {
	                    if($dd == 4)
	                    {
	                    	$inFuture = true;
	                    }

	                    // помечаем изменения, как перезаписанные
                        /*
                    	$db->Query($sql ="update log_client
		                    		set is_overwrited = 'yes'
		                    		where client_id='".$this->id."'
		                    				and type='fields'
		                    				and ((ts > '".$applyTS."' and apply_ts = '0000-00-00') or apply_ts = '".$applyTS."')");
                                            */

	                    if($dd < 4) //изменения применям сейчас (для установок в предыдущее время)
	                    {
	                        $db->Query($qq='update clients set '.$q.' where id="'.$this->id.'"');
	                        $this->exportClient(self::findClient($this->id));
	                    }
                    }
                }else{
                	$db->Query($qq='update clients set '.$q.' where id="'.$this->id.'"');
					$this->exportClient(self::findClient($this->id));
				}
			}
		}



		if ($uid===null) $uid = $user->Get('id');

		if($s)
        {
        	$a = array(
			 		"client_id" => $this->id,
			 		"user_id" => $uid,
			 		"ts" => array('NOW()'),
			 		"comment" => implode(',', array_keys($s)),
			 		"type" => "fields",
                    "apply_ts" => $applyTS
			 		);

        	if($inFuture)
        		$a["is_apply_set"] = "no";

			$verId = $db->QueryInsert("log_client", $a);

            foreach($s as $k => $v)
            {
                $db->QueryInsert("log_client_fields", array(
                            "ver_id" => $verId,
                            "field" => $k,
                            "value_from" => $v["from"],
                            "value_to" => $v["to"]
                            )
                        );
            }
        }

		if($company)
			$db->QueryInsert("log_client",
					array(
						"client_id" => $this->id,
						"user_id" => $uid,
						"ts" => array('NOW()'),
						"comment" => serialize($company),
						"type" => "company_name"
						)
			);


		return $this->id;
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


	function Add($status,$comment) {		//добавляет статус
		global $db,$user;
		$db->Query("select status from clients where id=".$this->id);
		$r=$db->NextRecord();
		if ($r['status']==$status) $status="";
		$db->Query("insert into client_statuses (ts,id_client,user,status,comment) values (NOW(),'".$this->id."','".$user->Get('user')."','{$status}','{$comment}')");
		if($status){
			$db->Query("update clients set status='{$status}' where id=".$this->id);
		}
	}
	function GetLastComment() {
		global $db;
		$db->Query("select * from client_statuses where (id_client='".$this->id."') and (comment!='') order by ts desc");
		@$r=$db->NextRecord();
		return $r;
	}
	function GetAllStatuses() {
		global $db;
		$db->Query("select * from client_statuses where (id_client='".$this->id."') order by ts asc");
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		return $R;
	}

	public function AddFile($name,$comment) {
		global $db,$user;

		if (!isset($_FILES['file']) || !$_FILES['file']['tmp_name']) return;

		if (!$name) {
			$name = basename($_FILES['file']['name']);
		} else {
			if (!preg_match('/\.([^.]{2,5})$/',$name) && preg_match('/\.([^.]{2,5})$/',$_FILES['file']['name'],$m)) {
				$name.= $m[0];
			}
		}

		$V = array('name'=>$name,'ts'=>array('NOW()'),'client_id'=>$this->id,'comment'=>$comment,'user_id'=>$user->Get('id'));
        printdbg($V);
		$id = $db->QueryInsert('client_files',$V);
		move_uploaded_file($_FILES['file']['tmp_name'],STORE_PATH.'files/'.$id);
	}
	public function GetFile($fid) {
		global $db,$user;
		$f = $db->getRow('select * from client_files where id='.$fid.' and client_id='.$this->id);
		if ($f) $f['path'] = STORE_PATH.'files/'.$f['id'];
		return $f;
	}
	public function GetFiles() {
		global $db,$user;
		return $db->AllRecords('select client_files.*,user_users.user from client_files'.
								' LEFT JOIN user_users ON user_users.id=client_files.user_id'.
								' where client_files.client_id='.$this->id.' order by client_files.id');
	}
	public function DeleteFile($fid) {
		global $db,$user;
		if ($f = $this->GetFile($fid)) {
			$db->Query('delete from client_files where id='.$f['id']);
			unlink($f['path']);
		}
	}
    public function GetList($listName, $zero = false)
    {

        switch($listName)
        {
            case 'metro': return self::_GetList("metro",$zero); break;
            case 'price_type': return self::_GetList("g_price_type"); break;
            case 'logistic': return array(
                                     "none" => "--- Не установленно ---",
                                     "selfdeliv" => "Самовывоз",
                                     "courier" => "Доставка курьером",
                                     "auto" => "Доставка авто",
                                     "tk" => "Доставка ТК",
                                     ); break;
            default: return array();
        }
    }

    private function _GetList($table, $zero = false)
    {
        global $db;
        static $list = array();

        if(!isset($list[$table]))
        {
            $list[$table] = array();

            if($zero == "std")
            {
                $list[$table][0] = "--- Не определено ---";
            }elseif($zero !== false)
            {
                $list[$table][0] = $zero;
            }

            foreach($db->AllRecords("select * from ".$table." order by name") as $m) {
                $list[$table][$m["id"]] = $m["name"];
            }
        }
        return $list[$table];
    }

    public function GetSaleChannelsList()
    {
        return self::_GetList("sale_channels", "std");
    }

    public function GetPriceTypeList($val = false)
    {
        return self::GetList("price_type");
    }

    public function GetMetroList()
    {
        return self::_GetList("metro", 'std');
    }
    public function GetName($type, $id)
    {
        $list = self::GetList($type);
        if(isset($list[$id]))
        {
            return $list[$id];
        }else{
            return false;
        }
    }
    public function GetIdByName($type, $name, $default = false)
    {
        foreach(self::GetList($type) as $id => $_name)
        {
            if($name == $_name) return $id;
        }
        return $default;
    }

    public function GetMetroName($mId)
    {
        $list = ClientCS::GetMetroList();
        if (isset($list[$mId]))
        {
            if ($mId == 0)
            {
                return str_replace("-","",$list[$mId]);
            }else{
                return $list[$mId];
            }
        }
    }

    public function GetPriceType($client)
    {
        $d = ClientCS::FetchClient($client);
        return $d["price_type"] ? $d["price_type"] : ClientCS::getIdByName("price_type", "Розница");
    }

    public function getClientLog($id, $types = array('msg','fields'))
    {
    	global $db;

    	$log = array();
    	foreach($db->AllRecords($q = '
					select
						L.*,
						U.user
					from
						log_client as L
					left join
						user_users as U
					ON
						U.id=L.user_id
					where
						L.client_id = '.$id.'
						and L.type in ("'.implode('","', $types).'")
					order by
						ts desc
				') as $l)
    	{
    		if($l["type"] == "msg")
    		{
    			//nothing :)
    		}elseif($l["type"] == "fields")
    		{
    			$l["comment"] = "Изменены поля: ".self::_resolveFields($l["comment"]);
    		}elseif($l["type"] == "company_name")
    		{
    			$l["company"] = unserialize($l["comment"]);
    			$l["comment"] = "";
    		}

    		$log[] = $l;
    	}

    	return $log;
    }

    private function _resolveFields($f)
    {
    	return str_replace(",", ", ",$f);
    }

    public function getOnDate($clientId, $date)
    {
        global $db;
        //echo $date."|".$clientId;

        $dNow = date("Y-m-d",strtotime("+1 day"));
        $c = $db->GetRow("select * from clients where id='".$clientId."'");

        $trasitFields = array("mail_print", "bill_rename1", "nds_zero");
        $transit = array();

        foreach($trasitFields as $f)
            $transit[$f] = $c[$f];

        if($dNow >= $date)
        {
            foreach($db->AllRecords($sql =
                        "select *
                        from log_client lc, log_client_fields lf
                        where client_id = ".$c["id"]." and
                            if(apply_ts = '0000-00-00', ts >= '".$date." 23:59:59', apply_ts > '".$date."')
                            and if(apply_ts = '0000-00-00', ts < '".$dNow." 00:00:00', apply_ts <= '".$dNow."')
                            and type='fields'
                            and lc.id = lf.ver_id
                            and is_overwrited = 'no'
                        order by lf.id desc ") as $l)
            {
                $ts = strtotime($l["apply_ts"] == "0000-00-00" ? $l["ts"] : $l["apply_ts"]);
                $c[$l["field"]] = $l["value_from"];
            }
        }else{
            foreach($db->AllRecords($sql =
                        "select *
                        from log_client lc, log_client_fields lf
                        where client_id = ".$c["id"]." and
                            if(apply_ts = '0000-00-00', ts >= '".$date." 23:59:59', apply_ts >= '".$date."')
                            and if(apply_ts = '0000-00-00', ts >= '".$dNow." 00:00:00', apply_ts > '".$dNow."')
                            and type='fields'
                            and lc.id = lf.ver_id
                            and is_overwrited = 'no'
                        order by lf.id") as $l)
            {
                $ts = strtotime($l["apply_ts"] == "0000-00-00" ? $l["ts"] : $l["apply_ts"]);
                $c[$l["field"]] = $l["value_to"];
            }
        }

        foreach($trasitFields as $f)
            $c[$f] = $transit[$f];

        return $c;
    }

    public function getManagerName($manager)
    {
        global $db;

        if(!($n = $db->GetValue("select name from user_users where user = '".$manager."'")))
            $n = $manager;

        return $n;
        
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
					$this->data[$i] = array('new',0,0);
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
				UNIX_TIMESTAMP(`R`.`actual_from`) as `actual_from`,
				UNIX_TIMESTAMP(`R`.`actual_to`) as `actual_to`,
				`R`.`net`,
				`clients`.`client`,
				`clients`.`status`,
				`R`.`id`
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
			AND `R`.`actual_from`!='2029-01-01'
			AND `R`.`actual_to`<>'0000-00-00'
			AND `tech_ports`.`port_type` IN ('adsl','adsl_connect','adsl_cards','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1')
			order by
				`R`.`actual_to` ASC, actual_from
		");//.MySQLDatabase::Generate($Cond));

		while($r = $db->NextRecord())
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
							if($r['status']=='tech_deny')
								$st = 'tech';
							elseif($r['status']=='closed' || $r['status']=='deny' || $r['actual_to']<=(time()-3600*24*30))
								$st = 'off';
							if($st){
								if(iplist_check($special,$v[0]))
									$st = 'special';
								$this->data[$i] = array($st,$r['actual_to'],$r['id']);
							}elseif(isset($this->data[$i]))
								unset($this->data[$i]);
						}
					}
				}
			}
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
						'id'=>$v[2]
					);
					$d = 1;
					$md = 1;
					while(($md<16) && ($k & (pow(2,$md)-1))==0)
						$md++;
					$md = pow(2,$md-1);
					while(isset($ta[$k+$d]) && ($d<$md)){
						$d++;
					}
					$dv = floor(log($d,2));
					$d = pow(2,$dv);
					$V[$t][long2ip($k)] = array(32-$dv,$r['actual_to'],$r['id'],$d);
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

class qrcode
{
  public static $codes = array(
    "bill" => array("code" => "01", "c" => "bill", "name" => "Счет"),
    "akt-1" => array("code" => "11", "c" => "akt", "s" => 1, "name" => "Акт 1"),
    "akt-2" => array("code" => "12", "c" => "akt", "s" => 2, "name" => "Акт 2"),
  );

  function encode($docType, $billNo)
  {
	  self::_prepareBillNo($billNo);

	  if(!isset(self::$codes[$docType])) return false;

	  return self::$codes[$docType]["code"].$billNo;
  }

  function _prepareBillNo(&$billNo)
  {
    $billNo = str_replace("-", "1", $billNo);
    $billNo = str_replace("/", "2", $billNo);
  }

  function getNo($billNo)
  {
    $billNo = str_replace("-", "1", $billNo);
    $billNo = str_replace("/", "2", $billNo);

    foreach(self::$codes as $c)
    {

      if(isset($c["s"]))
      {
        $r[$c["c"]][$c["s"]] = $c["code"]."".$billNo;
      }else{
        $r[$c["c"]] = $c["code"]."".$billNo;
      }
    }

    return $r;
  }

  function decodeNo($no)
  {
	  if(strlen($no) == 13)
	  {
		  $type = self::_getType(substr($no, 0, 2));
		  $number = self::_getNumber(substr($no, 2));

		  if($type)
		  {
			  return array("type" => $type, "number" => $number);
		  }
	  }
	  return false;
  }

  function _getType($t)
  {
	  foreach(self::$codes as $c)
	  {
		  if($c["code"] == $t) return $c;
	  }

	  return false;
  }

  function _getNumber($no)
  {
	  switch($no[6])
	  {
		  case '1' : $no[6] = "-"; break;
		  case '2' : $no[6] = "/"; break;
		  default: return false;
	  }

	  return $no;
  }
	function decodeFile($file)
	{
		exec("zbarimg -q ".$file, $o);

		if(!$o) 
			return false;

		foreach($o as $l)
		{
			list($code, $number) = explode(":", $l);

			if($code == "QR-Code")
				return $number;
		}

		return false;
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
    function getId($billNo, $doerId, $comment, $isTrouble = false)
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

            $db->Query($z= "update tt_troubles set doer_comment = '".mysql_escape_string($comment)."', all4geo_id = '".$aId."' where ".($isTrouble ? " id = '".$billNo."'" : "bill_no = '".$billNo."'"));
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
    function sendICQMsg($user, $msg)
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
        if($icq->connect("415601006", 'iddqd111'))
        {
            if(!$icq->send_message($uin, iconv("koi8-r", "cp1251", $msg)))
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
    function setReject($bill, $state)
    {
        if($bill["client_id"] == 15701)
        {
            global $db;

            mail("dga@mcn.ru", "MCN заявака в отказ", "Заявка #".$db->GetValue("select concat(req_no,'/',bill_no) from newbills_add_info  where bill_no = '".$bill["bill_no"]."'")." переведенна на этап \"отказ\"","Content-Type: text/plain; charset = \"koi8-r\"\nFrom: info@mcn.ru");

            mail("shop@nbn-holding.ru", "MCN заявака в отказ", "Заявка #".$db->GetValue("select concat(req_no,'/',bill_no) from newbills_add_info  where bill_no = '".$bill["bill_no"]."'")." переведенна на этап \"отказ\"","Content-Type: text/plain; charset = \"koi8-r\"\nFrom: info@mcn.ru");
        }
    }
}


?>
