<?

define("print_sql", 1);
	define('NO_WEB',1);
	define('NUM',35);
	define('PATH_TO_ROOT','./');
	include PATH_TO_ROOT."conf.php";
	include MODULES_PATH.'mail/module.php';
	if(
		date("H:i")>="02:10"
	&&
		date("H:i")<="04:00"
	)exit();

	$db->getRow('select * from mail_letter limit 1;');	//чтоб удостовериться, что всё работает
	$FN = PATH_TO_ROOT."automail.running";
	if(file_exists($FN))
		exit;

	$F = fopen($FN,'w');
	fwrite($F,date("Y-m-d H:i:s"));
	fclose($F);

	echo "############".date("Y-m-d H:i:s")."############## Was Running... \n";
	$R = array();
	$db->Query('select * from mail_letter where letter_state="ready"');
	
	
	while($r = $db->NextRecord(MYSQL_ASSOC))
		$R[$r['job_id']][] = $r;
	
	foreach($R as $job_id=>$R2){
		$idx = 0;
		$job = new MailJob($job_id);
		if(in_array($job->data['job_state'],array('ready','test','news'))){
			$test = (defined('MAIL_TEST_ONLY') && (MAIL_TEST_ONLY==1));
			$test = $test || $job->data['job_state']=='test';
			foreach($R2 as $r){
				if (($idx % 10) == 0 && !in_array($job->get_cur_state(), array('ready','test','news'))) break 1;
				
				$job->assign_client($r['client']);
				
				echo 'Sending '.$job_id.' to '.$r['client'].'..';
				$res = $job->Send($test?ADMIN_EMAIL:null);
				if($res!==true){
					echo "error\n";
				}else{
					echo "ok\n";
				}
				sleep(5);
				
				$idx++;
			}
		}
	}

	echo "============".date("Y-m-d H:i:s")."============== Ok ... \n\n\n";
	unlink($FN);
?>
