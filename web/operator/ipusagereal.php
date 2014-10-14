<?php
	define("PATH_TO_ROOT",'../../stat/');
	include PATH_TO_ROOT."conf_yii.php";

	$IP = array(
			'MCN-NET'		=> array('85.94.32.0','85.94.51.255'),
			'MCN-ADSL-NET2'	=> array('85.94.54.0','85.94.62.255'),
			'MCN-ADSL-NET3'	=> array('89.235.128.0','89.235.143.255'),
			'MCN-ADSL-NET4'	=> array('89.235.144.0','89.235.159.255'),
			'MCN-ADSL-NET5'	=> array('89.235.160.0','89.235.175.255'),
			'MCN-ADSL-NET6'	=> array('89.235.176.0','89.235.191.255')
			);
	$IPs = get_param_raw('ip');
	$tmFrom = get_param_raw('from','2007-01-01');
	$tmTo = get_param_raw('to','2010-05-01');

	if ($image = get_param_integer('image',0)) {
		$tmFrom = strtotime($tmFrom);
		$tmTo = strtotime($tmTo);
		if ($IPs && isset($IP[$IPs])) {
			$IPs = array(0/*$ipsel*/=>$IP[$IPs]);
		} else $IPs = $IP;
		$totalIPs = 0;
		foreach ($IPs as $k=>$v) {
			$t = netmask_to_ip_sum($v[0]);
			$IPs[$k][0] = $t[0];
			$t = netmask_to_ip_sum($v[1]);
			$IPs[$k][1] = $t[0];
			$totalIPs += $IPs[$k][1]-$IPs[$k][0]+1;
		}

		$Cond = array('AND','actual_from<=FROM_UNIXTIME("'.$tmTo.'")','actual_to>=FROM_UNIXTIME("'.$tmFrom.'")','actual_from!="0000-00-00"');
		$db->Query('select UNIX_TIMESTAMP(actual_from) as actual_from,UNIX_TIMESTAMP(actual_to) as actual_to,net from usage_ip_routes where '.MySQLDatabase::Generate($Cond));
		$F = array(); $T = array();
		while ($r = $db->NextRecord()) if ($v = netmask_to_ip_sum($r['net'])) {
			$b = 0;
			foreach ($IPs as $ip) {
				if ($v[0]>=$ip[0] && $v[0]<=$ip[1]) {$b=1; break;}
			}
			if ($b) {
				$r['actual_from'] = intval($r['actual_from']/86400);
				$r['actual_to'] = intval($r['actual_to']/86400);

                if(!isset($F[$r['actual_from']]))$F[$r['actual_from']] = 0;
                if(!isset($T[$r['actual_to']]))$T[$r['actual_to']] = 0;

				$F[$r['actual_from']] += $v[1];
				$T[$r['actual_to']] += $v[1];
			}
		}

		$tmDelta = $tmFrom - intval($tmFrom/86400)*86400;
		$tmFrom = intval($tmFrom/86400);
		$tmTo = intval($tmTo/86400);

		$counter = 0;
		foreach ($F as $k=>$v) if ($k<$tmFrom) $counter+=$v;
		$D = array(); $DMax = 0;
		for ($t = $tmFrom;$t<=$tmTo;$t++) {
			if (isset($F[$t])) $counter+=$F[$t];
			$DMax = max($DMax,$counter);
			$D[$t-$tmFrom] = $counter;
			if (isset($T[$t])) $counter-=$T[$t];
		}
		
		$W = 600; $H = 300; $dW = 20; $dH = 30;
		$img = imagecreate($W+$dW,$H+$dH);
		$cA = imagecolorallocate($img,255,255,255);
		$cB = imagecolorallocate($img,0,0,0);
		$cC = imagecolorallocate($img,160,160,160);
		$cD = imagecolorallocate($img,255,64,64);
		$scaleH = $H/$DMax;
		$scaleW = $W/($tmTo-$tmFrom);

		$d = pow(2,intval(log($DMax / 10,2)));
		for ($v=0;$v<$DMax;$v+=$d) {
			$vs = $v; if ($v%512==0) $vs = ($v/1024).'K';
			imagestring($img,2,0,$H-$v*$scaleH-8,$vs,$cB);
			imageline($img,$dW-2,$H-$v*$scaleH,$dW+$W,$H-$v*$scaleH,$cC);
		}
		$d = intval(($tmTo-$tmFrom) / 10);
		for ($v = 0;$v<=$tmTo-$tmFrom;$v+=$d) {
			$vs = date('y-m-d',($tmFrom+$v)*86400+$tmDelta);
			imagestring($img,1,$v*$scaleW+$dW-20,$H+4,$vs,$cB);
			imageline($img,$v*$scaleW+$dW,0,$v*$scaleW+$dW,$H+2,$cC);
		}
		
		
		for ($i=1;$i<=$tmTo-$tmFrom;$i++) {
			imageline($img,($i-1)*$scaleW+$dW,$H-$D[$i-1]*$scaleH,$i*$scaleW+$dW,$H-$D[$i]*$scaleH,$cD);
		}
		
		imagestring($img,2,5,$H+16,"Total number of IP addresses: ".$totalIPs."; used: ".$D[count($D)-1].' ('.round(100*$D[count($D)-1]/$totalIPs,2).'%)',$cB);
			
		header("Content-type: image/gif");
		imagegif($img);
	} else {
		$design->assign('IP',$IP);
		$design->assign('ips',$IPs);
		$design->assign('tmfrom',$tmFrom);
		$design->assign('tmto',$tmTo);
		$design->display('pop_header.tpl');
		$design->display('ipusage.tpl');
		$design->display('pop_footer.tpl');
	}
?>
