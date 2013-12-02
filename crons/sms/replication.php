<?php
define('QUERY_PACKET_SIZE_LIMIT', (1024*1024)*15 ); //15íÂ
define('PATH_TO_ROOT','../../');
include PATH_TO_ROOT."conf.php";


echo "\n".date("r");

$d = date("d");
$dd = strtotime("first day of next month");
$dd = strtotime("-1 day", $dd);

$yesterday = strtotime("yesterday");
$yesterdayDate = date("Y-m-d", $yesterday);
$start = strtotime("-1 month, -1 day", $yesterday);
$startDate = date("Y-m-d", $start);

if($d == date("d", $dd))
{
    $where = "`date` between '".$startDate."' and '".$yesterdayDate."'";
}else{
    $where = "`date` = '".$yesterdayDate."'";
}


$query = "select `client_id` `sender`, `smses` `count`, `date` `date_hour` from `sms_send_byday` where ".$where;

    echo "\n\n".$query;

    try {
        $thiamis = mysql_connect("thiamis.mcn.ru", 'sms_stat', 'yeeg5oxGa', true);
        if(!$thiamis)
            throw new Exception(mysql_error());

        mysql_select_db("sms2", $thiamis);

        $res = mysql_query($query,$thiamis);
        
        if (!$res)
            throw new Exception(mysql_error());

        $query_ = "insert ignore into `sms_stat`(`sender`,`count`,`date_hour`) values (";
        $query = $query_;
        $cnt = 0;
        while($row=mysql_fetch_assoc($res)){
            $query .= $row['sender'].','.$row['count'].',"'.$row['date_hour'].'"),(';
                $cnt ++;
                if(($len=strlen($query))>=QUERY_PACKET_SIZE_LIMIT){
                $query = substr($query,0,$len-2);
                $db->Query($query);
                $query = $query_;
                $cnt = 0;
                }
                }
                $query = substr($query,0,strlen($query)-2);

                $db->Query($query);
                echo "\n".$query;
                }catch(Exception $e)
{
    mail("adima123@yandex.ru", "sms replication error", $e->GetMessage());
}
?>
