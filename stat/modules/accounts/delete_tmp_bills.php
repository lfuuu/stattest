<?php
die('This function marked for deleting');
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	include INCLUDE_ARCHAIC_PATH."lib.php";
	db_open();
	$bills="where bill_no in (";
	for($i=76;$i<225;$i++){
		$postfix=strval($i);
		$p=array('000','00','0','');
		$postfix=$p[strlen($postfix)-1].$postfix;
		$bills.="'200510-".$postfix."',";
	}
	echo $bills;

SELECT substring(bill_no,length('200510')+2) as suffix 
          FROM  bill_bills 
          WHERE  bill_no like '200510-%' 
          ORDER BY  bill_no DESC  LIMIT 1"
	
?>
