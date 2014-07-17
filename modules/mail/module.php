<?
class m_mail{
	var $is_active = 0;
	var $rights=array(
		'mail' => array('Письма клиентам','w,r','работа с рассылкой,просмотр PM'),
	);
	var $actions=array(
		'default'		=> array('mail','r'),
		'list'			=> array('mail','w'),
		'edit'			=> array('mail','w'),
		'view'			=> array('mail','w'),
		'remove'		=> array('mail','w'),
		'preview'		=> array('mail','w'),
		'state'			=> array('mail','w'),
		'client'		=> array('mail','w'),
		'file_put'		=> array('mail','w'),
		'file_get'		=> array('mail','w'),
		'file_del'		=> array('mail','w')
	);

	var $menu=array(
		array('Почтовые задания',	'list',		''),
		array('Просмотр сообщений',	'default',	''),
	);

	function m_mail(){}
	function Install(){
		return $this->rights;
	}
	function GetPanel(){
		global $design,$user,$fixclient_data,$db;
		$R=array();
		foreach($this->menu as $val){
			$act=$this->actions[$val[1]];
			if (access($act[0],$act[1])) $R[]=array($val[0],'module=mail&action='.$val[1].(isset($val[2])?$val[2]:''), (isset($val[3])?$val[3]:''),(isset($val[4])?$val[4]:''));
		}
		if (!count($R)) return;
		if (access('mail','r') && !access('mail','w') && isset($fixclient_data)) {
			if ($this->is_active==0 && $db->GetRow('select * from mail_object where client_id="'.$fixclient_data['id'].'" AND object_type="PM" AND view_count=0 LIMIT 1')){
				trigger_error('<a href="?module=mail">У вас есть непросмотренные сообщения</a>');
			}
			$design->AddMenu('Просмотр сообщений',$R);
		} else {
			$design->AddMenu('Письма клиентам',$R);
		}
	}

