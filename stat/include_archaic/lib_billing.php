<?php
    require_once "lib.php";
    function get_VoIP_limits($client,$phone,$service=0){
        // Возвращает отрицательное значение при ошибки
        // ложь неверные  входные параметры
        // ложь ошибки работы с базой
        db_open();

        $client=htmlspecialchars($client);
        if (is_numeric($phone) and is_numeric($service)){
            $query="SELECT account
                    FROM accounts
                    WHERE client='$client' and service=$service  ";
        }else return false ;
        $res=mysql_query_or_exit($query);
        $row=mysql_fetch_assoc($res);
        if ($row['account']<=0 ){
            return 0;
        };
        // прайс должен быть актуальным - архиф тарифа будем хранить в другом файле
        $query="SELECT rate
                FROM price_voip
                WHERE  INSTR(".$phone.",destination_prefix)=1
                ORDER BY LENGTH(destination_prefix)
                DESC LIMIT 1";







    };
function is_date($arr){
    foreach ($arr as $item){
        if (!preg_match("|[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}|",$item)) return false;
    };
    return true;
};
function do_summ_VoIP($client,$period){
    // возвращает false при ошибках в коннекте или несуществующем клиенте
    // возвращвет массив по номерам телефона клиента массивов:
    //0           клиент
    //1           сумма за услуги
    //2           дата начала расчета
    //3           За что беруться деньги
    //4
    //5
    //6
    db_open();
    $client=addslashes($client);
    $pr[0]=substr($period,0,4);
    $pr[0].="-".substr($period,5,2);
    $pr[0].="-01 00:00:00";
    $pr[1]=substr($period,0,4)."-".substr($period,5,2)."-31 23:59:59";
    if(!is_date($pr)) return false;
    $query=    "SELECT usage_voip.client as client, sum(usage_voip_sess.AcctBillingSum) as sum, usage_voip.E164 as phone, usage_voip.tarif as tarif
                FROM `usage_voip_sess`,`usage_voip`
                WHERE  usage_voip_sess.AcctStartTime >='{$pr[0]}'
                    and usage_voip_sess.AcctStartTime <'{$pr[1]}'
                    and usage_voip.id=usage_voip_sess.usage_id
                    AND usage_voip.client='$client'
                    GROUP BY usage_voip.E164"     ;
                //echo "Query 1 - $query <br>";
    $res=mysql_query($query);
    if ((!$res) or (mysql_num_rows($res)<1)) return false;
    while ($row=mysql_fetch_assoc($res)){
        if ($row['sum']>0){
            $s_add='';
            $tarif_= explode("-",$row['tarif']);
            if($tarif_[3]=='A') $s_add=" (междугородние и международные звонки) ";
            $r[]=array($client,$row['sum'],$period,"Услуги IP телефонии за номер ".$row['phone'].$s_add);
        };
    }
    return $r;

};
function do_monthly_pay_VoIP($client,$period,$period_previus){
    //вычисляем абоненсткую плату
    // один клиент может иметь несколько подключений  с разными тарифными планами и на каждом подключении
    // разное кол-во линий
    //  возвращаем лож в случае ошибок
    //  возвращаем массив:
    //0- клиент
    //1- сумма
    //2- период
    //3- формулировка за что
    //4 - тариф
    //5 - кол-во
    //
    $sum=0;
    $r=array();
    db_open();
    $client=addslashes($client);
    $pr[0]=substr($period,0,4)."-".substr($period, 5,2)."-01 00:00:00";
    $pr[1]=substr($period,0,4)."-".substr($period,5,2)."-31 00:00:00";
    if(!is_date($pr)) return false;
    $pr_previus[0]=substr($period_previus,0,4)."-".substr($period_previus, 5,2)."-01 00:00:00";
    $pr_previus[1]=substr($period_previus,0,4)."-".substr($period_previus,5,2)."-31 00:00:00";
    if(!is_date($pr_previus)) return false;
    // выбираем только те у кого на момент начала периода еще не закончилось время астуальности услуги
    $query="SELECT tarif, no_of_lines, actual_from, actual_to, E164 as phone
            FROM `usage_voip`
            WHERE actual_to>'{$pr[0]}' AND client='$client' AND actual_from<'{$pr[1]}'";
            //echo "query= $query <br>";
    $res=mysql_query($query);
    if((!$res) or (mysql_num_rows($res)<=0)) return false;
    While ($row=mysql_fetch_assoc($res)){
        // ($row['actual_from']<=$pr[0] and $row['actual_to']>=$pr[1]){
            $k=1;
            // коэффициент изменяется если клиент подключил услугу в середине месяца и мы должны уменьшить
            // его абонгентскую плату
            // дальше как раз рассматривается такая ситуация
           // echo "{$pr[0]}   ----   {$row['actual_from']} <br>";
           // Мы должны менять коэффициент только если они подключились в предыдущем периоде 
           // т.к. при подключении они платят полную абонентку 
           //
           
            if ($pr_previus[0]<$row['actual_from']){
                $day=substr($row['actual_from'],8,2);
                //echo "<br> day".$day." -- ".month2days($period_previus);
                $k=(month2days($period)-$day+1)/month2days($period);
                //echo "<br> k=".$k;
            };
            $tarif_=explode('-',$row['tarif']);
          //  $sum+=$tarif_[1]+$tarif_[2]*($row['no_of_lines']-1);

                if ($row['no_of_lines']==1){
                    $sum=$tarif_[1]*$k;
                    if($sum>0){
                        $r[]=array($client,$sum, $period,"Абонентская плата за телефонный номер ".$row['phone']." и одну линию ",$tarif_[1]*$k,1);
                    };
                }else{
                    $sum=$tarif_[1]*$k;
                  //  echo "<br>sum=".$sum;
                    $sum2=$tarif_[2]*($row['no_of_lines']-1)*$k;
                    if($sum>0){
                        $r[]=array($client,$sum,$period,"Абонентская плата за телефонный номер ".$row['phone'].", и одну линию ",$tarif_[1]*$k,1);
                    };
                    $al=$row['no_of_lines']-1;
                    if($sum2>0){
                        $r[]=array($client, $sum2, $period, "Абонентская плата за дополнительные линии телефонного номера ".$row['phone']." ",$tarif_[2]*$k,$al);
                    };
                };

        //


    };

    //$r=array($client,$sum, $period,"Абонентская плата за IP телефонию");
    return $r;
};

function time_interval ($time1, $time2){
    $time_a= array($time1,$time2);
    if (is_date($time_a)){
        foreach($time_a as $time){
            $time=str_replace(" ","-",$time);
            $time=str_replace(":","-",$time);
            //echo "<br> время из тайм интервал :$time<br>";
            list($year, $month, $day, $hour, $minuts, $sec)=explode("-",$time);
            $time_stamp[]= mktime($hour,$minuts,$sec,$month,$day,$year);
        };

       $sec=(abs($time_stamp[0]-$time_stamp[1]));
       $min=$sec/60 ;
       $hours=$min/60;
       $days=$hours/24;
       return array($sec,$min,$hours,$days);
    };
    return FALSE;


}


function month2days($period)
{
 $month=substr($period,5,2);
 //echo "<br>month=".$month."<br>";
 $monthes=array("01"=>31,"02"=>28,"03"=>31,"04"=>30, "05"=>31, "06"=>30, "07"=>31, "08"=>31, "09"=>30,
 	"10"=>31, "11"=>30, "12"=>31);
 return $monthes[$month];
};



?>
