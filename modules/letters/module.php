<?
class m_letters {
	var $rights=array(
					'letters'		=>	array('Рассылка','w','работа с рассылкой'),
				);
	var $actions=array(
					'default'		=> array('letters','w'),
					'filter'		=> array('letters','w'),	//список клиентов
					'assign'		=> array('letters','w'),	//назначение клиентов письмам
					'unassign'		=> array('letters','w'),	//удаление клиентов
					'fupload'		=> array('letters','w'),
					'fdelete'		=> array('letters','w'),
					'fassign'		=> array('letters','w'),	//назначение
					'funassign'		=> array('letters','w'),	//назначение
					'lview'			=> array('letters','w'),	//просмотр-редактирование письма
					'lapply'		=> array('letters','w'),	//собственно изменение письма
					'ldelete'		=> array('letters','w'),	//удаление
					'process'		=> array('letters','w'),
		
				);

	var $menu=array(
					array('Рассылка',	'default',	''),
				);

	function m_letters(){
	}
	function Install(){
		return $this->rights;
	}
	function GetPanel(){
		global $design,$user;
		$R=array();
		foreach($this->menu as $val){
			$act=$this->actions[$val[1]];
			if (access($act[0],$act[1])) $R[]=array($val[0],'module=letters&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
		}
		if (count($R)>0){
			$design->AddMenu('Рассылка',$R);
		}
	}
	
	function GetMain($action,$fixclient){
		global $design,$db,$user;
		if (!isset($this->actions[$action])) return;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		call_user_func(array($this,'letters_'.$action),$fixclient);
	}
	
	function letters_default(){
		global $db,$design;
		$db->Query('select send_letters.* from send_letters order by send_letters.id');
		$L=array(); while ($r=$db->NextRecord()) $L[]=$r;
		foreach ($L as $i=>$r){
			$db->Query('select count(*) from send_assigns where id_letter='.$r['id'].' and state="sent"');
			$v=$db->NextRecord();
			$L[$i]['cnt_sent']=$v[0];
			$db->Query('select count(*) from send_files where id_letter='.$r['id'].'');
			$v=$db->NextRecord();
			$L[$i]['cnt_files']=$v[0];
			$db->Query('select count(*) from send_assigns where id_letter='.$r['id']);
			$v=$db->NextRecord();
			$L[$i]['cnt_total']=$v[0];
		}
		$design->assign('letters_letters',$L);
		
		$F=$this->get_files();
		$design->assign('letters_files',$F);

		$design->AddMain('letters/main.tpl');

	}
	
	function letters_filter(){
		global $db,$design;
		$filter=get_param_protected('filter');
		$letter=get_param_integer('letter');
		$design->assign('letters_letter',$letter);
		$filter_param=get_param_protected('filter_param');
		$C=array();
		if ($filter=='number'){
			$db->Query('select clients.* from usage_ip_ports INNER JOIN routes ON (routes.port_id=usage_ip_ports.id) and (routes.actual_from<NOW()) and (routes.actual_to>NOW()) INNER JOIN clients ON (clients.client=usage_ip_ports.client) where (usage_ip_ports.port="mgts") and (usage_ip_ports.node LIKE "'.$filter_param.'%") group by usage_ip_ports.client order by client');
			while ($r=$db->NextRecord()) $C[$r['client']]=$r;
		} else if ($filter=='internet') {
			$db->Query('select clients.* from usage_ip_ports INNER JOIN routes ON (routes.port_id=usage_ip_ports.id) and (routes.actual_from<NOW()) and (routes.actual_to>NOW()) INNER JOIN clients ON (clients.client=usage_ip_ports.client) where (usage_ip_ports.port="mgts") group by usage_ip_ports.client order by clients.client');
			while ($r=$db->NextRecord()) $C[$r['client']]=$r;
		} else if ($filter=='voip'){
			$db->Query('select clients.* from usage_voip INNER JOIN clients ON (clients.client=usage_voip.client) where (actual_from<NOW()) and (actual_to>NOW()) group by client order by clients.client');
			while ($r=$db->NextRecord()) $C[$r['client']]=$r;
		} else if ($filter=='email') {
			$db->Query('select clients.* from emails INNER JOIN clients ON (clients.client=emails.client) where (actual_from<NOW()) and (actual_to>NOW()) group by client order by clients.client');
			while ($r=$db->NextRecord()) $C[$r['client']]=$r;
		} else if ($filter=='router') {
			$db->Query('select clients.* from usage_ip_ports INNER JOIN routes ON (routes.port_id=usage_ip_ports.id) and (routes.actual_from<NOW()) and (routes.actual_to>NOW()) INNER JOIN clients ON (clients.client=usage_ip_ports.client) where (usage_ip_ports.node="'.$filter_param.'") group by usage_ip_ports.client order by clients.client');
			while ($r=$db->NextRecord()) $C[$r['client']]=$r;
		} else if ($filter=='add') {
			$filter_add=get_param_raw('filter_add');
			if (count($filter_add)){
				$V=array();
				foreach ($filter_add as $i) if ($i==(int)$i) $V[]=$i;
				$V=implode(',',$V);
				$db->Query('select description from bill_monthlyadd_reference where id IN ('.$V.')');
				$V=array(); while ($r=$db->NextRecord()) $V[]=str_protect($r[0]);
				$V=implode('","',$V);
				$db->Query('select clients.* from bill_monthlyadd INNER JOIN clients ON (clients.client=bill_monthlyadd.client) where (actual_from<NOW()) and (actual_to>NOW()) and (description IN ("'.$V.'")) group by client order by clients.client');
				while ($r=$db->NextRecord()) $C[$r['client']]=$r;
			}
		} else if ($filter=='firma') {
			if ($filter_param!='mcn') $filter_param='markomnet';
			$db->Query('select * from clients where firma="'.$filter_param.'" order by client');
			while ($r=$db->NextRecord()) $C[$r['client']]=$r;
		} else return $this->letters_default();
		
		$design->assign('letters_clients',$C);

		$design->AddMain('letters/filter.tpl');
	}

	function letters_assign($fixclient){
		global $db,$design;
		$clients=get_param_raw('clients');
		$flag=get_param_raw('flag');
		$emails=get_param_raw('emails');
		$letter=get_param_integer('letter');
		if (!$letter || !$clients || !$flag || !$emails || !is_array($flag) || !is_array($clients) || !is_array($emails)) return $this->letters_default();
		foreach ($clients as $i=>$c) if (isset($flag[$i]) && $flag[$i] && isset($emails[$i]) && $emails[$i]){
			@$db->Query('insert into send_assigns (id_letter,client,state,message) values ('.$letter.',"'.$c.'","ready","'.$emails[$i].'")');
		}
		return $this->letters_lview($fixclient,$letter);
	}
	
	function letters_unassign($fixclient){
		global $db,$design;
		$letter=get_param_integer('letter');
		@$db->Query("delete from send_assigns where ".
						"(id_letter={$letter}) AND ".
						"(state='ready' OR state='error')"
					);
		return $this->letters_lview($fixclient,$letter);
	}
	
	function letters_lview($fixclient,$letter='',$LA=''){
		global $db,$design;
		if (!$letter) $letter=get_param_integer('letter');
		$F=$this->get_files();
		$design->assign('letters_files',$F);
		if ($letter){
			$db->Query('select * from send_letters where id='.$letter);
			if (!($L=$db->NextRecord())) return;
			$design->assign('letter',$L);
			
			$db->Query('select filename from send_files where id_letter='.$letter);
			$LF=array(); while ($r=$db->NextRecord()) {
				if (isset($F[$r[0]])) {
					$LF[]=$F[$r[0]];
				} else {
					$LF[]=array($r[0],'файл не существует');	
				}
			}
			$design->assign('letter_files',$LF);
			
			if (!$LA){
				$db->Query('select * from send_assigns where id_letter='.$letter.' order by state');
				$LA=array(); while ($r=$db->NextRecord()) $LA[]=$r;
			}
			$design->assign('letter_assigns',$LA);
		} else {
			$design->assign('letter',array('body'=>'Текст письма','subject'=>'Тема письма','id'=>0));
			$design->assign('letter_files',array());
		}

		$db->Query('select * from bill_monthlyadd_reference');
		$R=array(); while ($r=$db->NextRecord()) $R[]=$r;
		$design->assign('letter_services',$R);

		$db->Query('select * from tech_routers order by router');
		$R=array(); while ($r=$db->NextRecord()) $R[$r['router']]=$r;
		$design->assign('routers',$R);


		$design->AddMain('letters/letter.tpl');	
	}

	function letters_lapply($fixclient){
		global $db,$design;
		$letter=get_param_integer('letter');
		$body=get_param_protected('body');
		$subject=get_param_protected('subject');
		if ($letter){
			$db->Query('update send_letters set body="'.$body.'", subject="'.$subject.'" where id='.$letter);
		} else {
			$db->Query('insert into send_letters (body,subject) values ("'.$body.'","'.$subject.'")');
			$letter=$db->GetInsertId();
		}
		return $this->letters_lview($fixclient,$letter);
	}
	function letters_ldelete($fixclient){
		global $db,$design;
		$letter=get_param_integer('letter');
		if ($letter){
			$db->Query('delete from send_assigns where id_letter='.$letter);
			$db->Query('delete from send_files where id_letter='.$letter);
			$db->Query('delete from send_letters where id='.$letter);
		}
		return $this->letters_default($fixclient);
	}
	function letters_fassign($fixclient){
		global $db,$design;
		$letter=get_param_integer('letter');
		$filename=get_param_protected('filename');
		@$db->QueryX('insert into send_files (id_letter,filename) values ('.$letter.',"'.$filename.'")');
		return $this->letters_lview($fixclient,$letter);
	}
	function letters_fdelete($fixclient){
		global $db,$design;
		$filename=get_param_protected('filename');
		unlink(LETTER_FILES_PATH.$filename);
		@$db->Query('delete from send_files where (filename="'.$filename.'")');
		return $this->letters_default($fixclient);
	}

	function letters_fupload($fixclient){
		global $db,$design;
		if (isset($_FILES['file'])) {
			$file=$_FILES['file'];
			move_uploaded_file($file['tmp_name'],LETTER_FILES_PATH.basename($file['name']));
		}
		return $this->letters_default($fixclient);
	}
	function letters_fupload2($fixclient){
		global $db,$design;
		$letter=get_param_integer('letter');
		if (isset($_FILES['file'])) {
			$file=$_FILES['file'];
			move_uploaded_file($file['tmp_name'],LETTER_FILES_PATH.basename($file['name']));
			$filename=basename($file['name']);
			@$db->QueryX('insert into send_files (id_letter,filename) values ('.$letter.',"'.$filename.'")');
		}
		return $this->letters_lview($fixclient,$letter);
	}

	
	function letters_funassign($fixclient){
		global $db,$design;
		$letter=get_param_integer('letter');
		$filename=get_param_protected('filename');
		@$db->QueryX('delete from send_files where (id_letter='.$letter.') and (filename="'.$filename.'")');
		return $this->letters_lview($fixclient,$letter);
	}
	function letters_process($fixclient){
		global $design,$db;
		include INCLUDE_PATH."class.phpmailer.php";
		include INCLUDE_PATH."class.smtp.php";
		$letter=get_param_integer('letter');
		$is_test=get_param_integer('test',1);
		$cont=get_param_integer('cont',0);

		$db->Query('select * from send_letters where id='.$letter);
		if (!($L=$db->NextRecord())) return;
		
		$F=$this->get_files();
		$db->Query('select filename from send_files where id_letter='.$letter);
		$LF=array(); while ($r=$db->NextRecord()) $LF[]=$F[$r[0]];

		$db->Query('select * from send_assigns where id_letter='.$letter.' order by state');
		$LA=array(); while ($r=$db->NextRecord()) $LA[$r['client']]=$r;

		for ($i=0;$i<5;$i++){
			$db->Query('select client,NOW() as n from send_assigns where (id_letter='.$letter.') and ((state="ready") or (state="error")) and (!last_send || (last_send+INTERVAL 1 DAY < NOW())) LIMIT 1');
			$b=0;
			if ($r=$db->NextRecord()) {
				$b=1;
				$client=$r[0];
				$LA[$client]['last_send']=$r['n'];
				$db->Query('select * from clients where client="'.$client.'"');
				if (!($C=$db->NextRecord())) $b=0;
			}
			if ($b){
				$db->Query('update send_assigns set state="sent" where (id_letter='.$letter.') and (client="'.$client.'")');
				$LA[$client]['cur_sent']=1;
				$LA[$client]['state']='sent';

				$Mail = new PHPMailer();
				$Mail->SetLanguage("ru","include/");
				$Mail->CharSet = "utf-8";
				$Mail->From = "info@mcn.ru";
				$Mail->FromName="Markomnet";
				$Mail->Mailer='smtp';
				$Mail->Host=SMTP_SERVER;

				foreach ($LF as $f) {
					$Mail->AddAttachment(LETTER_FILES_PATH.$f[0]);
				}
				if ((defined('MAIL_TEST_ONLY') && (MAIL_TEST_ONLY==1)) || $is_test) {
					$C['email']='shepik@yandex.ru';		//,andreys75@mcn.ru';
				}
				$C['email']=str_replace(';',',',$C['email']);
				$v=explode(',',$C['email']);
				if (count($v)){
					foreach ($v as $vi) $Mail->AddAddress(trim($vi));
				} else $Mail->AddAddress($C['email']);

				$Mail->ContentType='text/plain';
				$Mail->Body = $L['body'];
				$Mail->Subject = $L['subject'];
				if (!($Mail->Send())) {
					$msg=$Mail->ErrorInfo.'<br>'.'Адрес: '.$C['email'];
					$db->Query('update send_assigns set state="error",last_send=NOW(),message="'.AddSlashes($msg).'" where (id_letter='.$letter.') and (client="'.$client.'")');
					$LA[$client]['state']='error';
					$LA[$client]['message']=$msg;
				} else {
					$msg='Адрес: '.$C['email'];
					$db->Query('update send_assigns set state="sent",last_send=NOW(),message="'.AddSlashes($msg).'" where (id_letter='.$letter.') and (client="'.$client.'")');
					$LA[$client]['state']='sent';
					$LA[$client]['message']=$msg;
				}
			}
		}

		$design->assign('refresh',10*$cont);
		if ($cont) {
			trigger_error('Отправка следующих 5ти писем произойдёт через 10 секунд');
			trigger_error('<a href="?module=letters&action=lview&letter='.$letter.'">Остановить отправку</a>');
		}
		return $this->letters_lview($fixclient,$letter,$LA);
	}
	
	function get_files(){
		$R=array();
		$d=dir(LETTER_FILES_PATH);
		if ($d) {
			while (false !== ($entry = $d->read())) if ($entry!='.' && $entry!='..') {
				$R[$entry]=array($entry,filesize(LETTER_FILES_PATH.$entry));
			}
			$d->close();
		} else trigger_error("Ошибка работы с директорией ".LETTER_FILES_PATH);
		return $R;
	}
	
	
	function letters_send(){
		global $design;
		include INCLUDE_PATH.'Mail.php';
		$emails=$this->get_emails();

		$todo=get_param_protected("todo");

		$letter=$design->fetch('letters/letter1.tpl');

		$design->assign("letter",str_replace("\n","<br>",$letter));

		$from="support@mcn.ru";
		$subject="MCN (Предупреждение о перерыве в услуге доступа в интернет)";
		$report='';
		
		if ($todo=="send"){
			foreach($emails as $email){
				if (mail("$email", "$subject", $letter,"From: $from\r\n")){
					$report.="Письмо отправлено $email <br>";
				} else {
					$report.= "Письмо <b>НЕ</b> отправлено $email <br>";
				}
			}
			$design->assign('report',$report);
			$design->AddMain('letters/report.tpl');
			return;
		}

		$design->AddMain('letters/letters.tpl');
	}

	function get_emails(){
		global $db;
		$emails=array();

		$query="Select email from clients where status='work' and email<>''";
		$db->Query($query);

		while ($r=$db->NextRecord()){
			$emails[]=$r['email'];
		};

		$query="Select local_part, domain from emails where actual_to>NOW()";
		$db->Query($query);

		while ($r=$db->NextRecord()){
			$emails[]=$r['local_part']."@".$r['domain'];
		};

		foreach($emails as &$email){
			$email=str_replace(" ",'',$email);
			$email=str_replace(";",',',$email);
		}

		return $emails;
	}
	


}
?>