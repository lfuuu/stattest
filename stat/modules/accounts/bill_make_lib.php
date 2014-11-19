<?php
die('This function marked for deleting');
function printdbg1($param,$s=''){
 //     echo "<br><pre>$s=";print_r($param);echo "</pre><br>";

};


require_once("../../include_archaic/lib_billing.php");
function do_make_bill($client,$bill_date,$period_f,$period_pre,$comp=0,$type='default',$must_pay=1){
    printdbg1($period_f,"po factu");
    printdbg1($period_pre,"predoplata");
    printdbg1($comp,'Компенсация в часах');

    $bill_no=do_make_bill_generate_number(substr($period_f,0,4).substr($period_f,5,2));
    $sum=0;
    $free_emails=0;
    //networks
    // абонентская плата
    foreach (do_make_bill_conn_enum($client,$period_pre) as $net_id){

    //net_id теперь фактически port_id номер несети а подключения
            printdbg1($net_id,'номер подключения :');
            $tarif_pre=do_make_bill_get_tarif_by_net_id($net_id,$period_pre,2);
            $tarif_pre_=explode("-",$tarif_pre);

            $sum+=do_make_add_line($bill_no,"Абонентская плата за ".month_num2name(substr($period_pre,5,2))." ".substr($period_pre,0,4) ." года (тариф $tarif_pre, подключение $net_id)",1,$tarif_pre_[2],"${period_pre}-01");
            $free_emails++;


    }// конец выставления абонентской платы

    //выставляем трафик
    foreach (do_make_bill_conn_enum($client,$period_f) as $net_id){

            //net_id теперь фактически port_id номер несети а подключения
            printdbg1($net_id,'номер подключения :');

            $tarif_f=do_make_bill_get_tarif_by_net_id($net_id,$period_f,1);
            $tarif_f_=explode("-",$tarif_f);
            $days_this_month=month2days($period_f);
            $comp_money=$comp/($days_this_month*24)*$tarif_f_[2];

            if (strcmp($tarif_f_[0],"C")==0)
                {$trafcount="2F";}
            else
                {$trafcount="12F";}

            $trafs=do_make_bill_conn_count($client,$net_id,$trafcount,$period_f);

            printdbg1($trafs,'trafic из основной программы');

            if ((strcmp($tarif_f_[0],"C")==0) || ($trafs['MBin']>=$trafs['MBout'])){
                $traf_dir_e='in';
                $traf_dir_r='входящий';
            }else{
                $traf_dir_e='out';
                $traf_dir_r='исходящий';
            }

            $traf_to_bill=$trafs['MB'.$traf_dir_e];
            // если клиент был подключен в period_f  то трафик надо выставить с учетом части периода

            $k=get_procent_time($net_id,$period_f);
            printdbg1($k, 'коэффициент k:');

            if ($comp != 0) {
                    $sum+=do_make_add_line($bill_no,"Компенсация за непредоставление доступа в интернет за $comp часов ",1,-1*$comp_money,"${period_pre}-01");
                }
            $sum+=do_make_add_line($bill_no,"Трафик, включенный в абонентскую плату за ".month_num2name(substr($period_f,5,2))." ".substr($period_f,0,4) ." года (тариф $tarif_f, подключение $net_id), Мб",min($traf_to_bill,$tarif_f_[1]*$k),0,"${period_f}-01");
            if ($traf_to_bill>$tarif_f_[1]*$k){
                        $sum+=do_make_add_line($bill_no,"Превышение траффика за ".month_num2name(substr($period_f,5,2))." ".substr($period_f,0,4) ." года (тариф $tarif_f,подключение $net_id), Мб",($traf_to_bill-$tarif_f_[1]*$k),$tarif_f_[3],"${period_f}-01");
                }





    }// конец выставления трафика



  // вставляем абонентскую плату и оплату трафика телефонных переговоров  за IP telephon
   $r=do_monthly_pay_VoIP($client,$period_pre,$period_f);
    if($r!==false){
        foreach($r as $r_) {
            $days_this_month=month2days($period_f);
            $comp_money=$comp/($days_this_month*24)*$r_[4];
            $sum+=do_make_add_line($bill_no,$r_[3]. 'за '.month_num2name(substr($r_[2],5,2))." ".substr($r_[2],0,4) ." года",$r_[5], $r_[4]," ${period_pre}-01");
            if ($comp != 0) {
            $sum+=do_make_add_line($bill_no,"Компенсация за непредоставление услуг телефонии за $comp часов ",$r_[5],-1*$comp_money,"${period_pre}-01");
            }

        };
    };

    $r=do_summ_VoIP($client,$period_f);
    if($r!==false){
        foreach($r as $r_) {
            $sum+=do_make_add_line($bill_no,$r_[3]. ' за '.month_num2name(substr($r_[2],5,2))." ".substr($r_[2],0,4) ." года",1, $r_[1]," ${period_f}-01");
        };
    };


   // конец IP телефонии :)
    foreach (do_make_bill_monthlyadd_enum($client,$period_pre) as $id){
    $req="select description, actual_from, amount,price, period from bill_monthlyadd where id='$id';";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
            {echo "can't read from database!<br>$req"; exit;}
    if($row = mysql_fetch_assoc($result)){
        //$b=$row['only_one_time'];
      //  if (($b==0) or ($b==1 and inside_per($period_pre, $row['actual_from']))){
    $item=$row['description'];
    if ($row['period']!='once') $item.=" за ".month_num2name(substr($period_pre,5,2))." ".substr($period_pre,0,4) ." года";
        $sum+=do_make_add_line($bill_no,$item,$row['amount'],$row['price'],"${period_pre}-01");
      //  };
    }
    }
    foreach (do_make_bill_emails_enum($client,$period_pre) as $email_id){
    $this_email_price=0;
    if ($free_emails--<=0){$this_email_price=1;}
        $sum+=do_make_add_line($bill_no,"Поддержка почтового ящика ".do_make_bill_get_emails_by_id($email_id)." за ".month_num2name(substr($period_pre,5,2))." ".substr($period_pre,0,4) ." года",1,$this_email_price,"${period_pre}-01");
    }

    do_make_bill_register($bill_no,$bill_date,$client,$sum,$type,$must_pay);
    return $bill_no;

}

