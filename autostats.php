<?
// this line has to be modified for each server
	define('NO_WEB',1);
	define('PATH_TO_ROOT','./');
	include PATH_TO_ROOT."conf.php";
	
	set_time_limit(0);
	require_once(INCLUDE_PATH.'mysmarty.php');
	@include_once (MODULES_PATH."/stats/module.php");
	global $module_stats;
	$module_stats = new m_stats();

	$def=getdate();
	$year=$def['year'];
	$month=$def['mon'];
	$clear=1;
	$day=31;
	$i=0; while (!checkdate($month,$day,$year) && ($i<4)) {$day--; $i++;}	//ЮФПВЩ ОЕ ВЩМП 30ЗП ЖЕЧТБМС
	$from=mktime(0,0,0,$month,1,$year);
	$to=mktime(0,0,0,$month,$day,$year);

	$db->Query('select * from clients where client!=""');
	$C=array(); while ($r=$db->NextRecord()) $C[]=$r['client'];
	echo "Total clients: ".count($C)."\n";	
	$R=array(); $cc=0;
	foreach ($C as $client){
		$db->Query('select * from usage_ip_ports where client="'.$client.'"');
		$R2=array(); while ($r=$db->NextRecord()) $R2[$r['id']]=$r;
		$db->Query('delete from stats_report where client="'.$client.'" AND (month='.$month.') AND (year='.$year.')');

		$b=0;
		foreach ($R2 as $v){		//по всем подключениям клиента
			$T=get_tarif_current('usage_ip_ports',$v['id']);
			$port_id=$v['id'];
			$db->Query('select mb_month from tarifs_internet where id="'.$v['tarif_id'].'"');
			if ($r=$db->NextRecord()) $max_bytes=1024*1024*$r[0]; else $max_bytes=0;
			
			$routes_all=array();
			$db->Query('select * from usage_ip_routes where (port_id='.$port_id.') order by id');
			while ($r=$db->NextRecord()){
				$routes_all[$r['net']]=array($r['net'],$r['actual_from'],$r['actual_to']);
			}
			$routes_all_f=$module_stats->get_routes_list_ip($routes_all);

			$stats=$module_stats->GetStatsInternet($client,$from,$to,'no',$routes_all_f,($T['type']=='C'?1:0));
			$c=count($stats); $r=$stats[$c-1];
			if ($T['type']=='C') {
				$r['bytes']=$r['in_bytes'];
			} else $r['bytes']=max($r['in_bytes'],$r['out_bytes']);
			@$db->Query('insert into stats_report (client,id,bytes,max_bytes,month,year) values ("'.$client.'",'.$port_id.','.$r['bytes'].','.$max_bytes.','.$month.','.$year.')');
			unset($routes_all);
			unset($routes_all_f);
			unset($stats);
		}
		
		$cc=$cc+1;
		echo round(100*$cc/count($C))."% worked: ".$client."\n";
		unset($R2);
	}
	
		
?>