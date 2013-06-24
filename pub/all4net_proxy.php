<?php
	$var = array_merge($_GET,$_POST);
	$auth_code = '460246c85842755e83982b26015863e498bd8becd1dff23a8a0523ae8717cc5b04f2661b25dd073cc0e21866fa9d5bbd758d3588b290da04b42365e04c58cf1c';

	$datetime = date('Y-m-d H:i:s');
	file_put_contents(dirname(__FILE__)."/../log/all4net.log", $datetime.' --- '.var_export($var,true)."\n",FILE_APPEND);

	if(!isset($var['auth']) || $var['auth']<>$auth_code){
		Header('HTTP/1.0 404 Not Found');
		exit();
	}

	if(!isset($var['order_number']) || !is_numeric($var['order_number'])){
		echo "InvalidNumber";
		exit();
	}

	$on = $var['order_number'];
	define('PATH_TO_ROOT',dirname(__FILE__).'/../');
	include(dirname(__FILE__)."/../conf.php");
	include INCLUDE_PATH."bill.php";
	include INCLUDE_PATH."all4net_integration.php";

	try{
		$all4net = new all4net_integration();
	}catch(ErrorException $e){
		echo 'Error:'.$e->getCode();
		file_put_contents(dirname(__FILE__)."/../log/all4net.log", 'Error:'.$e->getCode()."\n\n\n====\n\n",FILE_APPEND);
		exit();
	}

	if($var['dbg']==1){
		if($var['error']){
			echo 'Error:'.(int)$var['error'];
			exit();
		}
		echo '200905-0001-'.$var['order_number'];
		exit();
	}else{
		try{
			$bill_no = $all4net->sync_bill($on);
		}catch(ErrorException $e){
			echo 'Error:'.$e->getCode();
			file_put_contents(dirname(__FILE__)."/../log/all4net.log", 'Error:'.$e->getCode()."\n\n\n====\n\n",FILE_APPEND);
			exit();
		}
	}

	echo $bill_no;
	file_put_contents(dirname(__FILE__)."/../log/all4net.log", "bn:".$bill_no."\n\n\n===\n\n",FILE_APPEND);
	exit();
?>