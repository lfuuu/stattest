<?
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";
	debug_table(__file__."\n".'GET '.print_r($_GET,true)."\n\n".'POST '.print_r($_POST,true));
	if (get_param_integer('LMI_PREREQUEST',0)==1) {
		echo $module_pay->webmoneySetStatus('check');
	} else {
		echo 'PAY ';
		echo $module_pay->webmoneySetStatus('payed');
		
	}
?>
