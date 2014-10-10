<?php
	define("PATH_TO_ROOT",'../../stat/');
	include PATH_TO_ROOT."conf.php";

	include_once INCLUDE_PATH.'uniteller.php';

	$gw = new uniteller();
	$gw->updateOrderInfo(intval($_REQUEST['Order_ID']));		
									
?>
