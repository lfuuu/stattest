<?php
use _1c\tr;
class m_yandex extends IModule{
	private static $object;

	function GetMain($action,$fixclient){
		require_once INCLUDE_PATH.'yandex/Client.php';
		
		//if (file_exists(MODULES_PATH.'yandex/ya_stat_token.php')) 
		require_once MODULES_PATH.'yandex/ya_stat_token.php';
		//if (file_exists(MODULES_PATH.'yandex/ya_compapa_token.php')) 
		require_once MODULES_PATH.'yandex/ya_compapa_token.php';

		if (!$action || $action=='default') $action='history';
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if ($act!=='' && !access($act[0],$act[1])) return;
		
		call_user_func(array($this,'yandex_'.$action),$fixclient);		
	}
	
	function yandex_history(){
		global $design, $ya_stat_token, $ya_compapa_token;
		$k = get_param_protected('k', '');
		$history = array();

		$account = '';
		$balance = '';
		if ($k == 'stat' || $k == 'compapa'){
			if ($k == 'stat') $ya = new ZenYandexClient($ya_stat_token);
			if ($k == 'compapa') $ya = new ZenYandexClient($ya_compapa_token);
	
			$res = $ya->getAccountInformation();
			$account = $res['account'];
			$balance = $res['balance'];
			
	        $hist = $ya->listOperationHistory('payment');
	        foreach($hist['operations'] as $op){
	        	$r = array();
	        	//$op = new ZenYandexOperation();
	        	$r['title'] = $op->getTitle();
	        	$r['sum'] = number_format($op->getAmount(), 2);
	        	$r['date'] = date('d.m.Y H:i', $op->getDateTime());
	        	$history[] = $r;	
	        }
		}
		$design->assign('account',$account);
		$design->assign('balance',$balance);
		$design->assign('k',$k);
		$design->assign('history',$history);
		$design->AddMain('yandex/history.html');	
	}

	function yandex_authorize(){
		global $design, $ya_stat_token, $ya_compapa_token;
		$k = get_param_protected('k', '');

		if ($k == 'stat' || $k == 'compapa'){
			if ($k == 'stat') $ya = ZenYandexClient::setClientId('09F00873CFFF8C0AA5A29F554082DEFA3AC90B912758F4F5665CBCD0C04D19DB');
			if ($k == 'compapa') $ya = ZenYandexClient::setClientId('7C084C6C621A3C4639641299B1396C6AB70269CD8B69A5717CA1E57CAFED67E6');
	
			$scope = 'operation-history account-info operation-details payment-shop';
			ZenYandexClient::authorize($scope, 'https://' . $_SERVER['SERVER_NAME'] . '/index.php?module=yandex&action=authorize_callback'.$k.'&x=2');
			
		}else{
			$design->AddMain('yandex/authorize.html');
		}	
	}
	function yandex_authorize_callbackstat(){
		ZenYandexClient::setClientId('09F00873CFFF8C0AA5A29F554082DEFA3AC90B912758F4F5665CBCD0C04D19DB');
	    $access_token = ZenYandexClient::convertAuthToken();
//	    file_put_contents(MODULES_PATH.'yandex/ya_stat_token.php',
//	        	'<?php global $ya_stat_token; $ya_stat_token = "'.$access_token.'";');
	
	    echo "$access_token";
	    echo "<br><br><a href='index.php?module=yandex&action=history'>Перейти в историю</a>";
	    die();
	    //header('location: index.php?module=yandex&action=history');
	}
	function yandex_authorize_callbackcompapa(){

		ZenYandexClient::setClientId('7C084C6C621A3C4639641299B1396C6AB70269CD8B69A5717CA1E57CAFED67E6');
	    $access_token = ZenYandexClient::convertAuthToken();
//	    file_put_contents(MODULES_PATH.'yandex/ya_stat_token.php',
//	        	'<?php global $ya_stat_token; $ya_stat_token = "'.$access_token.'";');
	
	    echo "$access_token";
	    echo "<br><br><a href='index.php?module=yandex&action=history'>Перейти в историю</a>";
	    die();
	    //header('location: index.php?module=yandex&action=history');
    		
	}	
	
	function yandex_pay_stat(){
		global $db, $user, $ya_stat_token;
		
		$bill = get_param_protected('bill', '');
		$comstar = get_param_protected('comstar', '');
		$sum = get_param_protected('sum', '0');
		$backurl = get_param_protected('backurl', '');
		
		$res = '';
		
		try{
			if (!preg_match('/^\d{1,12}-\d{2}$/', $comstar) ||
				$sum == '' ||
				$bill == '' ||
				$backurl == '')
			{
				$res = 'bad parameters';	
			}else{
				$ya = new ZenYandexClient($ya_stat_token);
		        $res1 = $ya->requestPayment('2203', $sum, array('FormComment'=>"COMSTAR {$bill} {$comstar}",'PROPERTY1'=>$comstar));
				if ($res1['status']=='success'){
		        	$res2 = $ya->processPayment($res1['request_id']);
		        	if ($res2['status']=='success'){
						$db->Query("update newbills set payed_ya=payed_ya+'".$sum."' where bill_no = '".$bill."'");
						$db->QueryInsert("log_newbills",array('bill_no'=>$bill,'ts'=>array('NOW()'),'user_id'=>$user->Get('id'),'comment'=>"Оплата YM $sum / COMSTAR {$bill} {$comstar}"));
		        		$res = 'success';
		        	}else{
		        		$res = $res2['error'];
		        	}
		        }else{
					$res = $res1['error'];
		        }
			}
		}catch(Exception $e){
			$res = $e->getMessage();
		}
		header("location: {$backurl}&ym_pay=".urlencode($res));
		die();		
	}
	
	
	function yandex_pay_compapa(){
		global $db, $user, $ya_compapa_token;
		
		$bill = get_param_protected('bill', '');
		$strim = get_param_protected('strim', '');
		$sum = get_param_protected('sum', '0');
		$backurl = get_param_protected('backurl', '');
		
		$res = '';
			
		try{
			if (!preg_match('/^\d{7}-\d{2}$/', $strim) ||
				$sum == '' ||
				$bill == '' ||
				$backurl == '')
			{
				$res = 'bad parameters';	
			}else{
				$strim2 =explode('-', $strim);
				
				$ya = new ZenYandexClient($ya_compapa_token);
		        $res1 = $ya->requestPayment('2765', $sum, array('FormComment'=>"MTS STRIM {$bill} {$strim}",'PROPERTY1'=>$strim2[0],'PROPERTY2'=>$strim2[1]));
				if ($res1['status']=='success'){
		        	$res2 = $ya->processPayment($res1['request_id']);
		        	if ($res2['status']=='success'){
		        		$res = 'success';
		        	}else{
		        		
		        		$res = $res2['error'];
		        	}
		        }else{
					$res = $res1['error'];
		        }
			}
		}catch(Exception $e){
			$res = $e->getMessage();
		}
		
		header("location: {$backurl}&ym_pay=".urlencode($res));
		die();		
	}	


	
}
