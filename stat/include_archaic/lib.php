<?php
   require_once("dbsettings.php");
function db_open()
{
    $dbh = mysql_connect($GLOBALS['db_host'],
                          $GLOBALS['db_user'],
                          $GLOBALS['db_pswd']);
    if (!$dbh) {
        echo "can't connect to database!<br>";
        exit;
    }
    if (!mysql_select_db($GLOBALS['db_name'], $dbh)) {
        echo "can't select database!<br>";
        exit;
    }
    $GLOBALS['dbh']=$dbh;
   // echo "к базе подключились<br>";
}

function db_quote($string)
{
    $tmp=str_replace("\\", "\\\\", $tmp);
    $tmp=str_replace("'", "\\'", $string);
    return $tmp;
}

function mysql_query_or_exit($query)
{
    $result = mysql_query($query);
    if (! $result) {
        echo "</title></script></center><br><br><b><font color=red>error 500</font> ".
            "occured while executing transaction:</b><br><code>".
            $query."</code><br>";
        exit;
    }
    return ($result);
}

function ip2int($ip_str)
{
    $ip_int=0;
    if (preg_match("/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/",$ip_str,$m)){
	$ip_int=((($m[1]*256+$m[2])*256+$m[3])*256+$m[4]);
    }
    return $ip_int;
}

function int2ip($ip_int)
{
    $t=$ip_int;
    $o4=($t & 0xff);$t/=256;
    $o3=($t & 0xff);$t/=256;
    $o2=($t & 0xff);$t/=256;
    $o1=($t & 0xff);
    return sprintf("%d.%d.%d.%d",$o1,$o2,$o3,$o4);
}

$status_en2ru['negotiations']='переговоры';
$status_en2ru['testing']='тестирование';
$status_en2ru['connecting']='подключение';
$status_en2ru['work']='включен';
$status_en2ru['disabled']='отключен';
$status_en2ru['closed']='договор расторгнут';

/*****************************************************/

$_1_2[1]="одна ";
$_1_2[2]="две ";

$_1_19[1]="один ";
$_1_19[2]="два ";
$_1_19[3]="три ";
$_1_19[4]="четыре ";
$_1_19[5]="пять ";
$_1_19[6]="шесть ";
$_1_19[7]="семь ";
$_1_19[8]="восемь ";
$_1_19[9]="девять ";
$_1_19[10]="десять ";

$_1_19[11]="одиннацать ";
$_1_19[12]="двенадцать ";
$_1_19[13]="тринадцать ";
$_1_19[14]="четырнадцать ";
$_1_19[15]="пятнадцать ";
$_1_19[16]="шестнадцать ";
$_1_19[17]="семнадцать ";
$_1_19[18]="восемнадцать ";
$_1_19[19]="девятнадцать ";

$des[2]="двадцать ";
$des[3]="тридцать ";
$des[4]="сорок ";
$des[5]="пятьдесят ";
$des[6]="шестьдесят ";
$des[7]="семьдесят ";
$des[8]="восемдесят ";
$des[9]="девяносто ";

$hang[1]="сто ";
$hang[2]="двести ";
$hang[3]="триста ";
$hang[4]="четыреста ";
$hang[5]="пятьсот ";
$hang[6]="шестьсот ";
$hang[7]="семьсот ";
$hang[8]="восемьсот ";
$hang[9]="девятьсот ";

$namerub[1]="рубль ";
$namerub[2]="рубля ";
$namerub[3]="рублей ";

$nametho[1]="тысяча ";
$nametho[2]="тысячи ";
$nametho[3]="тысяч ";

$namemil[1]="миллион ";
$namemil[2]="миллиона ";
$namemil[3]="миллионов ";

$namemrd[1]="миллиард ";
$namemrd[2]="миллиарда ";
$namemrd[3]="миллиардов ";

$kopeek[1]="копейка ";
$kopeek[2]="копейки ";
$kopeek[3]="копеек ";


function semantic($i,&$words,&$fem,$f){
global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd;
$words="";
$fl=0;
if($i >= 100){
$jkl = intval($i / 100);
$words.=$hang[$jkl];
$i%=100;
}
if($i >= 20){
$jkl = intval($i / 10);
$words.=$des[$jkl];
$i%=10;
$fl=1;
}
switch($i){
case 1: $fem=1; break;
case 2:
case 3:
case 4: $fem=2; break;
default: $fem=3; break;
}
if( $i ){
if( $i < 3 && $f > 0 ){
if ( $f >= 2 ) {
$words.=$_1_19[$i];
}
else {
$words.=$_1_2[$i];
}
}
else {
$words.=$_1_19[$i];
}
}
}


function spell_number($L,$currency){
global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;

if (strcmp($currency,"USD")==0){
	$namerub=array("","доллар США ","доллара США ","долларов США ");
	$kopeek=array("","цент ","цента ","центов ");
};
$s=" ";
$s1=" ";
$s2=" ";
$kop=intval( ( $L*100 - intval( $L )*100 +0.01));

$L=intval($L);
if($L>=1000000000){
$many=0;
semantic(intval($L / 1000000000),$s1,$many,3);
$s.=$s1.$namemrd[$many];
$L%=1000000000;
}

if($L >= 1000000){
$many=0;
semantic(intval($L / 1000000),$s1,$many,2);
$s.=$s1.$namemil[$many];
$L%=1000000;
if($L==0){
$s.=$namerub[3];
}
}

if($L >= 1000){
$many=0;
semantic(intval($L / 1000),$s1,$many,1);
$s.=$s1.$nametho[$many];
$L%=1000;
if($L==0){
$s.=$namerub[3];
}
}

if($L != 0){
$many=0;
semantic($L,$s1,$many,0);
$s.=$s1.$namerub[$many];
}

if($kop > 0){
$many=0;
$l_k=$kop % 10;
switch ($l_k){
    case 1:
        $s.=" ".$kop." ".$kopeek[1];
        break;
    case 2:
    case 3:
    case 4:
	if($kop>10 and $kop<20) {$s.=" ".$kop." ".$kopeek[3]; break;}   
        $s.=" ".$kop." ".$kopeek[2];
        break;
   default :
        $s.=" ".$kop." ".$kopeek[3];
        break;


};
}
else {
$s.=" 00 ".$kopeek[3];
}
 setlocale(LC_ALL,'ru_RU.UTF-8');
 $s=strtoupper(substr($s,1,1)).substr($s,2);
return $s;
}


?>
