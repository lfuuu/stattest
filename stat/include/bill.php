<?php
class Bill{
	private $client_id;
	private $client_data = null;
	private $bill_no;
	private $bill_ts;
	private $bill;
	private $changed ;
	private $max_sort;
	private $_comment = null;
    private $bill_courier;
	public $negative_balance=false; //для 4й счет-фактуры - если недостаточно средств для проведения авансовых платежей

	public function SetComment($s) {
		$this->_comment=$s;
	}

	public function Client($v = '') {
		global $db;
		if (!$this->client_data)
        {
            $this->client_data = ClientCS::getOnDate($this->client_id, $this->bill["bill_date"]);
        }

		return ($v?($this->client_data[$v]):($this->client_data));
	}

    public function SetClientDate($date)
    {
           $this->client_data = ClientCS::getOnDate($this->client_id, $date);
    }

	public function __construct($bill_no,$client_id = '',$bill_date = '',$is_auto=1,$currency=null,$isLkShow=true, $isUserPrepay=false) {
		global $db;
		if ($bill_no){
			$this->bill_no=$bill_no;
			$r=$db->GetRow("
				select
					max(sort) as V
				from
					newbill_lines
				where
					bill_no='".$bill_no."'
			");
			$this->max_sort=($r?$r['V']:0);
		} else {
			$prefix=date("Ym",$bill_date);
			if ($r=$db->GetRow($q = "
				SELECT
					bill_no as suffix
				FROM
					newbills
				WHERE
					bill_no like '".$prefix."-%'
				ORDER BY
					bill_no DESC
				LIMIT 1
			"))
				$suffix=1+intval(substr($r['suffix'],7));
			else
				$suffix=1;

		    $this->bill_no=sprintf("%s-%04d",$prefix,$suffix);

		    if (is_array($client_id)) {
		    	$this->client_data=$client_id;
		    	$client_id=$client_id['id'];
		    	if (!$currency) $currency=$this->client_data['currency'];
		    } else if (!$currency) {
				$this->client_data=$db->GetRow("
					select
						*
					from
						clients
					where
						id='".$client_id."'
				");
				$currency=$this->client_data['currency'];
		    }
			$db->QueryInsert(
				"newbills",
				array(
					"client_id"=>$client_id,
					"currency"=>$currency,
					"bill_no"=>$this->bill_no,
					"bill_date"=>date('Y-m-'.($is_auto?'01':'d'),$bill_date),
					"nal" => $this->client_data["nal"],
                    "is_lk_show" => $isLkShow ? 1 : 0,
                    "is_user_prepay" => $isUserPrepay ? 1 : 0
				)
			);
		}

		$this->bill = $db->GetRow("
			select
				*,
				UNIX_TIMESTAMP(bill_date) as ts,
				UNIX_TIMESTAMP(doc_date) as doc_ts,
				UNIX_TIMESTAMP(bill_no_ext_date) as bill_no_ext_date 
			from
				newbills
			where
				bill_no='".$this->bill_no."'
		");

        // rename if rollback
        if($this->bill["is_rollback"]){
            switch($this->bill["state_1c"]) {
                case 'КОтгрузке': $this->bill["state_1c"] = "К поступлению"; break;
                case 'Отгружен': $this->bill["state_1c"] = "Принят"; break;
            }
        }
		$this->bill_ts=$this->bill['ts'];
		unset($this->bill['ts']);
		$this->client_id=$this->bill['client_id'];
        $this->bill_courier = $this->GetCourierName($this->bill["courier_id"]);
		$this->changed=0;
	}
	public function AddLine($currency,$title,$amount,$price,$type,$service='',$id_service='',$date_from='',$date_to='',$all4net_price=0,$overprice=array()){
		global $db;
		if($currency!=$this->bill['currency'])
			return false;
		$this->max_sort++;
		if(!$date_from && !$date_to){
            $date_from_ts = $this->bill_ts;
			$date_from = date('Y-m-01',$this->bill_ts);
            if($price < 0) {$date_from_ts = strtotime("-1 month", $this->bill_ts);$date_from = date("Y-m-01", $date_from_ts);}
			$d=getdate($date_from_ts);
			$moncount=cal_days_in_month(CAL_GREGORIAN, $d['mon'], $d['year']);
			$date_to = date('Y-m-'.$moncount,$date_from_ts);
		}
		$this->changed = 1;

        $nds = $this->Client("nds_zero") ? "1" : "1.18";

		$lpk = $db->QueryInsert(
			"newbill_lines",
			array(
				"bill_no"=>$this->bill_no,
				"sort"=>$this->max_sort,
				"item"=>$title,
				"amount"=>$amount,
				"price"=>$price,
                "sum" => round($amount*$price*$nds,2),
				"type"=>$type,
				"service"=>$service,
				"id_service"=>$id_service,
				'date_from'=>$date_from,
				'date_to'=>$date_to,
				'all4net_price'=>$all4net_price
			)
		,1);
		if($overprice && count($overprice)){
			foreach($overprice as $v){
				$v['bill_line_pk'] = $lpk;
				ServicePrototype::WriteOverprice($v);
			}
		}

		return true;
	}
	public function AddLines($R){
		$b=true;
		foreach($R as $r){
			if(isset($r[10]))
				$b1=$this->AddLine($r[0],$r[1],$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],$r[8],$r[9],$r[10]);
			elseif(isset($r[9]))
				$b1=$this->AddLine($r[0],$r[1],$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],$r[8],$r[9]);
			else
				$b1=$this->AddLine($r[0],$r[1],$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],$r[8]);
			$b=$b && $b1;
			if(!$b1)
				trigger_error('<font color=green>Невозможно добавить '.$r[1].'-'.$r[3].$r[0].'x'.$r[2].'</font>');
		}
		return $b;
	}
    public function SetNal($nal)
    {
        if ($this->bill["nal"] != $nal && in_array($nal, array("nal", "beznal","prov"))) {
            global $db,$user;
            $this->Set("nal", $nal);
			$db->QueryInsert("log_newbills",array('bill_no'=>$this->bill['bill_no'],'ts'=>array('NOW()'),'user_id'=>$user->Get('id'),'comment'=>"Именен предпологаемый тип платежа на ".$nal));
        }
    }
    public function SetExtNo($bill_no_ext)
    {
        if ($this->bill["bill_no_ext"] != $bill_no_ext) {
            global $db,$user;
            $this->Set("bill_no_ext", $bill_no_ext);
			$db->QueryInsert("log_newbills",array('bill_no'=>$this->bill['bill_no'],'ts'=>array('NOW()'),'user_id'=>$user->Get('id'),'comment'=>"Именен внешний номер на ".$bill_no_ext));
        }
    }
    public function SetExtNoDate($bill_no_ext_date = '0000-00-00')
    {
        if ($this->bill["bill_no_ext_date"] != $bill_no_ext_date) {
            global $db,$user;
            $this->Set("bill_no_ext_date", $bill_no_ext_date . ' 00:00:00');
            $comment = ($bill_no_ext_date) ? "Именена дата внешнего счета на ". $bill_no_ext_date : 'Удаление даты внешнего счета';
			$db->QueryInsert("log_newbills",array('bill_no'=>$this->bill['bill_no'],'ts'=>array('NOW()'),'user_id'=>$user->Get('id'),'comment'=>$comment));
        }
    }
    public function SetCourier($courierId){
        if ((int)$courierId != $courierId) return;
        if ($this->bill["courier_id"] != $courierId) {
            global $db,$user;
            $this->Set("courier_id", $courierId);
            $db->QueryUpdate("courier", array("id"), array("id" => $courierId, "is_used" => "1"));
			$db->QueryInsert("log_newbills",array('bill_no'=>$this->bill['bill_no'],'ts'=>array('NOW()'),'user_id'=>$user->Get('id'),'comment'=>"Назначен курьер ".Bill::GetCourierName($courierId)));
        }
    }
	public function EditLine($sort,$title,$amount,$price,$type) {
		global $db;
		$this->changed = 1;

        $nds = $this->Client("nds_zero") ? "1" : "1.18";

		$db->QueryUpdate("newbill_lines",array('bill_no','sort'),array(
                    'bill_no'=>$this->bill_no,
                    'sort'=>$sort,
                    'item'=>$title,
                    'amount'=>$amount,
                    'sum' => round($amount*$price*$nds,2),
                    'price'=>$price,
                    'type'=>$type));
	}
	public function RemoveLine($sort) {
		global $db;
		$this->changed = 1;
		$db->QueryDelete("newbill_lines",array('bill_no'=>$this->bill_no,'sort'=>$sort));
	}
	public static function RemoveBill($bill_no) {
		global $db, $user;

        /*
		if(include_once(INCLUDE_PATH."1c_integration.php")){
			$clS = new \_1c\clientSyncer($db);
			if(!$clS->deleteBill($bill_no,$f)){
				trigger_error("Внимание! Не удалось синхронизировать счет с 1С.");
                return;
			}
		}
        */

		$bill=$db->QuerySelectRow('newbills',array('bill_no'=>$bill_no));
		if(!$bill)
			return 0;
		$db->Query('start transaction');
		//ServicePrototype::CleanOverprice($bill_no);
		$db->QueryDelete('newbills',array('bill_no'=>$bill_no));
		$db->QueryDelete('newbill_lines',array('bill_no'=>$bill_no));
		$db->QueryDelete('newbill_owner',array('bill_no'=>$bill_no));
        $troubleId = $db->GetValue("select id from tt_troubles where bill_no='".$bill_no."'");
        if($troubleId)
    		$db->Query("delete from tt_stages where trouble_id = '.$troubleId.'");

		$db->QueryDelete('tt_troubles',array('bill_no'=>$bill_no));
		$db->QueryInsert("log_newbills",array('bill_no'=>$bill_no,'ts'=>array('NOW()'),'user_id'=>$user->Get('id'),'comment'=>"Удаление"));
		//$db->QueryDelete('log_newbills',array('bill_no'=>$bill_no));
		$db->Query("update log_newbills set bill_no = '".$bill_no.date('dHs')."' where bill_no='".$bill_no."'");
		$db->QueryDelete('newbills_documents',array('bill_no'=>$bill_no));
		$db->Query('commit');
	}
	public function Save($remove_empty = 0,$check_change=1) {
		global $db,$design,$user;
		if ($check_change && !$this->changed && !$remove_empty) return 0;
		$this->changed=0;

		$r = $this->CalculateSum(1,'AB');

		if ($remove_empty && $r['A']==0) {
			Bill::RemoveBill($this->bill_no);
			return 2;
		} else {
			if($this->bill['sum']!=$r['B']) {
				$this->bill['sum']=$r['B'];
				$db->QueryInsert("log_newbills",array(
                    'bill_no'=>$this->bill['bill_no'],
                    'ts'=>array('NOW()'),
                    'user_id'=>(is_object($user) ? $user->Get('id') : AuthUser::getSystemUserId()),
                    'comment'=>($this->_comment?$this->_comment:'Сумма: '.$this->bill['sum'])));
			}
			if(!$this->bill['cleared_flag']){
				$this->bill['cleared_sum'] = $this->bill['sum'];
				$this->bill['sum'] = 0;
			}
			$bSave = $this->bill;
			unset($bSave["doc_ts"]);
			$db->QueryUpdate("newbills","bill_no",$bSave);
			$this->updateBill2Doctypes(null, false);
            /*
			if(include_once(INCLUDE_PATH."1c_integration.php")){
				$clS = new \_1c\clientSyncer($db);
				if(!$clS->pushClientBillService($this->bill_no))
					$db->QueryUpdate("newbills","bill_no",array('bill_no'=>$this->bill['bill_no'],'sync_1c'=>'no'));
				else
					$db->QueryUpdate("newbills","bill_no",array('bill_no'=>$this->bill['bill_no'],'sync_1c'=>'yes'));
			}
            */
			return 1;
		}
	}
	public function GetNo() {
		return $this->bill_no;
	}
	public function GetBill() {
		return $this->bill;
	}

	public function GetManager() {
		global $db;
        $m = $db->GetRow("select owner_id from newbill_owner where bill_no = '".$this->bill_no."'");
        return $m ? $m["owner_id"] : 0;
	}
    public function SetManager($mId) {
        global $db,$user;
        $getedId= $this->GetManager();

        $s = "";
        if($mId == 0){
            $db->Query("delete from `newbill_owner` where  bill_no = '".$this->bill_no."'");
            $s = "Удален менеджер";
        }elseif($getedId == 0){
            $db->Query("insert into `newbill_owner` set owner_id ='".$mId."', bill_no = '".$this->bill_no."'");
            $s = "Установлен менеджер";
        }elseif($getedId != $mId){
            $s = "Изменен менеджер на";
            $db->Query("update `newbill_owner` set owner_id ='".$mId."' where bill_no = '".$this->bill_no."'");
        }else return;

        $db->QueryInsert("log_newbills",array(
                    'bill_no'=>$this->bill_no,
                    'ts'=>array('NOW()'),
                    'user_id'=>$user->Get('id'),
                    'comment'=> $s.($mId ? ": ".getUserName($mId) : "")
                    )
                );

    }

    public function isClosed()
    {
        global $db;

        $t = $db->GetRow(
                "SELECT state_id
                FROM tt_troubles t, tt_stages s
                WHERE bill_no = '".$this->bill_no."' and  t.cur_stage_id = s.stage_id");
        return $t ? $t["state_id"] == 20 : false;
    }

    public function SetUnCleared()
    {
        global $db;

        if($this->bill["cleared_flag"] == 1){
            $db->Query('call switch_bill_cleared("'.addcslashes($this->bill_no, "\\\"").'")');
            if(!defined("NO_WEB"))
			$GLOBALS['module_newaccounts']->update_balance($this->bill['client_id'], $this->bill["currency"]);
        }
    }

    public function SetCleared()
    {
        global $db;

        if($this->bill["cleared_flag"] == 0){
            $db->Query('call switch_bill_cleared("'.addcslashes($this->bill_no, "\\\"").'")');
            if(!defined("NO_WEB"))
			$GLOBALS['module_newaccounts']->update_balance($this->bill['client_id'], $this->bill["currency"]);
        }
    }
    public function GetStaticComment()
    {
        global $db;
        $v = $db->GetRow("select ts as date, user, comment from `log_newbills_static`  l
                left join user_users u on (user_id = u.id)
                where bill_no = '".$this->bill_no."'");
        if($v)
        {
            $v["comment"] = nl2br($v["comment"]);
        }
        return $v;
    }
	public function GetTs() {
		return $this->bill_ts;
	}
    public function GetCourier() {
        return $this->bill_courier;
    }
	public function Set($p,$v) {
		if ($this->bill[$p]!=$v) {
			$this->bill[$p]=$v;
			$this->changed = 1;
		}
	}
	public function Get($p) {
		return isset($this->bill[$p])?$this->bill[$p]:null;
	}
	public function refactLinesWithFourOrderFacure(&$ret){
		global $db;
		$ret_x = array(
			'is_four_order'=>true,

			'0' => '',			'bill_no' => '',
			'1' => 1, 			'sort' => 1,
			'2' => '', 			'item' => '',
			'3' => 1,			'amount' => 1,
			'4' => '',			'price' => '',
			'5' => 'usage_ip_ports',			'service' => 'usage_ip_ports',
			'6' => 2945,			'id_service' => 2945,
			'7' => '',			'date_from' => '',
			'8' => '',			'date_to' => '',
			'9' => 'service',	'type' => 'service',
			'10' => '',			'outprice' => '',
			'11' => '',			'sum' => '',
			'12' => '',			'id' => '',
			'13' => '',			'ts_from' => '',
			'14' => '',			'ts_to' => '',
								'tsum'=>0,
								'tax'=>0,
								'country_id' => 0,
                                'okvd_code' => 0,
                                'okvd' => ""
		);
		$ret_x[2] =& $ret_x['item'];
		$ret_x[0] =& $ret_x['bill_no'];
		$ret_x[4] =& $ret_x['price'];
		$ret_x[10] =& $ret_x['outprice'];
		$ret_x[11] =& $ret_x['sum'];
		$ret_x[7] =& $ret_x['date_from'];
		$ret_x[8] =& $ret_x['date_to'];
		$ret_x[12] =& $ret_x['id'];
		$ret_x[13] =& $ret_x['ts_from'];
		$ret_x[14] =& $ret_x['ts_to'];
		$diff = 0;

		$query = "
			SELECT
				sum(`subq`.`sum`) `sum`,
				`subq`.`type`
			FROM
				(
					SELECT
						IFNULL(`np`.`sum_rub`,`nb`.`sum`) `sum`,
						IF(IFNULL(`np`.`sum_rub`,false),'PAY','BILL') `type`
					FROM
						`newbills` `nb`
					LEFT JOIN
						`newpayments` `np`
					ON
						`np`.`bill_no` = `nb`.`bill_no`
					WHERE
						`nb`.`bill_no` = '".$this->bill_no."'
				) subq
			GROUP BY
				`subq`.`type`
		";

		$db->Query($query);
		$pay = $db->NextRecord(MYSQL_ASSOC);
		if($pay['type'] == 'PAY'){
			$ret_x['tsum'] = $pay['sum'];
			$ret_x['tax'] = $pay['sum']/1.18*0.18;
		}

		foreach($ret as $key=>&$item){
			if($item['outprice']>0 && preg_match('/^\s*Абонентская\s+плата|^\s*Поддержка\s+почтового\s+ящика|^\s*Виртуальная\s+АТС|^\s*Перенос|^\s*Выезд|^\s*Сервисное\s+обслуживание|^\s*Хостинг|^\s*Подключение|^\s*Внутренняя\s+линия|^\s*Абонентское\s+обслуживание|^\s*Услуга\s+доставки|^\s*Виртуальный\s+почтовый|^\s*Размещение\s+сервера|^\s*Настройка[0-9a-zA-Zа-яА-Я]+АТС|^Дополнительный\sIP[\s\-]адрес|^Поддержка\sпервичного\sDNS|^Поддержка\sвторичного\sDNS|^Аванс\sза\sподключение\sинтернет-канала|^Администрирование\sсервер|^Обслуживание\sрабочей\sстанции|^Оптимизация\sсайта|^Неснижаемый\sостаток/',$item['item'])){
				//$item['item']=&$item['2'];
				$item['item'] = str_replace('Абонентская','абонентскую',str_replace('плата','плату',$item['item']));
				$item['item'] = str_replace('Поддержка','поддержку',$item['item']);
				$item['item'] = str_replace('Виртуальная','виртуальную',$item['item']);
				$item['item'] = str_replace('Перенос','перенос',$item['item']);
				$item['item'] = str_replace('Выезд','выезд',$item['item']);
				$item['item'] = str_replace('Сервисное','сервисное',$item['item']);
				$item['item'] = str_replace('Хостинг','хостинг',$item['item']);
				$item['item'] = str_replace('Подключение','подключение',$item['item']);
				$item['item'] = str_replace('Внутренняя линия','внутреннюю линию',$item['item']);
				$item['item'] = str_replace('Услуга','услугу',$item['item']);
				$item['item'] = str_replace('Виртуальный','виртуальный',$item['item']);
				$item['item'] = str_replace('Размещение','размещение',$item['item']);
				$item['item'] = str_replace('Аванс за','',$item['item']);
				$item['item'] = str_replace('Оптимизация','оптимизацию',$item['item']);
				$item['item'] = str_replace('Обслуживание','обслуживание',$item['item']);
				$item['item'] = str_replace('Администрирование','администрирование',$item['item']);

				$item['item'] = 'Авансовый платеж за '.$item['item'];

				$ret_x['item'] .= $item['item'].";<br />";
				$ret_x['bill_no'] = $item['bill_no'];
				$ret_x['sum'] = $item['sum'];
				$ret_x['price'] = $item['price'];
				$ret_x['outprice'] = $item['outprice'];
				$ret_x['date_from'] = $item['date_from'];
				$ret_x['date_to'] = $item['date_to'];
				$ret_x['id'] = $item['id'];
				$ret_x['ts_from'] = $item['ts_from'];
				$ret_x['ts_to'] = $item['ts_to'];

				if($pay['type'] == 'BILL'){
					$ret_x['tsum'] += $item['outprice']*$item['amount']*1.18;
					$ret_x['tax']  += $item['outprice']*$item['amount']*0.18;
				}
			}else{
				if($pay['type'] == 'PAY' && $item['type']<>'zalog'){
					$ret_x['tsum']-=$item['tsum'];
					$ret_x['tax'] -=$item['tax'];
				}
				if($item['type']=='zadatok'){
					$ret_x['tsum']+=$item['tsum'];
					$ret_x['tax'] +=$item['tax'];
				}
				unset($ret[$key]);
			}
		}

		if($ret_x['tsum']<0)
			$this->negative_balance = true;

		$ret = array($ret_x);

	}
	public function refactLinesWithOrder(&$ret){
		foreach($ret as &$item){
			$date = explode("-",$item['date_from']);
			$now = (int)date('Ym');
			$bda = (int)date('Ym',mktime(0, 0, 0, $date[1], $date[2], $date[0]));
			if($now <= $bda && preg_match('/^\s*Абонентская\s+плата|^\s*Поддержка\s+почтового\s+ящика|^\s*Виртуальная\s+АТС/',$item['item'])){
				//$item['item']=&$item['2'];
				$item['item'] = str_replace('Абонентская','абонентскую',str_replace('плата','плату',$item['item']));
				$item['item'] = str_replace('Поддержка','поддержку',$item['item']);
				$item['item'] = str_replace('Виртуальная','виртуальную',$item['item']);
				$item['item'] = 'Авансовый платеж за '.$item['item'];
			}
		}
	}
    public function GetBonus()
    {
        global $db;

        $r = array();
        $q = $db->AllRecords("
                SELECT code_1c as code, round(if(b.type = '%', bl.price*1.18*bl.amount*0.01*`value`, `value`*amount),2) as bonus
                FROM newbill_lines bl
                inner join g_bonus b on b.good_id = bl.item_id
                    and `group` = (select if(usergroup='account_managers', 'manager', usergroup) from newbill_owner nbo, user_users u where nbo.bill_no = bl.bill_no and u.id=nbo.owner_id) where bl.bill_no='".$this->bill_no."'");
        if($q)
            foreach($q as $l)
                $r[$l["code"]] = $l["bonus"];
        return $r;
    }

	public function GetLines($rate = 1,$mode=false){
		global $db;
		$nds = $this->Client("nds_zero") ? "1" : "1.18";
		$fields = '';
		$joins = '';
		if (strpos($this->bill_no, '/') !== false)
		{
			$fields = '  gu.name, ';
			$joins = ' LEFT JOIN g_unit as gu ON g.unit_id = gu.id ';
		}
		$ret =
			$db->AllRecords($q='
				select
					nl.*,
					art,
					round(nl.price*'.$rate.',4) as outprice,
					if(sum is null,round(nl.price*amount*'.$rate.',4),sum/'.$nds.') as sum,
					sort as id,
					UNIX_TIMESTAMP(date_from) as ts_from,
					UNIX_TIMESTAMP(date_to) as ts_to,
					if(g.nds is null, 18, g.nds) as line_nds,
					g.num_id,
					store,
					service, 
					if(nl.service="usage_extra", 
						(select 
							okvd_code 
						from 
							usage_extra u, tarifs_extra t 
						where 
							u.id = nl.id_service and 
							t.id = tarif_id 
						), 
						if (nl.type = "good", 
							(select 
								okei 
							FROM
								g_unit
							WHERE 
								id = g.unit_id
							), "")
					) okvd_code

				from
					newbill_lines nl
				left join 
					g_goods g on (nl.item_id = g.id) 
				LEFT JOIN 
					g_unit as gu ON g.unit_id = gu.id
				where
					bill_no="'.$this->bill_no.'"
				order by
					sort
				','id');


		$countryMaker = array();

        foreach($ret as &$r)
        {
            if($r["sum"] > 0 && $r["amount"] > 0 && 
                    round($r["sum"]/$r["amount"],4) != $r["price"])
            {
                $r["sum"] = round($r["price"]*$r["amount"], 4);
            }
            $r["amount"] = round($r["amount"],6);
            $r["country_name"] = $this->getCountryName($r["country_id"]);

            // если услуга, прописанная через 1с, услуга с датой. Для выписки документов (акт1)
            if($r["service"] == "1C" && $r["type"] == "service")
            {
                $billDate = $this->getShipmentDate();
                if(!$billDate)
                    $billDate = strtotime($this->bill["bill_date"]);

                $r["ts_from"] = strtotime("- ".(date("d", $billDate)-1)." days", $billDate);
                $r["ts_to"] = strtotime("+1 month -1 day", $r["ts_from"]);
            }
        }

		if($mode !== false){
			switch($mode){
				case 4:{//каст для счета 4й фактуры
					$this->refactLinesWithFourOrderFacure($ret);
					break;
				}case 'order':{//каст для счета
					$this->refactLinesWithOrder($ret);
					break;
				}
			}
		}

		return $ret;
	}
	public function __destruct() {
		$this->Save(0,1);
	}
	public function GetMaxSort() {
		return $this->max_sort;
	}
	public function CalculateSum($rate,$type = 'AB') {
		global $db;

        $nds = $this->Client("nds_zero") ? "1" : "1.18";

		$r=$db->GetRow($q=
                'select sum(round('.$nds.'*'.$rate.'*price*amount,2)) as A,
                        sum(round('.$nds.'*'.$rate.'*price*amount*IF(type="zadatok",0,1),2)) as B
                from newbill_lines where bill_no="'.$this->bill_no.'"');

		if (!$r) $r=array('A'=>0,'B'=>0);
		if ($type=='AB') return $r;
		return $r[$type];
	}
	public function CheckForAdmin($doTrigger = true) {
		global $db,$user;
		$A = date('Ym');
		$B = substr($this->bill_no.'000000',0,6);
		if ($B>=$A) return true;
		if (!access('newaccounts_bills','admin')) return false;
		return true;
	}

    public function GetCouriers()
    {
        global $db;

        static $R = array();

        if (empty($R))
        {
            $R[0] = "--- Не установлен ---";
            $db->Query("select id, name from courier where enabled='yes' order by name");
            while($r = $db->NextRecord()) $R[$r["id"]] = $r["name"];
        }
        return $R;
    }

    public function GetCourierName($id)
    {
        $c = Bill::GetCouriers();

        if (!isset($c[$id])) {/*trigger_error("Установленный курьер не найден!");*/ return "";}
        return str_replace("-","", $c[$id]);
    }

    public function getCountryName($id)
    {
    	global $db;

    	static $cach = array("0" => "");

    	if(!isset($cach[$id]))
    	{
    		$cach[$id] = $db->GetValue("select name from country where code = '".$id."'");
    	}

    	return $cach[$id] ? $cach[$id] : "";
    }

    public function SetDocDate($utDate)
    {
        global $db;

        if($utDate)
        {
            $wDate = date("Y-m-d", $utDate);
        }else{
            $wDate = "0000-00-00";
        }

        $db->QueryUpdate("newbills", "bill_no", array(
                    "bill_no" => $this->bill_no,
                    "doc_date" => $wDate));

        $this->addLog($utDate ? "Дата документ установлена: ".mdate("d месяца Y г.", $utDate) : "Дата документа убрана");

        $this->bill["doc_date"] = $wDate;
        $this->bill["doc_ts"] = $utDate;
    }

    public function addLog($comment)
    {
        global $db, $user;

        $db->QueryInsert("log_newbills",array(
                    'bill_no'=>$this->bill_no,
                    'ts'=>array('NOW()'),
                    'user_id'=>$user->Get('id'),
                    'comment'=> $comment
                    )
                );
    }

    public function is1CBill()
    {
        return (bool)preg_match("/20\d{4}\/\d{4}/", $this->bill_no);
    }

    public function getShipmentDate()
    {
        global $db;

        if(!$this->is1CBill() && !$this->isOneTimeService()) return false;

        if($this->bill["doc_ts"]) return $this->bill["doc_ts"];

        return $db->GetValue("
                     SELECT 
                        unix_timestamp(min(cast(date_start as date)))
                     FROM 
                        tt_troubles t , `tt_stages` s  
                     WHERE 
                            t.bill_no = '".$this->bill_no."'
                        and t.id = s.trouble_id 
                        and state_id in (select id from tt_states where state_1c = 'Отгружен')
                     ");
    }

    public function isOneTimeService()
    {
        global $db;

        $ls = $db->AllRecords("select type, service, id_service from newbill_lines where bill_no='".$this->bill_no."'");

        if(count($ls) != 1) return false;

        return $ls[0]["type"] == "service" && $ls[0]["id_service"] == 0 && $ls[0]["service"] == "";
    }

    public function isOneZadatok()
    {
        global $db;

        $ls = $db->AllRecords("select type, service, id_service from newbill_lines where bill_no='".$this->bill_no."'");

        if(count($ls) != 1) return false;

        return $ls[0]["type"] == "zadatok";
    }

    public function getDocumentType($bill_no)
    {
        if(preg_match("/\d{2}-\d{8}/", $bill_no))
        {
            return array("type" => "incomegood");
        }elseif(preg_match("/20\d{4}\/\d{4}/", $bill_no))
        {
            return array("type" => "bill", "bill_type" => "1c");
        }elseif(preg_match("/20\d{4}-\d{4}/", $bill_no)){
            return array("type" => "bill", "bill_type" => "stat");
        }

        return array("type" => "unknown");
    }

    public function getDocument($docId, $clientId = false)
    {
        $docType = self::getDocumentType($docId);

        if($docType["type"] == "bill")
        {
            $doc = NewBill::find($docId);
        }elseif($docType["type"] == "incomegood")
        {
            $doc = GoodsIncomeOrder::getOrder($docId, $clientId);
        }else{
            die("Неизвестный тип документа!");
        }

        if(!$doc)
            throw new Exception("Документ не найден");

        return $doc;
    }
//------------------------------------------------------------------------------------
    public function setBill2Doctypes($data = array())
    {
        global $db;

        $data['bill_no'] = $this->bill_no;
        $data['ts'] = array('NOW()');

        if ($this->checkBill2Doctypes()) 
            $db->QueryUpdate("newbills_documents","bill_no",$data);
        else 
            $db->QueryInsert("newbills_documents",$data);

    }
//------------------------------------------------------------------------------------
    public function updateBill2Doctypes($L = null, $returnData = false)
    {
        global $db;

        if(!$L) 
            $L = $this->GetLines();

        $period_date = get_inv_date_period($this->GetTs());

        $p1 = m_newaccounts::do_print_prepare_filter('invoice',1,$L,$period_date);
        $a1 = m_newaccounts::do_print_prepare_filter('akt',1,$L,$period_date);

        $p2 = m_newaccounts::do_print_prepare_filter('invoice',2,$L,$period_date);
        $a2 = m_newaccounts::do_print_prepare_filter('akt',2,$L,$period_date);

        $p3 = m_newaccounts::do_print_prepare_filter('invoice',3,$L,$period_date,true,true);
        $a3 = m_newaccounts::do_print_prepare_filter('akt',3,$L,$period_date);

        $p4 = m_newaccounts::do_print_prepare_filter('lading',1,$L,$period_date);
        $p5 = m_newaccounts::do_print_prepare_filter('invoice',4,$L,$period_date);

        $p6 = m_newaccounts::do_print_prepare_filter('invoice',5,$L,$period_date);

        $gds = m_newaccounts::do_print_prepare_filter('gds',3,$L,$period_date);

        $bill_akts = array(
            1=>count($a1),
            2=>count($a2),
            3=>count($a3)
        );

        $bill_invoices = array(
            1=>count($p1),
            2=>count($p2),
            3=>count($p3),
            4=>count($p4),
            5=>($p5==-1 || $p5 == 0)?$p5:count($p5),
            6=>count($p6),
            7=>count($gds)
        );

        $bill_invoice_akts = array(
                        1=>count($p1),
                        2=>count($p2)
        );

        $doctypes = array();
        for ($i=1;$i<=3;$i++) $doctypes['a'.$i] = $bill_akts[$i];
        for ($i=1;$i<=7;$i++) $doctypes['i'.$i] = $bill_invoices[$i];
        for ($i=1;$i<=2;$i++) $doctypes['ia'.$i] = $bill_invoice_akts[$i];

        $this->setBill2Doctypes($doctypes);

        return ($returnData) ? $this->getBill2Doctypes() : true;
    }
//------------------------------------------------------------------------------------
    public function getBill2Doctypes()
    {
        global $db;

        $res = $db->GetRow("SELECT * FROM newbills_documents WHERE bill_no='".$this->bill_no."'");
        
        if (!$res) 
            $res = $this->updateBill2Doctypes(null, true);
        
        return $res;
    }
//------------------------------------------------------------------------------------
    public function checkBill2Doctypes()
    {
        global $db;

        return $db->GetValue("SELECT COUNT(*) FROM newbills_documents WHERE bill_no='".$this->bill_no."'");
    }

    public static function getPreBillAmount($client_id)
    {
	global $db;
	if (!$client_id) 
	{
		return 0;
	}
	$client_data = $db->GetRow('
		SELECT * 
		FROM clients 
		WHERE 
			id = ' . $client_id
	);
	if (empty($client_data)) 
	{
		return 0;
	}
	$services = get_all_services($client_data['client'],$client_id);

	$time_from = strtotime('first day of next month 00:00:00');
	$time_to = strtotime('last day of next month 23:59:59');
	$nds = ((!$client_data['nds_zero'])) ? 1.18 : 1;
	$R = 0;
	foreach ($services as $service){
		if((unix_timestamp($service['actual_from']) > $time_to || unix_timestamp($service['actual_to']) < $time_from))
		{
			continue;
		}
		$s=ServiceFactory::Get($service,$client_data);
		
		$s->SetMonth($time_from);
		
		$R+=round($s->getServicePreBillAmount()*$nds, 2);
	}
	
	return $R;
    }
    /**
     *	Предназнеачена для изменения "линий" в счетах, при вызове все линии счета удаляются и заменяются одной обобщенной
     */
    public function changeToOnlyContract()
    {
	$ts = $this->GetTs();
	$min_ts = time();
	$lines = BillLines::find('all', array('select' => '*,UNIX_TIMESTAMP(date_from) as from_ts', 'conditions' => array('bill_no = ?', $this->bill_no), 'order' => 'sort'));
	if (!empty($lines))
	{
		foreach ($lines as $k=>&$v)
		{
			if ($k)
			{
				$first->price += $v->price;
				$first->sum += $v->sum;
				if ($v->service == 'usage_voip' && $v->id_service > 0)
				{
					$first->service = 'usage_voip'; 
					$first->id_service = $v->id_service;
				}
				if ($min_ts > $v->from_ts)
				{
					$min_ts = $v->from_ts;
				}
				unset($v);
			} else {
				$v->item = 'Услуги связи по договору '.BillContract::getString($this->client_id, $ts);
				$v->amount = 1;
				$v->type = 'service';
				$v->service = "";
				$v->id_service = "";
				$first = $v;
			}
		}
		BillLines::delete_all(array('conditions'=>array('bill_no = ? AND sort > ?', $this->bill_no, 1)));
		$date_string = ' за период c ' . date('d', $min_ts) . ' по ' . mdate('t месяца', $min_ts);
		$first->item .= $date_string;
		$first->date_from = date('Y-m-d', $min_ts);
		$first->date_to = date('Y-m-t', $min_ts);
		$first->save();
	}
    }
    /**
     *	Предназнеачена для добавления "линии" переплата
     *	@param int $balance текущий баланс клиента
     *	@param bool $nds_zero флаг, приминять НДС или нет 
     */
    public function applyRefundOverpay($balance, $nds_zero)
    {
	if (!$balance) return;
	$nds = ($nds_zero) ? 1 : 1.18;
	$lines_info = BillLines::first(array('select' => 'MAX(sort) as max_sort, SUM(sum) as sum', 'conditions' => array('bill_no = ?', $this->bill_no)));
	if ($lines_info->sum)
	{
		$balance = min($lines_info->sum, $balance);
		$new_line = new BillLines();
		$new_line->bill_no = $this->bill_no;
		$new_line->sort = $lines_info->max_sort + 1;
		$new_line->item = 'Переплата';
		$new_line->amount = 1;
		$new_line->type = 'zadatok';
		$new_line->price = -$balance/$nds;
		$new_line->sum = -$balance;
		
		$ts = $this->GetTs();
		$new_line->date_from = date('Y-m-d', strtotime('first day of previous month', $ts));
		$new_line->date_to = date('Y-m-d', strtotime('last day of previous month', $ts));
		$new_line->save();
	}
    }
    //------------------------------------------------------------------------------------

    /**
     * Функция подготовки линий для добавления в счет. Складывает однотипные позиций.
     *
     * @param array входйщий спосок позиций счета
     * @return array сгруппированный список
     */
    public function CombineRows($inR)
    {
        $tmpR = [];
        foreach($inR as $rs)
        {
            foreach($rs as $r)
            {
                $key = $r[1]."|".$r[3]."|".$r[7]."|".$r[8]; //складываем только одинаковые услуги в рамках периода
                $tmpR[$key][] = $r;
            }
        }

        $outR = [];
        foreach($tmpR as $item => $rs)
        {
            if (count($rs) > 1)
            {
                $line = null;
                foreach($rs as $r)
                {
                    if (!$line)
                    {
                        $line = $r;
                    } else {
                        $line[2] += $r[2];
                    }
                }
                $outR[] = [$line];
            } else {
                $outR[] = $rs;
            }
        }

        return $outR;
    }
}
?>