/**************************************************************************/

function do_make_bill_monthlyadd_enum($client,$period){
    $cnt=0;
    $ids=array();
    //FIXME: select only those in period correctly
    $req="select id from bill_monthlyadd where client='$client' and actual_from<='${period}-31' and actual_to>='${period}-01';";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
    while($row = mysql_fetch_assoc($result)){
    $ids[$cnt++]=$row['id'];
    }
    return ($ids);
}

/***/

function do_make_bill_emails_enum($client,$period){
    $cnt=0;
    $ids=array();
     // Проверяем нет ли у клиента услуги виртуального почтового сервера
     // если есть то счет на почтовые ящики не выставляем
    $query="SELECT * FROM bill_monthlyadd
             WHERE client='$client'
             AND actual_from<='${period}-31'
             AND  actual_to>='${period}-01'
             AND description like 'Виртуальный почтовый сервер%'";

    if (!($res = mysql_query($query,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$query"; exit;}

    if (mysql_num_rows($res)==0){

            //FIXME: select only those in period correctly
            $req="select id from emails where client='$client' and actual_from<='${period}-31' and actual_to>='${period}-01';";
            if (!($result = mysql_query($req,$GLOBALS['dbh'])))
                {echo "can't read from database!<br>$req"; exit;}
            while($row = mysql_fetch_assoc($result)){
            $ids[$cnt++]=$row['id'];
            }
            return ($ids);
    }
}

function do_make_bill_get_emails_by_id($id){

    $req="select local_part,domain from emails where id='$id';";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
    if($row = mysql_fetch_assoc($result)){
    return($row['local_part'].'@'.$row['domain']);
    }
    return ($ids);
}


/***/
function next_period($period){

$period_=explode('-',$period);
if ($period_[1]=='12'){
    $period_[1]='01';
    $period_[0]+=1;
}else{
    $period_[1]+=1;
    if($period_[1]<10) $period_[1]="0{$period_[1]}";
}

return "{$period_[0]}-{$period_[1]}";
}

function do_make_bill_get_tarif_by_net_id($net_id,$period,$flag){
//теперь net_id  это порт id

$req="SELECT id_tarif from log_tarif where id_service=$net_id and service='usage_ip_ports' order by ts desc,id desc LIMIT 1";
if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
$row=mysql_fetch_assoc($result);
extract($row);

//$n_period=next_period($period);

//if ($tarif_change>"$period-31") $tarif_id=$tarif_old_id;

$req="SELECT * from tarifs_internet where id=$id_tarif";
if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
$row=mysql_fetch_assoc($result);
$tarif="{$row['tarif_type']}-{$row['mb_month']}-{$row['pay_month']}-{$row['pay_mb']}";
//echo "<br> получили тариф: $tarif";
return $tarif;



}

function do_make_bill_conn_count($client,$net_id,$trafcount,$period){

// net_id - теперь port_id  надо по нему получить все активные сети

   $query="select usage_ip_routes.id from usage_ip_routes
LEFT JOIN usage_ip_ports on usage_ip_ports.id=usage_ip_routes.port_id
        where client='$client'
        and usage_ip_routes.actual_from<='${period}-31'
        and usage_ip_routes.actual_to>='${period}-01'
        and usage_ip_routes.port_id=$net_id";
        if (!($result = mysql_query($query,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$query"; exit;};
        if (mysql_num_rows($result)<=0){
            echo "В подключении $net_id нет рабочих сетей";
            $r=array(0,0);
            return $r;

        };

    while($r=mysql_fetch_assoc($result)) {
        $net_ids[]=$r['id'];

    };


   // старая часть
//    $net_ids[0]=$net_id;
   //echo "<br>net_id=$net_id";
 //  printdbg1($net_id,"из счетчика трафика");
    /*$cnt=1;
    $req="select id from routes where secondary_to_net='$net_id';";
  printdbg1($req,"запрос на поиск вторичной сети");
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
    while($row = mysql_fetch_assoc($result)){
    $net_ids[$cnt++]=$row['id'];
    }
    */
    // правка Андрей Сылка вставляю поиск подсетей из подключений

    if (strcmp($trafcount,"2F")==0){
    $what="(sum(in_r2)+sum(in_f))/1024/1024 as MBin, ".
        "(sum(out_r2)+sum(out_f))/1024/1024 as MBout ";
    }else{
    $what="(sum(in_r)+sum(in_r2)+sum(in_f))/1024/1024 as MBin, ".
        "(sum(out_r)+sum(out_r2)+sum(out_f))/1024/1024 as MBout ";
    }
    $req="SELECT $what".
    "from traf_flows_1d WHERE  ( ".construct_where_by_net_list($net_ids)." ) AND time like '$period%'";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req eror-".mysql_error()."  <br> net_ids=$net_ids"; exit;}
    if(!($row = mysql_fetch_assoc($result)))
        {echo "no data in database!<br>$req"; exit;}
    return $row;
}

function do_make_bill_conn_enum($client,$period){
    // клиент и периад на выходе список Id всех подключений
    $query="SELECT id from usage_ip_ports where client='$client'";
 //   echo $query."<br>";
    if (!($res = mysql_query($query,$GLOBALS['dbh'])))
        {
            echo "can't read from database!<br>$res";
            exit;
        }
    $ids=array();
   //echo "подключений=".mysql_num_rows($res)."<br>";
    while($r=mysql_fetch_assoc($res)){
        $ids[]=$r['id'];
    };
  //  printdbg1($ids,"все подключения");
    $ports=array();
    //проверяем есть ли в данном подключении активные сети
    foreach($ids as $port){
   //   echo "<br>port- $port";
        $query="select usage_ip_routes.id from usage_ip_routes
LEFT JOIN usage_ip_ports ON usage_ip_ports.id=usage_ip_routes.port_id
        where usage_ip_ports.client='$client'
        and usage_ip_routes.actual_from<='${period}-31'
        and usage_ip_routes.actual_to>='${period}-01'
        and usage_ip_routes.port_id=$port";
    //  printdbg1($query);
        if (!($result = mysql_query($query,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$query"; exit;};
        if (mysql_num_rows($result)>0){
            $ports[]=$port;
        }
    }
    return $ports;
/* Старя процедура
    $cnt=0;
    //FIXME: select only those in period correctly
    $req="select id from routes where client='$client' and secondary_to_net='' and actual_from<='${period}-31' and actual_to>='${period}-01';";
    printdbg1($req,"enum connections req");
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}

    while($row = mysql_fetch_assoc($result)){
    $ids[$cnt++]=$row['id'];
    }
    return ($ids);
    printdbg1($ids,"ids");
*/
}

function make_balance_correction_nodb($client,$sum){
	if ($sum>=0) $sum='+'.$sum;
	$q=mysql_query("select * from balance where client='{$client}'");
	if (!($r=mysql_fetch_row($q))) mysql_query("insert into balance (client) values ('{$client}');");
	mysql_query("update balance set sum=sum{$sum} WHERE client='{$client}'");
}

function do_make_bill_register($bill_no,$bill_date,$client,$sum,$type,$must_pay,$sum_virtual=0){
    $req="select company_full,address_post,usd_rate_percent,fax from clients where client='$client' limit 1;";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
    if(!($row = mysql_fetch_assoc($result)))
        {echo "no data in database!<br>$req"; exit;}
    do_make_add_line($bill_no,"*Итого :",1,$sum,'0000-00-00');
    $sum+=do_make_add_line($bill_no,"*НДС 18% :",1,$sum*0.18,'0000-00-00');
    do_make_add_line($bill_no,"*Всего с НДС :",1,$sum,'0000-00-00');
	if ($type!='advance' && $must_pay) make_balance_correction_nodb($client,-$sum);
    $req="insert into bill_bills ".
    "(client,company_full,address_post,bill_no,bill_date,sum,usd_rate_percent,state,fax,type,must_pay,sum_virtual)".
    "values".
    "('$client','".$row['company_full']."','".$row['address_post']."','$bill_no','$bill_date','$sum','".$row['usd_rate_percent']."','ready','".$row['fax']."','$type','$must_pay','$sum_virtual')";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't write to database!<br>$req"; exit;}
}
function do_make_add_line($bill_no,$item,$amount,$price,$item_date,$service='',$param=''){

    $req="select line from bill_bill_lines where bill_no='$bill_no' order by line desc limit 1;";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$req"; exit;}
    if($row = mysql_fetch_assoc($result))
    {$line=$row['line']+1;}
    else
    {$line=1;}
    $sum=$amount*$price;
    $req="insert into bill_bill_lines ".
    "(bill_no,line,item,amount,price,sum,item_date,service,param)".
    "values".
    "('$bill_no','$line','$item','$amount','$price','".$sum."','$item_date','$service','$param')";
//echo $req."<br><br>";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "can't write to database!<br>$req"; exit;}
    return($sum);
}

function do_make_bill_generate_number($prefix){
    $req="SELECT substring(bill_no,length('$prefix')+2) as suffix
          FROM  bill_bills
          WHERE  bill_no like '$prefix-%'
          ORDER BY  bill_no DESC  LIMIT 1";

    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
        {echo "do_make_bill_generate_number::can't read from database!<br>$req"; exit;}
    if($bill_row = mysql_fetch_array($result))
    {$suffix=$bill_row['suffix'];}
    else
        {$suffix=0;}
    $suffix++;
    return sprintf("%s-%04d",$prefix,$suffix);
}

/********************************************/

function construct_where_by_net_list($nets){
    $ip_match="*";
    $where_ip="";
    $ids="";
    foreach ($nets as $net_id){
    if (strlen($ids)>0){$ids.=" OR ";}
    $ids.="id = $net_id";
    //echo "<br> $net_id";
    }
    $req="select actual_from,actual_to,net,flows_node from usage_ip_routes ".
    "where ($ids);";
    if (!($result = mysql_query($req,$GLOBALS['dbh'])))
    {echo "can't read from database!<br>$req"; exit;}
    while($row = mysql_fetch_row($result)){
    if ((int)$row[0]>=2029){$row[0]='2029-01-01';}
    if ((int)$row[1]>=2029){$row[1]='2029-01-01';}
    $where_ip=construct_where_by_net_list_parse_or_block($where_ip,$row[0],$row[1],$row[2],$row[3],$ip_match);
    }
    mysql_free_result($result);
    return $where_ip;
}

function construct_where_by_net_list_parse_or_block($where_ip,$from,$to,$ip_block,$router,$ip_match){
    if (preg_match("/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\/?(\d{1,2})?/",$ip_block,$matches)){
        $ip_int=ip2int($matches[1]);
	if (!$matches[2]) $matches[2]=32;
        $ips=pow(2,32-$matches[2]);
    $nnn=0;
        for ($i=0;$i<$ips;$i++){
        $ip_str=int2ip($ip_int);
        if (strcmp($ip_match,'*')==0 || strcmp($ip_match,$ip_str)==0){
        if ($nnn++==0){
            if (strlen($where_ip)>0){$where_ip.=" OR ";}
                $where_ip.="(time>='$from' AND time<='$to' AND router='$router' AND (";
        }else{
            $where_ip.=" OR ";
        }
            $where_ip.="ip_int=inet_aton('".$ip_str."')";
        }
            $ip_int++;
    }
    if ($nnn>0){
            $where_ip.=")) ";
    }
    }
    return $where_ip;
}//end function

function month_num2name($num) {
    $nbm = array('','январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь');
    return ($nbm[(int)($num+0)]);
}
function inside_per($period, $p){
    $pr[0]=substr($period,0,4);
    $pr[0].="-".substr($period,4,2);
    $pr[0].="-01 00:00:00";
    $pr[1]=substr($period,0,4)."-".substr($period,4,2)."-31 23:59:59";
    if ($pr[0]<=$p and  $p<=$pr[1]) return true;
    return false;
}

function closed_connection($period,$net_id){
  $query="SELECT actual_from, actual_to FROM usage_ip_routes
      WHERE id=$net_id";
  if (!($result = mysql_query($query,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$query"; exit;}
  $row=mysql_fetch_row($result);
  $start=substr($period,0,4)."-".substr($period,5,2)."-01";
  if ($row[1]<$start){
    return false;
  }
  return true;

};
function get_procent_time($port_id,$period){
$query="select  MIN(actual_from) as afrom, MAX(actual_to) as ato from usage_ip_routes
        where actual_from<='${period}-31'
        and actual_to>='${period}-01'
        and port_id=$port_id";
        if (!($result = mysql_query($query,$GLOBALS['dbh'])))
        {echo "can't read from database!<br>$query"; exit;};
$row=mysql_fetch_assoc($result);
extract($row);

$pf="$period-01";
$d=month2days($period);
//echo "период:$period   --- дней:$d<br>";
$pt="$period-$d";
//echo "<br> дата подключения/отключения$afrom --- $ato<br>";

if ($afrom>$pf) $pf=$afrom;
if ($ato<$pt)$pt=$ato;

//echo "$pf --- $pt<br>";
$pf.=" 00:00:00";
$pt.=" 23:59:59";
$delta=time_interval($pf,$pt);
printdbg1($delta,"delta");
if (($delta[3]+1)<$d){
    //echo "days1=$d | delta3={$delta[3]}";
    $k=($delta[3]+1)/$d;
}else $k=1;

//echo "коэффициент - $k<br>";
return $k;


}

?>
