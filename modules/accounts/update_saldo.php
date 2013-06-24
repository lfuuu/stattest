<?php
	define("PATH_TO_ROOT",'../../');
	include "../../conf.php";
	
//аутентификация
	$module=get_param_raw('module','');
	$action=get_param_raw('action','default');
	$user->DoAction($action);
	$user->DenyInauthorized();

	$client=get_param_protected('clients_client','');
	if ($client=="") 
	{
		echo("Не выбран клиент");
		exit;
		
	};

	$date_last_saldo=get_param_protected('date_last_saldo');
	$fix_saldo=get_param_protected('fix_saldo');
	$comment=get_param_protected("comment");
	
	$query="UPDATE saldo SET date_of_last_saldo='$date_last_saldo', fix_saldo=$fix_saldo, comment='$comment' 
		where client='$client'";
	$db->Connect();
	$db->Query($query);
	if($db->mErrno>0) echo "Ошибка обновления базы, $query";
	
		
?>
<a href="#" onclick="window.close();">Закрыть окно</a>




