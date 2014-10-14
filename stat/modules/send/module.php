<?
class m_send {	
	var $rights=array(
					'send'		=>array('Массовая отправка счетов','r,send','просмотр состояния,отправка')
				);
	var $actions=array(
					'default'		=> array('send','r'),
					'send'			=> array('send','send'),
					'confirm'		=> array('send','send'),
					'process'		=> array('send','send'),
				);

	//содержимое левого меню. array(название; действие (для проверки прав доступа); доп. параметры - строкой, начинающейся с & (при необходимости); картиночка ; доп. текст)
	var $menu=array(
					array('Состояние',	'default',	''),
				);

	function m_send(){	
		
	
	}
	function Install($p){
		return $this->rights;
	}
	
	function GetPanel($fixclient){
		$R=array();
		foreach($this->menu as $val){
			$act=$this->actions[$val[1]];
			if (access($act[0],$act[1])) $R[]=array($val[0],'module=send&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
		}
		if (count($R)>0){
            return array('Отправка счетов',$R);
		}
	}

	function GetMain($action,$fixclient){
		global $design,$db,$user;
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		call_user_func(array($this,'send_'.$action),$fixclient);
	}
	
	function send_default($fixclient){
		global $db,$design;
		$sql='select * from newbill_send order by state,last_send desc,client';
		$db->Query($sql);
		$R=array(); while ($r=$db->NextRecord()) {
			if (isset($R[$r['client']])){
				$R[$r['client']][]=$r;
			} else $R[$r['client']]=array($r);
		}
		$design->assign('send_clients',$R);
		$design->AddMain('send/main.tpl');		
	}
	
	function send_send($fixclient){
		global $db,$design;
		$year=get_param_integer('year',''); if (!$year) return;
		$month=get_param_integer('month',''); if (!$month) return;
		$date=$year.'-'.$month.'-1';
		$sql="select newbills.bill_no,newbills.bill_date,clients.manager,clients.client,clients.email,IF(DAY(bill_date)=1,1,0) as fday from newbills
			INNER JOIN clients on newbills.client_id=clients.id
			where (newbills.bill_date='".$year."-".$month."-01')
			ORDER BY IF(clients.email='',1,0),client";
		$db->Query($sql);
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		$design->assign('send_confirms',$R);
		$design->AddMain('send/confirm.tpl');	
	}
	function send_confirm($fixclient){
		global $db,$design;
		$bill_client=get_param_raw('bill_client'); if (!is_array($bill_client)) return;
		$bill_no=get_param_raw('bill_no'); if (!is_array($bill_no)) return;
		$bill_confirmed=get_param_raw('bill_confirmed'); if (!is_array($bill_confirmed)) return;
		$bill_email=get_param_raw('bill_email'); if (!is_array($bill_email)) return;
		$db->Query('delete from newbill_send');
		foreach ($bill_client as $i=>$c) if (isset($bill_confirmed[$i]) && $bill_confirmed[$i]==1) {
			$sql='insert into newbill_send
						(client,bill_no,state,message) values
						("'.$bill_client[$i].'","'.$bill_no[$i].'","ready","'.$bill_email[$i].'")';
			$db->Query($sql);
		}
		trigger_error('<script language=javascript>window.location.href="?module=send";</script>');
	}
	function send_process($fixclient){
		global $design,$db;
		$is_test=get_param_integer('test',1);
		$cont=get_param_integer('cont',0);
		$sql='select client from newbill_send where
				((state="ready") || (state="sent") || (state="error")) &&
				(!last_send || (last_send+INTERVAL 1 DAY < NOW()))
				group by client order by state,last_send desc,client LIMIT 5';
		$db->Query($sql);
		$C=array(); while ($r=$db->NextRecord()) $C[$r['client']]=$r['client'];
		foreach ($C as $client){
			$this->to_client($client,$is_test);
		}

		if (count($C)) $q='IF (client IN ("'.implode('","',$C).'"),1,0)'; else $q='0';
		$sql='select *,'.$q.' as cur_sent from newbill_send order by cur_sent desc,state,last_send desc,client';
		$db->Query($sql);
		$R=array(); while ($r=$db->NextRecord()) {
			$r['cur_sent']=(isset($C[$r['client']]))?1:0;
			if (isset($R[$r['client']])){
				$R[$r['client']][]=$r;
			} else $R[$r['client']]=array($r);
		}
		
		$design->assign('send_clients',$R);
		$design->assign('refresh',30*$cont);
		if ($cont) {
			trigger_error('Отправка следующих 5ти счетов произойдёт через 30 секунд');
			trigger_error('<a href="?module=send">Остановить отправку</a>');
		}
		$design->AddMain('send/main.tpl');
	}
	
	
	function to_client($client,$is_test = 1){
		global $db;		
		$sql='select * from newbill_send where
					(client="'.$client.'") and ((state="ready") || (state="sent") || (state="error")) &&
					(!last_send || (last_send+INTERVAL 1 DAY < NOW()))';
		$db->Query($sql);
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		
		$subj="яВЕРЮ";
		$body="сБЮФЮЕЛШЕ цНЯОНДЮ!" . "\n" . "нРОПЮБКЪЕЛ бЮЛ ЯВЕРЮ МЮ НОКЮРС СЯКСЦ:" . "\n";
		foreach ($R as $r){
			$bill = new Bill($r['bill_no']);
			$R=array('obj'=>'bill','source'=>2,'curr'=>'USD','bill'=>$r['bill_no']);
			$R['client']=$bill->Get('client_id');
			$body.=LK_PATH.'docs/?bill='.udata_encode_arr($R)."\n";
		}
		$body.="\n оПНЯХЛ ЯБНЕБПЕЛЕММН ХУ НОКЮРХРЭ.\n\n";
		
		$sql='select * from clients where client="'.$client.'"';
		$db->Query($sql);
		$r=$db->NextRecord();
		$headers = "From: MCN Info <info@mcn.ru>\n";
		$headers.= "Content-Type: text/plain; charset=windows-1251\n";
		
		//##########################################
//		$r['email']='andreys75@mcn.ru';
		$r['email']=str_replace(';',',',$r['email']);
		if ((defined('MAIL_TEST_ONLY') && (MAIL_TEST_ONLY==1)) || $is_test) $r['email']='shepik@yandex.ru, mak@mcn.ru';

		error_close();
		ob_start();
		$msg='Адрес получателя: '.$r['email'].'<br>';
		if (!$r['email']) $msg='Адрес получателя не указан<br>';
		
		if ($r['email'] && (mail ($r['email'],$subj,$body,$headers))){
			$sql='update newbill_send set state="sent",last_send=NOW(),message="'.$msg.'" where
						(client="'.$client.'") and ((state="ready") || (state="sent") || (state="error"))';
			$db->Query($sql);
		} else {
			$sql='update newbill_send set state="error",last_send=NOW(),message="'.$msg.AddSlashes(ob_get_contents()).'" where
						(client="'.$client.'") and ((state="ready") || (state="sent"))';
			$db->Query($sql);
		}
		ob_end_clean();
		error_init();
	}
}
	
?>
