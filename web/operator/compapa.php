<?php
	define("PATH_TO_ROOT",'../../stat/');
	include PATH_TO_ROOT."conf_yii.php";
	
	require_once INCLUDE_PATH.'yandex/Client.php';
	
	require_once MODULES_PATH.'yandex/ya_compapa_token.php';
	
	global $ya_compapa_token;
	
	if (isset($_GET['balance'])){
		
		$res = '';
			
		try{
			$ya = new ZenYandexClient($ya_compapa_token);
	        $res1 = $ya->getAccountInformation();
			$res = $res1['balance'];
		}catch(Exception $e){
			$res = $e->getMessage();
		}
		

		die($res);	
	}