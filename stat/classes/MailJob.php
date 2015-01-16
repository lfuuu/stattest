<?php

class MailJob {
	public $data = array();
	public $client = array();
	public $encoding = 'utf-8';
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
		if($this->encoding=='utf-8'){
			$s1 = ' от ';
			$s2 = ' г.: ';
		}else{
			$s1 = ' НР ';
			$s2 = ' Ц.: ';
		}

		if($match[1]=='U')
		{
			$pay_flag = 'AND `is_payed`= 0';
		} elseif($match[1]=='P') {
			$pay_flag = 'AND `is_payed`= 2';
		}elseif($match[1]=='N') {
			$pay_flag = 'AND (`is_payed`= 2 OR `is_payed`= 0)';
		} else
			$pay_flag = '';

		$query = "
			SELECT
				*
			FROM
				`newbills`
			WHERE
				`client_id` = ".$this->client['id']."
			AND 
				`sum` > 0 
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
            list($b_akt, $b_sf, $b_upd) = m_newaccounts::get_bill_docs($bill);
            /*
            if($b_sf[1]) $T .="\nСчет-фактура ".$r['bill_no']."-1: ".$this->get_object_link('invoice',$r['bill_no'],1);
            if($b_sf[2]) $T .="\nСчет-фактура ".$r['bill_no']."-2: ".$this->get_object_link('invoice',$r['bill_no'],2);
            if($b_sf[3]) $T .="\nСчет-фактура ".$r['bill_no']."-3: ".$this->get_object_link('invoice',$r['bill_no'],3);
            if($b_sf[5]) $T .="\nСчет-фактура ".$r['bill_no']."-4: ".$this->get_object_link('invoice',$r['bill_no'],5);
            if($b_sf[6]) $T .="\nСчет-фактура ".$r['bill_no']."-5: ".$this->get_object_link('invoice',$r['bill_no'],6);

            if($b_akt[1]) $T .="\nАкт ".$r['bill_no']."-1: ".$this->get_object_link('akt',$r['bill_no'],1);
            if($b_akt[2]) $T .="\nАкт ".$r['bill_no']."-2: ".$this->get_object_link('akt',$r['bill_no'],2);
            if($b_akt[3]) $T .="\nАкт ".$r['bill_no']."-3: ".$this->get_object_link('akt',$r['bill_no'],3);
             */

            if($b_upd[1]) $T .="\nУПД ".$r['bill_no']."-1: ".$this->get_object_link('upd',$r['bill_no'],1);
            if($b_upd[2]) $T .="\nУПД ".$r['bill_no']."-2: ".$this->get_object_link('upd',$r['bill_no'],2);

            if($b_sf[4]) $T .="\nТоварная накладная ".$r['bill_no'].": ".$this->get_object_link('lading',$r['bill_no']);

            $T .="\n";
		}
		return $T;
	}
	
	
	public function Template($str,$format = 'text'){


		$text = $this->data[$str];
		if($this->encoding!='utf-8')
			$text = convert_cyr_string($text,'k','w');
		$text = str_replace(
			array('%CLIENT%','%CLIENT_NAME%'),
			array($this->client['client'],$this->client['company_full']),
			$text
		);
		$text = preg_replace_callback('/%(A)BILL(\d{4}-\d{2}(?:-\d+)?)%/',array($this,'_get_bills'),$text);
		$text = preg_replace_callback('/%(U)BILL(\d{4}-\d{2}(?:-\d+)?)%/',array($this,'_get_bills'),$text);
		$text = preg_replace_callback('/%(P)BILL(\d{4}-\d{2}(?:-\d+)?)%/',array($this,'_get_bills'),$text);
		$text = preg_replace_callback('/%(N)BILL(\d{4}-\d{2}(?:-\d+)?)%/',array($this,'_get_bills'),$text);
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
                $Mail->AddAttachment($v['path'], "=?utf-8?B?".base64_encode($v['name'])."?=", "base64", $v['type']);
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
