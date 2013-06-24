<?php
define('QUERY_PACKET_SIZE_LIMIT', (1024*1024)*15 ); //15íÂ
define('PATH_TO_ROOT','./');
include PATH_TO_ROOT."conf.php";
include MODULES_PATH.'stats/module.php';

$query = "
	select
		`client_id` `sender`,
		`smses` `count`,
		`date` `date_hour`
	from
		`sms_send_byday`
	where
		`date` between date(now()) - interval 1 day and date(now())";

$thiamis = mysql_connect("thiamis.mcn.ru", 'sms_stat', 'yeeg5oxGa', true);
mysql_select_db("smsinfo", $thiamis);
$res = mysql_query($query,$thiamis);
$query_ = "insert into `sms_stat`(`sender`,`count`,`date_hour`) values (";
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
?>