	function GetMain($action,$fixclient){
		global $design,$db,$user;
		if (!isset($this->actions[$action])) return;
		$this->is_active = 1;
		$act=$this->actions[$action];
		if (!access($act[0],$act[1])) return;
		call_user_func(array($this,'mail_'.$action),$fixclient);
	}
	function mail_list(){
		global $db,$design;
		$L = $db->AllRecords('
			SELECT
				mail_job.*,
				COUNT(mail_letter.client) as cnt_total,
				SUM(IF(mail_letter.letter_state="sent",1,0)) as cnt_sent
			FROM
				mail_job
			LEFT JOIN
				mail_letter
			ON
				mail_letter.job_id = mail_job.job_id
			GROUP BY
				job_id
			ORDER BY
				job_id desc
            limit 50
		');
		$design->assign('mail_job',$L);
		$design->AddMain('mail/list.tpl');
	}
	function mail_edit(){
		global $db,$design,$user;
		$id=get_param_integer('id');

		$R = array(
			'template_body'=>get_param_raw('body'),
			'template_subject'=>get_param_raw('subject')
		);

		$R['date_edit'] = array('NOW()');
		$R['user_edit'] = $user->Get('user');

		if($id){
			$query = '
				UPDATE
					`mail_job` `mj`
				SET
					`mj`.`template_body` = "'.mysql_escape_string($R['template_body']).'",
					`mj`.`template_subject` = "'.mysql_escape_string($R['template_subject']).'",
					`mj`.`date_edit` = NOW(),
					`mj`.`user_edit` = "'.$R['user_edit'].'"
				WHERE
					`mj`.`job_id` = '.$id;
			$db->Query($query);
		}else{
			$query = '
				INSERT INTO	`mail_job`
					(`template_subject`,`template_body`,`date_edit`,`user_edit`)
				VALUES
					("'.mysql_escape_string($R['template_subject']).'","'.mysql_escape_string($R['template_body']).'",NOW(),"'.$R['user_edit'].'")
			';
			$db->Query($query);
			$id = $db->GetInsertId();
		}
		if($design->ProcessEx('errors.tpl'))
			header('Location: ?module=mail&action=view&id='.$id);
	}
	function mail_view($fixclient){
		global $db,$design;
		if(!($id=get_param_integer('id'))){
			$design->assign(
				'template',
				array(
					'template_body'=>'Текст письма',
					'template_subject'=>'Тема письма',
					'job_id'=>null
				)
			);
		}else{
			$design->assign(
				'template',
				$r = $db->GetRow('select * from mail_job where job_id='.$id)
			);
			$L = $db->AllRecords('
				select
					L.*,
					C.id as client_id
				from
					mail_letter as L
				inner join
					clients as C
				ON
					C.client=L.client
				where
					job_id='.$id.'
				order by
					letter_state,
					L.client
			');
			foreach($L as &$l){
				if($l['letter_state']=='sent'){
					$l['objects'] = $db->AllRecords('
						select
							*
						from
							mail_object
						where
							job_id='.$id.'
						AND
							client_id='.$l['client_id']
					);
				}
			}
			unset($l);
			$design->assign('mail_letter',$L);
			require_once('mailFiles.php');
			$Files = new mailFiles($id);
			$files = $Files->getFiles();
			$design->assign('files', $files);
			$design->assign('job_id', $id);
		}

		$design->AddMain('mail/view.tpl');
	}
	function mail_remove(){
		global $db,$design;
		$id=get_param_integer('id');
		$db->Query('delete from mail_job where job_id='.$id);
		$db->Query('delete from mail_letter where job_id='.$id);
		if ($design->ProcessEx('errors.tpl')) header('Location: ?module=mail');
	}
	function mail_client(){
		global $db,$design;
		if(!($id=get_param_integer('id')))
			return;
		$clients = get_param_raw('clients',array());
		$flag = get_param_raw('flag',array());
		$flag2 = get_param_raw('flag2',array());
		if(is_array($clients)){
			$V = array();
			$db->Query('select client from mail_letter where job_id='.$id);
			while($r = $db->NextRecord())
				$V[$r['client']] = 1;
			$str1 = '';
			$str2 = '';
			foreach($clients as $k=>$v){
				if(isset($V[$v]) && !isset($flag2[$k])){
					$str1.=($str1?',':'').'"'.$v.'"';
					unset($V[$v]);
				}
				if((isset($flag[$k]) || isset($flag2[$k])) && !isset($V[$v])){
					$str2.=($str2?',':'').'('.$id.',"'.$v.'")';
				}
			}
			if($str1)
				$db->Query('delete from mail_letter where job_id='.$id.' AND client IN ('.$str1.')');
			if($str2)
				$db->Query('insert into mail_letter (job_id,client) values '.$str2);
		}

		$W = array('AND');
		$J = array();
		$filter = get_param_raw('filter',array());
		foreach($filter as $type=>$p)
			if($p[0]!='NO')
				switch($type){
					case 'status':
						$W[] = 'C.status="'.addslashes($p[0]).'"';
						break;
					case 'manager':
						$W[] = 'C.manager="'.addslashes($p[0]).'"';
						break;
					case 'bill':
						$W[] = 'B.bill_date>="'.addslashes($p[1]).'"';
						$W[] = 'B.bill_date<="'.addslashes($p[2]).'"';
						$J[] = 'INNER JOIN newbills as B ON B.client_id=C.id';
						if($p[0]==2){
							$W[] = 'B.is_payed!=1';
							$W[] = '(select sum(sum_rub) from newpayments where bill_no=B.bill_no) IS NULL';
							$W[] = 'B.`sum` > 0';
						}
						if($p[0]==3){
							$W[] = 'B.is_payed<>1';
							$W[] = 'B.`sum`	- (select sum(if(B.currency="USD",sum_rub/payment_rate,sum_rub)) from newpayments where bill_no=B.bill_no group by bill_no) > 1';
						}
						break;
                    case 's8800':
                        $J[] = 'left join usage_8800 u8 on (u8.client = C.client)';
                        $W[] = 'u8.id is '.($p[0] == 'with' ? ' not ' : '').'null';
                        
                        break;
				}
		$design->assign('mail_filter',$filter);
		$design->assign('mail_id',$id);

		$m=array();
		$GLOBALS['module_users']->d_users_get($m,'manager');

		$design->assign(
			'f_manager',
			$m
		);

		$design->assign('f_status',$GLOBALS['module_clients']->statuses);

		$J[] = 'LEFT JOIN client_contacts as M ON M.type="email" AND M.client_id=C.id AND M.is_active=1 AND M.is_official=1';
		$ack = get_param_raw('ack',0);
		$C = array();
		$R = $db->AllRecords('
			select
				C.*,
				letter_state,
				1 as selected,
				0 as filtered
			from
				mail_letter as L
			INNER JOIN
				clients as C
			ON
				C.client=L.client
			WHERE
				L.job_id='.$id.'
			ORDER BY
				selected desc,
				C.client asc
		');
		foreach($R as $r)
			$C[$r['id']] = $r;
		if($ack || (count($W)>1)){
			$W[] = 'C.client!=""';
			$R = $db->AllRecords($q='
				select
					C.*,
					0 as selected,
					IF(M.data="",0,1) as filtered
				from
					clients as C
				'.implode(' ',$J).'
				WHERE
				'.MySQLDatabase::Generate($W).'
				GROUP BY
					C.id
				ORDER BY
					C.client
			');

			foreach($R as $r){
				if(!isset($C[$r['id']]))
					$C[$r['id']] = $r;
				else
					$C[$r['id']]['filtered']=$r['filtered'];
			}
		}

		$design->assign('mail_clients',$C);
		$design->AddMain('mail/filter.tpl');
	}
	function mail_file_put($fixclient) {
		require_once('mailFiles.php');
		global $design;
		if(!($job_id=get_param_integer('job_id')))
			return;

		$Files=new mailFiles($job_id);
		$Files->putFile();
		if ($design->ProcessEx('errors.tpl')) header('Location: ?module=mail&action=view&id='.$job_id);
	}
	function mail_file_get($fixclient) {
		require_once('mailFiles.php');
		global $design;
		$job_id = get_param_integer('job_id');
		if (!$job_id) return;
		$Files=new mailFiles($job_id);
		if ($f = $Files->getFile(get_param_protected('file_id'))) {
			header("Content-Type: " . $f['type']);
			header("Pragma: ");
			header("Cache-Control: ");
			header('Content-Transfer-Encoding: binary');
			header('Content-Disposition: attachment; filename="'.iconv("KOI8-R","CP1251",$f['name']).'"');
			header("Content-Length: " . filesize($f['path']));
			readfile($f['path']);
			$design->ProcessEx();
		}
	}
	function mail_file_del($fixclient) {
		require_once('mailFiles.php');
		global $design;
		$job_id = get_param_integer('job_id');
		if (!$job_id) return;
		$Files=new mailFiles($job_id);
		$Files->deleteFile(get_param_protected('file_id'));
		if ($design->ProcessEx('errors.tpl')) header('Location: ?module=mail&action=view&id='.$job_id);
	}
	function mail_state($fixclient) {
		global $db,$design;
		$id=get_param_integer('id');
		$state = get_param_raw('state');
		$db->Query('update mail_job set job_state="'.$state.'" where job_id='.$id);

        if($state == "ready") //реальная отправка писем
        {
            $this->_publishClientBills($id);
        }

		if ($state=='PM') 
        {
			$job = new MailJob($id);
			$R=$db->AllRecords('select * from mail_letter where job_id='.$id);
			foreach ($R as $r) 
            {
                $job->assign_client($r['client']);
                $job->get_object_link('PM',$id);
                $db->QueryUpdate(
                        'mail_letter',
                        array('job_id', 'client'),
                        array(
                            'job_id' => $id,
                            'client' => $r['client'],
                            'letter_state' => 'sent',
                            'send_message' => '',
                            'send_date' => array('NOW()')
                            )
                        );
            }

		}
		if ($design->ProcessEx('errors.tpl')) header('Location: ?module=mail&action=view&id='.$id);
	}

    function _publishClientBills($jobId)
    {
        global $db;

        $db->Query(
        "update 
            mail_letter m, 
            clients c, 
            newbills b 
        set 
            is_lk_show=1 
        where 
                job_id = '".$jobId."'
            and c.client=m.client 
            and b.client_id = c.id 
            and is_lk_show =0");


    }

	function mail_default($fixclient,$pre = 0) {
		global $db,$design,$user,$fixclient_data;
        return $this->mail_list($fixclient);
		if (!$fixclient) return;
		$R = $db->AllRecords('select * from mail_object where client_id="'.$fixclient_data['id'].'" AND object_type="PM"'.($pre?' AND view_count=0':'').' ORDER BY object_id');
		foreach ($R as $r) {
			$job = new MailJob($r['job_id']);
			$job->assign_client_data($fixclient_data);
			if ($user->GetAsClient()) $db->Query('update mail_object set view_count=view_count+1, view_ts = IF(view_ts=0,NOW(),view_ts) where object_id='.$r['object_id']);
			$design->assign('pm_subject',$job->Template('template_subject','html'));
			$design->assign('pm_body',$job->Template('template_body','html'));
			$design->AddMain('mail/pm.tpl',1);
			unset($job);
		}
	}
	function mail_preview($fixclient){
		global $db,$design;
		$id=get_param_integer('id');
		$client = get_param_raw('client');
		$obj = new MailJob($id);
		$obj->assign_client($client);
		echo "<h2>".$obj->Template('template_subject')."</h2>";
		echo "<pre>"; echo $obj->Template('template_body');echo "</pre>";
		$design->ProcessEx('errors.tpl');
	}
}

class MailJob {
	public $data = array();
	public $client = array();
	public $encoding = 'koi8-r';
	public $emails = array();
	private static $prepared = 0;

	public function __construct($id = null) {
		global $db;
		if($id!==null)
			$this->data = $db->GetRow('select * from mail_job where job_id='.$id);
	}
	public static function GetObjectP() {
		return self::GetObject(
			get_param_raw('o'),
			get_param_raw('k')
		);
	}
	public static function GetObject($object_id,$key){
		global $db;
		$v = $db->GetRow('select * from mail_object where object_id='.$object_id);
		$key1 = self::get_object_key($v);
		if($key1==$key)
			return $v;
		else
			return null;
	}
	public function assign_client($client){
		global $db;
		$this->client = $db->GetRow('select * from clients where client="'.$client.'"');
		$this->emails = array();
		if(!$this->client)
			return;
		$R = $db->AllRecords('
			select
				*
			from
				client_contacts
			where
				client_id='.$this->client['id'].'
			AND
				is_active=1
			AND
				is_official=1
			AND
				type="email"
		');
		foreach($R as $r){
			$r=str_replace(
				array(',',' '),
				array(';',';'),
				$r['data']
			);
			$r = explode(';',$r);
			foreach($r as $v)
				if(trim($v)!=""){
					$this->emails[] = trim($v);
				}
		}
	}
	public function assign_client_data($cdata){
		global $db;
		$this->client = $cdata;
	}
	private static function get_object_key($v){
		$k = md5(
			md5(
				md5($v['job_id']).$v['client_id']
			).$v['object_id']
		);
		return substr($k,0,8);
	}
	public function get_object_link($object_type,$object_param, $source = 2){
		global $db;
		$v = array();
		$v['job_id'] = $this->data['job_id'];
		$v['client_id'] = $this->client['id'];
		$v['object_type'] = $object_type;
		$v['object_param'] = $object_param;
        $v["source"] = $source;
		$ins = false;
		while(!($r = $db->QuerySelectRow('mail_object',$v))){
			if($ins)
				throw new Exception("Can't create object ".print_r($object,true));
			$ins = true;
			$v['object_id'] = $db->QueryInsert('mail_object',$v);
		}
		$k = self::get_object_key($r);
		//return WEB_ADDRESS.WEB_PATH.'mail.php?o='.$r['object_id'].'&k='.$k;
        return LK_PATH.'docs/?o='.$r['object_id'].'&k='.$k;
	}
	public function _get_assignments($match){

        global $db;
        $T = "";
        if($match[1] == "SOGL")
        {
	        if($db->GetValue("select id from test_operator.mcn_client where id = '".$this->client["id"]."'"))
	        {
				$T =
					"Соглашение о передаче прав и обязанностей: ".
					$this->get_object_link('assignment', $db->GetValue("select bill_no from newbills where client_id = '".$this->client["id"]."' order by bill_date desc limit 1"), (isset($match[2]) && $match[2] ? $match[2] : 4));
	        }
        }elseif($match[1] == "ORDER"){
        	$T = "Приказ о назначении: ".
        	$this->get_object_link('order', $db->GetValue("select bill_no from newbills where client_id = '".$this->client["id"]."' order by bill_date desc limit 1"));
       	}elseif($match[1] == "NOTICE"){
        	$T = "Уведомление о назначении: ".
        	$this->get_object_link('notice', $db->GetValue("select bill_no from newbills where client_id = '".$this->client["id"]."' order by bill_date desc limit 1"));
       	}elseif($match[1] == "DIRECTOR")
        {
        	$T = "Информационное письмо о смене генерального директора: ".
        	$this->get_object_link('new_director_info', $db->GetValue("select bill_no from newbills where client_id = '".$this->client["id"]."' order by bill_date desc limit 1"));
        }elseif($match[1] == "DOGOVOR") {
		$T = BillContract::getString($this->client["id"], time());
        }
        return $T;
    }

	public function _get_bills($match){
		global $db;

        require_once(INCLUDE_PATH."bill.php");
        require_once(MODULES_PATH."newaccounts/module.php");

		/*$W = array('AND');
		if($match[1]=='U')
			$W[] = 'is_payed!=1';
		$W[] = 'bill_date LIKE "'.$match[2].'-%"';
		$W[] = 'client_id = '.$this->client['id'];*/

		$T = '';
		if($this->encoding=='koi8-r'){
			$s1 = ' от ';
			$s2 = ' г.: ';
		}else{
			$s1 = ' НР ';
			$s2 = ' Ц.: ';
		}

		if($match[1]=='U')
			$pay_flag = 'AND
				`is_payed`<>1
			AND
				`sum` > 0
			AND
				(select sum(`sum_rub`) from `newpayments` where `bill_no`=`newbills`.`bill_no`) IS NULL';
		elseif($match[1]=='P')
			$pay_flag = 'AND
				`is_payed`<>1
			AND
				`newbills`.`sum`	- (select sum(if(`newbills`.`currency`="USD",`sum_rub`/`payment_rate`,`sum_rub`)) from `newpayments` where `bill_no`=`newbills`.`bill_no` group by `bill_no`) > 1';
		else
			$pay_flag = '';

		$query = "
			SELECT
				*
			FROM
				`newbills`
			WHERE
				`client_id` = ".$this->client['id']."
			AND
				`bill_date` BETWEEN '".$match[2]."-1'
							AND		DATE_ADD(DATE_ADD('".$match[2]."-1', INTERVAL 1 MONTH),INTERVAL -1 DAY)
			".$pay_flag;

		$rows = $db->AllRecords($query,null,MYSQL_ASSOC);

		foreach($rows as $r){//while($r = $db->NextRecord()){
			if(strlen($T)>0)
				$T .= "\n";
			$T .=
				"Счет ".$r['bill_no']. // номер счета
				$s1. // от
				date('d.m.Y',strtotime($r['bill_date'])). // дата счета
				$s2. // г.
				$this->get_object_link('bill',$r['bill_no']); // вот тут косяк. Здешняя библиотека sql не готова к таким зигзагам. Если счетов больше чем 1 - будет выход из цикла.

            $bill = new Bill($r["bill_no"]);
            list($b_akt, $b_sf) = m_newaccounts::get_bill_docs($bill);
            if($b_sf[1]) $T .="\nСчет-фактура ".$r['bill_no']."-1: ".$this->get_object_link('invoice',$r['bill_no'],1);
            if($b_sf[2]) $T .="\nСчет-фактура ".$r['bill_no']."-2: ".$this->get_object_link('invoice',$r['bill_no'],2);
            if($b_sf[3]) $T .="\nСчет-фактура ".$r['bill_no']."-3: ".$this->get_object_link('invoice',$r['bill_no'],3);
            if($b_sf[5]) $T .="\nСчет-фактура ".$r['bill_no']."-4: ".$this->get_object_link('invoice',$r['bill_no'],5);
            if($b_sf[6]) $T .="\nСчет-фактура ".$r['bill_no']."-5: ".$this->get_object_link('invoice',$r['bill_no'],6);

            if($b_akt[1]) $T .="\nАкт ".$r['bill_no']."-1: ".$this->get_object_link('akt',$r['bill_no'],1);
            if($b_akt[2]) $T .="\nАкт ".$r['bill_no']."-2: ".$this->get_object_link('akt',$r['bill_no'],2);
            if($b_akt[3]) $T .="\nАкт ".$r['bill_no']."-3: ".$this->get_object_link('akt',$r['bill_no'],3);

            if($b_sf[4]) $T .="\nТоварная накладная ".$r['bill_no'].": ".$this->get_object_link('lading',$r['bill_no']);

            $T .="\n";
		}
		return $T;
	}
	
	
	public function Template($str,$format = 'text'){


		$text = $this->data[$str];
		if($this->encoding!='koi8-r')
			$text = convert_cyr_string($text,'k','w');
		$text = str_replace(
			array('%CLIENT%','%CLIENT_NAME%'),
			array($this->client['client'],$this->client['company_full']),
			$text
		);
		$text = preg_replace_callback('/%(A)BILL(\d{4}-\d{2}(?:-\d+)?)%/',array($this,'_get_bills'),$text);
		$text = preg_replace_callback('/%(U)BILL(\d{4}-\d{2}(?:-\d+)?)%/',array($this,'_get_bills'),$text);
		$text = preg_replace_callback('/%(P)BILL(\d{4}-\d{2}(?:-\d+)?)%/',array($this,'_get_bills'),$text);
		$text = preg_replace_callback('/%(SOGL)_TELEKOM(\d{0,2})%/',array($this,'_get_assignments'),$text);
		$text = preg_replace_callback('/%(NOTICE)_TELEKOM%/',array($this,'_get_assignments'),$text);
		$text = preg_replace_callback('/%(ORDER)_TELEKOM%/',array($this,'_get_assignments'),$text);
		$text = preg_replace_callback('/%(DIRECTOR)_TELEKOM%/',array($this,'_get_assignments'),$text);
		$text = preg_replace_callback('/%(DOGOVOR)_TELEKOM%/',array($this,'_get_assignments'),$text);
		if($format=='html'){
			$text = nl2br(htmlspecialchars_($text));
		}
		return $text;
	}
	private static function SendPrepare(){
		include INCLUDE_PATH."class.phpmailer.php";
		include INCLUDE_PATH."class.smtp.php";
		self::$prepared = 1;
	}
	public function Send($emails = null){
		global $db;
		require_once('mailFiles.php');
		if(!self::$prepared)
			self::SendPrepare();

		if($emails===null){
			$emails = $this->emails;
		}elseif(!is_array($emails))
			$emails = array($emails);

		$Mail = new PHPMailer();
		$Mail->SetLanguage("ru","include/");
		$Mail->CharSet = $this->encoding;
		$Mail->From = ($this->data['job_state']=='news')?'news@mcn.ru':"info@mcn.ru";
		$Mail->FromName="MCN";
		$Mail->Mailer='smtp';
		$Mail->Host=SMTP_SERVER;
		foreach($emails as $adr)
			if($adr)
				$Mail->AddAddress($adr);
		$Mail->ContentType='text/plain';
		
		$Files = new mailFiles($this->data['job_id']);
		$files = $Files->getFiles(true);
		if (!empty($files))
		{
			foreach ($files as $v)
			{
				$Mail->AddAttachment($v['path'], $v['name'], "base64", $v['type']);
			}
		}
		
		$Mail->Subject = $this->Template('template_subject');
		$Mail->Body = $this->Template('template_body');

		$r = array('job_id'=>$this->data['job_id'],'client'=>$this->client['client']);
		if(!(@$Mail->Send())){
			$ret = $Mail->ErrorInfo;
			$r['send_message'] = $Mail->ErrorInfo;
			$r['letter_state'] = 'error';
		}else{
			$ret = true;
			$r['send_message'] = '';
			$r['letter_state'] = 'sent';
		}
		$r['send_date'] = array('NOW()');
		$db->QueryUpdate('mail_letter',array('job_id','client'),$r);
		return $ret;
	}
	function get_cur_state()
	{
	    global $db;
	    $res = $db->GetValue('select job_state from mail_job where job_id='.$this->data['job_id']);
	    
	    return $res;
	}
}
?>
