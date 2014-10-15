<?
	define('PATH_TO_ROOT','../../stat/');
	define('ERROR_NO_WEB',1);
	include PATH_TO_ROOT."conf_yii.php";
    $user->AuthorizeByUserId(Yii::$app->user->id);

	$action=get_param_raw('action');

	require_once INCLUDE_PATH."graphic.php";

	if (!access('monitoring','view')) exit;

	$ip=get_param_protected('ip','');
	if (!$ip) {
		$graphic=new Graphic2('days');
		$graphic->ShowCols();
	}
	
	//$graphic=new Graphic();

	//1 - this day
	//2 - previous day
	//3 - this month
	//4 - previous month
	$period=get_param_raw('period',1);
	
	if ($period==1 || $period==2 || $period=='day') {
		if ($period=='day') {
			$day=mktime(0,0,0,get_param_integer('m'),get_param_integer('d'),get_param_integer('y'));
		} else {
			$day=mktime(0,0,0,date('m'),date('d'),date('Y'));
			if ($period==2) $day-=86400;
		}
		$day=floor($day/300);
		if ($period==1) {
			$w=' AND time300>='.$day;
		} else {
			$w=' AND time300>='.($day).' AND time300<'.($day+288);
		}
		$db->Query('select * from monitor_5min where ip_int=INET_ATON("'.$ip.'")'.$w.' order by time300');
		$R=array(); $maxval=0; $E=0; $cnt=0;
		while ($r=$db->NextRecord()) {
			$maxval=max($maxval,$r['value']);
			$v=$r['time300']-$day;
			$R[$v]=array($r['value'],$r['value']==0?2:0);
			$E+=$r['value'];
			$cnt++;
		}
		$E=$E/($cnt?$cnt:1);
		if ($maxval<=50) {$maxval=50;
		} elseif ($maxval<=200) {$maxval=200;
		} else {
			for ($i=0;$i<12*24;$i++) if (isset($R[$i]) && $R[$i][0]>200) $R[$i]=array(200,1);
			$maxval=200;
		}
		$graphic=new Graphic2('day',2);
		$graphic->Paint($R,array($maxval,2),$E);
	} else if ($period==3 || $period==4 || $period=='month'){
		if ($period=='month') {
			$day=mktime(0,0,0,get_param_integer('m'),1,get_param_integer('y'));
			$day2=getdate($day+86400*40);		//next month
			$day2=mktime(0,0,0,$day2['mon'],1,$day2['year']);
			$day2=floor($day/3600);
		} else {
			$day=mktime(0,0,0,date('m'),'1',date('Y'));
			if ($period==4) {
				$day2=floor($day/3600);
				$day=getdate($day-86400*4);		//previous month
				$day=mktime(0,0,0,$day['mon'],1,$day['year']);
			}
		}
		$day=floor($day/3600);
		if ($period==3) {
			$w=' AND time3600>='.$day;
		} else {
			$w=' AND time3600>='.$day.' AND time3600<'.($day2);
		}
		
		$R=array(); $maxval=0; $E=0; $cnt=0;
		$db->Query('select * from monitor_1h where ip_int=INET_ATON("'.$ip.'")'.$w.' order by time3600');
		while ($r=$db->NextRecord()) {
			$maxval=max($maxval,$r['good_sum']);
			$v=$r['time3600']-$day;
			$R[$v]=array($r['good_sum'],$r['bad_count']);
			$E+=$r['good_sum'];
			$vlast=$v;
			$cnt++;
		}
		$E=$E/($cnt?$cnt:1);
		$cdays=cal_days_in_month(CAL_GREGORIAN,date('m',$day*3600),date('Y',$day*3600));
		if ($maxval<=100) { $maxval=100;
		} elseif ($maxval<=500) { $maxval=500;
		} elseif ($maxval<=1000) {
			for ($i=0;$i<$cdays*24;$i++) if (isset($R[$i]) && $R[$i][0]>1000) $R[$i]=array(1000,min(12,$R[$i][1]+1));
			$maxval=1000;
		}
		$graphic=new Graphic2('month',1,$cdays);
		$graphic->Paint($R,array($maxval,12),$E);
	}
?>