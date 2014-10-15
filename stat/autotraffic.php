#!/bin/php
<?php
try{

	if(in_array('-h',$argv)||in_array('--help',$argv)){
		echo "    Options:
        -h, --help :                this help message
        -v, --verbose :             show runtime information
		--log <file_to_log> :       push sql errors to log file
		--log-full :                fix very biggest query in log, when raise error. Need --log key specify
      Time options.
        --now :                     started for period: today
        --yesterday :               started for period: yesterday
        -ey, --eop-yesterday :      last day of period - yesterday, default - today
        -d :                        day of periods began
        -m :                        month of periods began
        -y :                        year of periods began

        If you didn't specify --now or --yesterday flags, you must define -d,-m and -y\n";
		exit();
	}
	$start = time();

	define('NO_WEB',1);
	define('PATH_TO_ROOT','./');
	include PATH_TO_ROOT."conf_yii.php";
	include MODULES_PATH.'stats/module.php';

	if(in_array('--log',$argv)){
		$lf = $argv[array_search('--log', $argv)+1];
		$lf = str_replace("__FILE__",dirname(__FILE__),$lf);
		define('log',$lf);
		if(!is_writeable(log)){
			echo "Sorry, this log file is not writeable: '".log."'\n";
			exit();
		}
	}else
		define('log',false);

	$ntime=time(); // текущее время
	if(in_array('--now',$argv))
		$time = $ntime;
	elseif(in_array('--yesterday',$argv)){
		$time = time()-3600*24;
		$ntime = $time;
	}else{
		if(!in_array('-d',$argv)){
			echo "Please, give me a day by flag '-d'\n";
			exit();
		}
		if(!in_array('-m',$argv)){
			echo "Please, give me a month by flag '-m'\n";
			exit();
		}
		if(!in_array('-y',$argv)){
			echo "Please, give me a year by flag '-y'\n";
			exit();
		}
		$time =mktime(0, 0, 0, $argv[array_search('-m', $argv)+1], $argv[array_search('-d', $argv)+1], $argv[array_search('-y', $argv)+1]); // время текущего шага

		if(in_array('--eop-yesterday',$argv)||in_array('-ey',$argv)){
			$ntime = time()-3600*24;
		}
	}
	define('verbose',in_array('-v',$argv)||in_array('--verbose',$argv));
	if(verbose){
		echo "Ok, let's go!\n";
		flush();
	}

	while($time <= $ntime){
		$date = date('Y-m-d',$time);
		if(verbose){
			echo $date.": go ... select ip_ports ... ";
			flush();
		}
		$Port = array();
/*old variant.. cycle queries it's very bad!
		$R = $db->AllRecords("
			select
				P.*
			from
				usage_ip_ports as P
			WHERE
				P.actual_from<='".$date."'
			AND
				P.actual_to>='".$date."'
			AND
				P.client!=''
		");

		foreach ($R as $r) {
			$r['tarif'] = get_tarif_current('usage_ip_ports',$r['id']);
			if ($r['tarif']['type']=='I' && $r['tarif']['type_internet']!='wimax') {
				if ($r['tarif']['type_count']!='all_f') { // it's wrong...
					if ($r['tarif']['mb_month']>0) { // it's wrong...
						$Port[$r['id']] = $r;
					}
				}
			}
		} */

		$R = $db->AllRecords($q="
			SELECT
				uip.*
			FROM
				usage_ip_ports uip
			INNER JOIN
				log_tarif lt
			ON
				lt.id_service = uip.id
			AND
				lt.service = 'usage_ip_ports'
			AND
				lt.date_activation <= NOW()
			AND
				lt.id_tarif <> 0
			INNER JOIN
				tarifs_internet ti
			ON
				ti.id = lt.id_tarif
			AND
				ti.type = 'I'
			AND
				ti.type_internet <> 'wimax'
			WHERE
				uip.actual_from<='".$date."'
			AND
				uip.actual_to>='".$date."'
			AND
				uip.client<>''
			AND
				lt.id = (
					SELECT
						id
					FROM
						log_tarif
					WHERE
						service = 'usage_ip_ports'
					AND
						date_activation <= NOW()
					AND
						id_service = uip.id
					ORDER BY
						date_activation desc,
						ts desc,
						id desc
					LIMIT 1
				)
		");

		foreach($R as $r){
			$Port[$r['id']] = $r;
		}

		if(verbose){
			echo "select ip_routes ... ";
			flush();
		}
		$IP = array();
		$R = $db->AllRecords($q="
			select
				R.*
			from
				usage_ip_routes as R
			INNER JOIN
				usage_ip_ports as P
			ON
				P.id=R.port_id
			WHERE
				R.actual_from<='".$date."'
			AND
				R.actual_to>='".$date."'
			AND
				P.actual_from<='".$date."'
			AND
				P.actual_to>='".$date."'
			AND
				P.client!=''
		");

		foreach ($R as $r) {
			if (isset($Port[$r['port_id']])) {
				$v = netmask_to_ip_sum($r['net']);
				for ($i =$v[0];$i<$v[0]+$v[1];$i++) {
					if(isset($IP[(String)$i])){
						if(!in_array($r['port_id'],$IP[(String)$i]))
							$IP[(String)$i][] = $r['port_id'];
					}else{
						$IP[(String)$i] = array(0=>$r['port_id']);
					}
				}
			}
		}

		if(verbose){
			echo "select traf_flows_1d ... ";
			flush();
		}
		$R = array();
        $q="
			select
				ip_int,
				sum(in_r+in_r2+in_f) as in_bytes,
				sum(out_r+out_r2+out_f) as out_bytes
			FROM
				traf_flows_1d
			WHERE
				time = '".$date."'
			GROUP BY
				ip_int
		";
		foreach ($db->AllRecords($q) as $r) {
			if (isset($IP[(String)$r['ip_int']])) {
				foreach($IP[(String)$r['ip_int']] as $pid){
					if(!isset($R[$pid]))
						$R[$pid] = array('in'=>0,'out'=>0);
					$R[$pid]['in'] += $r['in_bytes'];
					$R[$pid]['out'] += $r['out_bytes'];
				}
			}
		}


		if(verbose){
			echo "inserting traf_flows_report ... ";
			flush();
		}
		$rcnt = 0;
		ob_start();
		$query = "INSERT INTO traf_flows_report (`id_port`,`date`,`in_bytes`,`out_bytes`) VALUES ";
		foreach ($R as $pid=>$r) {
			//$db->QueryInsert('traf_flows_report',array('id_port'=>$pid,'date'=>$date,'in_bytes'=>$r['in'],'out_bytes'=>$r['out']));
			$query .= "(".$pid.",'".$date."',".$r['in'].",".$r['out']."),";
			$rcnt++;
		}
		$query = substr($query, 0, strlen($query)-1);
		if($rcnt>0)
			$db->Query($query);

		if(mysql_errno()){
			if(log!==false && in_array('--log-full',$argv)){
				file_put_contents(log, $date.": query: ".$query."; error: ".mysql_error()."\n", FILE_APPEND);
			}
			$rcnt = "ERROR";
			$cnt = 0;
			foreach ($R as $pid=>$r) {
				$db->QueryInsert('traf_flows_report',array('id_port'=>$pid,'date'=>$date,'in_bytes'=>$r['in'],'out_bytes'=>$r['out']));
				if(!mysql_errno())
					$cnt++;
				elseif(log!==false){
					file_put_contents(log, $date." key: ".$pid."; error: ".mysql_error()."\n", FILE_APPEND);
				}
			}
			$rcnt .= " ".$cnt;
		}
		unset($query);
		ob_end_clean();
		if(verbose){
			echo $rcnt." rows inserted ... ok!\n";
			flush();
		}

		$time += 1*60*60*24;
	}
	$end = time();
	if(verbose){
		$time = $end - $start;
		echo "Total time: \n";
		echo $time." seconds OR ".floor($time/60)." minutes and ".($time - (floor($time/60)*60))." seconds\n";
		echo "good luck!\n";
		flush();
	}
}catch(Exception $e)
{
    echo $e->getMessage();
}
?>
