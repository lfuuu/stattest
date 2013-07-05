<?php
namespace clCards;
	class __state{
		private static $data = array();
		public static function set($k,$v){
			self::$data[$k] = $v;
		}
		public static function get($k){
			return self::$data[$k];
		}
	}

	/**
	 * Синкает реквизиты всех карточкек клиента
	 * @param MySQLDatabase $db приблуда для работы с базой
	 * @param string $cl_tid текстовый идентификатор карточки клиента - clients.client
	 * @param bool $from_main если true, то вычисляется главная карточка клиента, и все синкаются с нее, иначе главная синкается вместе с остальными с переданной
	 */
	function SyncAdditionCards($db,$cl_tid,$from_main=true){
		$cl_main_tid = '';
		if(strrpos($cl_tid, '/')!==false){
			$cl_main_tid = substr($cl_tid,0,-2);
		}else{
			$cl_main_tid = $cl_tid;
		}
		$from_tid = ($from_main)?$cl_main_tid:$cl_tid;

		$up_query = "
			update
				clients cl
			left join
				clients clf
			on
				clf.client = '".addcslashes($from_tid, "\\'")."'
			set
				cl.company = clf.company,
				cl.comment = clf.comment,
				cl.address_jur = clf.address_jur,
				cl.company_full = clf.company_full,
				cl.address_post = clf.address_post,
				cl.address_post_real = clf.address_post_real,
				cl.type = clf.type,
				cl.inn = clf.inn,
				cl.kpp = clf.kpp,
				cl.bik = clf.bik,
				cl.pay_acc = clf.pay_acc,
				cl.corr_acc = clf.corr_acc,
				cl.bank_properties = clf.bank_properties,
				cl.bank_name = clf.bank_name,
				cl.bank_city = clf.bank_city,
				cl.address_connect = clf.address_connect,
				cl.phone_connect = clf.phone_connect,
				cl.previous_reincarnation = clf.previous_reincarnation,
				cl.sync_1c = clf.sync_1c
			where
				(
					cl.client like '".addcslashes($cl_main_tid,"\\'")."/_'
				or
					cl.client like '".addcslashes($cl_main_tid,"\\'")."'
				)
			and
				cl.client <> '".addcslashes($from_tid, "\\'")."'
		";
		$db->Query($up_query);
	}

	/**
	 * Перекидывает текущие услуги с одной карточки на другую
	 * @param MySQLDatabase $db приблуда для работы с базой
	 * @param User $user класс сущности текущего пользователя-менеджера
	 * @param int $from_id идентификатор карточки клиента clients.id
	 * @param string $to текстовый идентификатор карточки клиента clients.client
	 * @return bool результат выполнения - удалось ли завершить транзакцию переноса
	 */
	function moveUsages($db,$user,$from_id,$to){
		$from = (int)$from_id;
		$db->Query('start transaction');
		$err = false;
		$db->Query("update usage_voip set client='".addcslashes($to,"\\'")."' where client = (select client from clients where id=".$from.") and actual_to > now()");
		$err |= (bool)mysql_errno();
		$db->Query("update usage_extra set client='".addcslashes($to,"\\'")."' where client = (select client from clients where id=".$from.") and actual_to > now()");
		$err |= (bool)mysql_errno();
		$db->Query("update usage_ip_ports set client='".addcslashes($to,"\\'")."' where client = (select client from clients where id=".$from.") and actual_to > now()");
		$err |= (bool)mysql_errno();
		$db->Query("update usage_ip_ppp set client='".addcslashes($to,"\\'")."' where client = (select client from clients where id=".$from.") and actual_to > now()");
		$err |= (bool)mysql_errno();
		if($err){
			$db->Query('rollback');
			return false;
		}else{
			$db->Query('commit');
			$db->Query('
				insert into
					log_client (client_id,user_id,ts,comment)
				VALUES (
					'.((int)$_POST['id']).',
					"'.$user->Get('id').'",
					NOW(),
					concat("Получены услуги клиента ",(select client from clients where id='.((int)$_POST['move_usages']).'))
				)
			');
			$db->Query('
				insert into
					log_client (client_id,user_id,ts,comment)
				VALUES (
					'.((int)$_POST['move_usages']).',
					"'.$user->Get('id').'",
					NOW(),
					"Текущие услуги переданы клиенту '.addcslashes($_POST['client'],"\\'").'"
				)
			');
			return true;
		}
	}

	/**
	 * Устанавливает для всех карточек (и пишет логи) предыдущие реквизиты клиента - clients.previous_reincarnation
	 * @param MySQLDatabase $db приблуда для работы с базой
	 * @param User $user класс сущности текущего пользователя-менеджера
	 * @param int $parent_id идентификатор карточки с предыдущими реквизитами - clients.id
	 * @param string $cl_tid текстовый идентификатор карточки клиента для которого устанавливаются предыдущие - clients.client
	 * @return bool результат выполнения
	 */
	function setParent($db,$user,$parent_id,$cl_tid){
		$nr_id = (!$parent_id)?'null':(int)$parent_id;

		$cli = $db->GetRow("select * from clients where client='".addcslashes($cl_tid, "\\'")."'");
		$db->Query("update clients set previous_reincarnation=".$nr_id." where client='".addcslashes($cl_tid,"\\'")."'");
		if($nr_id<>'null'){
			$db->Query('
				insert into
					log_client (client_id,user_id,ts,comment)
				VALUES (
					'.$nr_id.',
					"'.$user->Get('id').'",
					NOW(),
					"Установлен как предыдущий для '.addcslashes($cl_tid,"\\'").'"
				)
			');
			$db->Query('
				insert into
					log_client (client_id,user_id,ts,comment)
				VALUES (
					'.$cli['id'].',
					"'.$user->Get('id').'",
					NOW(),
					concat("Установлен предыдущий клиент ",(select client from clients where id='.$nr_id.'))
				)
			');
		}else{
			if($cli['previous_reincarnation'])
				$db->Query('
					insert into
						log_client (client_id,user_id,ts,comment)
					VALUES (
						'.$cli['id'].',
						"'.$user->Get('id').'",
						NOW(),
						concat("Сброшен предыдущий клиент ",(select client from clients where id='.$cli['previous_reincarnation'].'))
					)
				');
		}
		SyncAdditionCards($db, $cl_tid,false);
		return true;
	}

	/**
	 * сущность реквизитов клиента
	 */
	class struct_cardDetails{
		const client = 1;
		const card = 2;
		const cli_1c = 4;
		const con_1c = 8;
		const company = 16;
		const company_full = 32;
		const inn = 64;
		const bik = 128;
		const pay_acc = 256;
		const corr_acc = 512;
		const bank_name = 1024;
		const bank_city = 2048;
		const address_jur = 4096;
		const kpp = 8192;
		const firma = 16384;
		const type = 32768;
		const currency = 65536;
		const price_type = 131072;

		public $client;
		public $card_id;
		public $cli_1c;
		public $con_1c;
		public $company;
		public $company_full;
		public $inn;
		public $bik;
		public $pay_acc;
		public $corr_acc;
		public $bank_name;
		public $bank_city;
		public $address_jur;
		public $kpp;
		public $firma;
		public $type;
		public $currency;
		public $price_type;

		private $mask = 0;

		public function getAttrsMask(){
			return array(
				'client'=>self::client,
				'card_id'=>self::card,
				'cli_1c'=>self::cli_1c,
				'con_1c'=>self::con_1c,
				'company'=>self::company,
				'company_full'=>self::company_full,
				'inn'=>self::inn,
				'bik'=>self::bik,
				'pay_acc'=>self::pay_acc,
				'corr_acc'=>self::corr_acc,
				'bank_name'=>self::bank_name,
				'bank_city'=>self::bank_city,
				'address_jur'=>self::address_jur,
				'kpp'=>self::kpp,
				'firma'=>self::firma,
				'type'=>self::type,
				'currency'=>self::currency,
				'price_type'=>self::price_type
			);
		}

		public function haveCardId(){
			return $this->mask & self::card;
		}
		public function haveMask($m){
			return $this->mask & $m;
		}

		public function setAtMask($mask,$val){
			if($mask & self::client)
				$this->setCard($val);
			elseif($mask & self::card)
				$this->setCard($val);
			elseif($mask & self::cli_1c)
				$this->setCli1c($val);
			elseif($mask & self::con_1c)
				$this->setCon1c($val);
			elseif($mask & self::company)
				$this->setCompany($val);
			elseif($mask & self::company_full)
				$this->setCompanyFull($val);
			elseif($mask & self::inn)
				$this->setInn($val);
			elseif($mask & self::bik)
				$this->setBik($val);
			elseif($mask & self::pay_acc)
				$this->setPayAcc($val);
			elseif($mask & self::corr_acc)
				$this->setCorrAcc($val);
			elseif($mask & self::bank_name)
				$this->setBankName($val);
			elseif($mask & self::bank_city)
				$this->setBankCity($val);
			elseif($mask & self::address_jur)
				$this->setAddressJur($val);
			elseif($mask & self::kpp)
				$this->setKpp($val);
			elseif($mask & self::type)
				$this->setType($val);
			elseif($mask & self::currency)
				$this->setCurrency($val);
			elseif($mask & self::price_type)
				$this->setPriceType($val);
			return $this;
		}
		public function getAtMask($mask){
			$attrs = $this->getAttrsMask();
			foreach($attrs as $a=>$m){
				if($m & $mask)
					return $this->{$a};
			}
		}

		public function setCard($card_tid){
			$cl_main_card = '';
			if(($pos = strrpos($card_tid, '/'))===false)
				$cl_main_card = $card_tid;
			else
				$cl_main_card = substr($card_tid,0,-2);
			$this->client = $cl_main_card;
			$this->card_id = $card_tid;

			if($this->client && $this->card_id)
				$this->mask |= self::client | self::card;

			return $this;
		}
		public function setCli1c($uid_1c){
			$this->cli_1c = $uid_1c;
			if($this->cli_1c)
				$this->mask |= self::cli_1c;
			return $this;
		}
		public function setCon1c($uid_1c){
			$this->con_1c = $uid_1c;
			if($this->con_1c)
				$this->mask |= self::con_1c;
			return $this;
		}
		public function setCompany($company){
			$this->company = $company;
			if($this->company)
				$this->mask |= self::company;
			return $this;
		}
		public function setCompanyFull($company_full){
			$this->company_full = $company_full;
			if($this->company_full)
				$this->mask |= self::company_full;
			return $this;
		}
		public function setInn($inn){
			$this->inn = $inn;
			if($this->inn)
				$this->mask |= self::inn;
			return $this;
		}
		public function setBik($bik){
			$this->bik = $bik;
			if($this->bik)
				$this->mask |= self::bik;
			return $this;
		}
		public function setPayAcc($acc){
			$this->pay_acc = $acc;
			if($this->pay_acc)
				$this->mask |= self::pay_acc;
			return $this;
		}
		public function setCorrAcc($acc){
			$this->corr_acc = $acc;
			if($this->corr_acc)
				$this->mask |= self::corr_acc;
			return $this;
		}
		public function setBankName($name){
			$this->bank_name = $name;
			if($this->bank_name)
				$this->mask |= self::bank_name;
			return $this;
		}
		public function setBankCity($city){
			$this->bank_city = $city;
			if($this->bank_city)
				$this->mask |= self::bank_city;
			return $this;
		}
		public function setAddressJur($address){
			$this->address_jur = $address;
			if($this->address_jur)
				$this->mask |= self::address_jur;
			return $this;
		}
		public function setKpp($kpp){
			$this->kpp = $kpp;
			if($this->kpp)
				$this->mask |= self::kpp;
			return $this;
		}
		public function setFirma($f){
			$this->firma = $f;
			if($this->firma)
				$this->mask |= self::firma;
			return $this;
		}
		public function setType($t){
			$this->type = $t;
			if($this->type)
				$this->mask |= self::type;
			return $this;
		}
		public function setCurrency($c){
			$this->currency = $c;
			if($this->currency)
				$this->mask |= self::currency;
			return $this;
		}

		public function setPriceType($c){
			$this->price_type = $c;
			if($this->price_type !== false)
				$this->mask |= self::price_type;
			return $this;
		}

		public function enableMask($bitwise){
			$this->mask |= $bitwise;
		}
		public function disableMask($bitwise){
			$this->mask &= ~$bitwise;
		}
		public function eq(struct_cardDetails $cd, $strict=false){

			if($strict && $this->mask != $cd->mask){
				return false;
            }else{
				$attrs = $this->getAttrsMask();

				foreach($attrs as $at=>$m){
					if(!($this->mask & $m) || !($cd->mask & $m))
						continue;
					if($this->{$at} !== $cd->{$at})
						return false;
				}
			}

			return true;
		}
		public function hasAnotherFields(struct_cardDetails $cd){
			return ~$this->mask & $cd->mask;
		}
		public function merge(struct_cardDetails $cd){
			$n=$this->hasAnotherFields($cd);
			if($n){
				$attrs = $this->getAttrsMask();

				foreach($attrs as $a=>$m){
					if($m & $n)
						$this->setAtMask($m, $cd->{$a});
				}
				return true;
			}
			return false;
		}
		public function set(struct_cardDetails $cd){
			$n=$this->getIntersectMask($cd);
			if($n){
				$attrs = $this->getAttrsMask();

				foreach($attrs as $a=>$m){
					if($m & $n)
						$this->setAtMask($m, $cd->{$a});
				}
				return true;
			}
			return false;
		}
		public function getIntersectMask(struct_cardDetails $cd){
			return $this->mask & $cd->mask;
		}
		public function getDiffArr(struct_cardDetails $cd,$escape=null){
			$diff = array();
			//$m_intersect = $this->mask | $cd->mask;
			$m_intersect = $cd->mask;

			$attrs = $this->getAttrsMask();
			foreach($attrs as $a=>$m){
				if(!($m & $m_intersect))
					continue;
				if($this->{$a} !== $cd->{$a}){
					if($escape)
						$diff[$a] = addcslashes($cd->{$a}, $escape);
					else
						$diff[$a] = $cd->{$a};
				}
			}
			return $diff;
		}

		public function getDetailsArr($escape=null){
			$attrs = $this->getAttrsMask();
			$ret = array();
			foreach($attrs as $a=>$m){
				$ret[$a] = ($escape)?addcslashes($this->{$a}, $escape):$this->{$a};
			}
			return $ret;
		}
	}

	/**
	 * возвращает инициализированную сущность клиента
	 * @param MySQLDatabase $db приблуда для работы с базой
	 * @param string $client_tid текстовый идентификатор клиента - clients.client
	 * @return struct_cardDetails структура клиентских реквизитов
	 */
	function getCard($db,$client_tid){
		if(!$client_tid)
			return false;
		$cl = $db->GetRow("select * from clients where client='".$client_tid."'");
		if(!$cl)
			return false;
		$d = new struct_cardDetails();
		$d->setAddressJur($cl['address_jur']);
		$d->setBankCity($cl['bank_name']);
		$d->setBankName($cl['bank_city']);
		$d->setBik($cl['bik']);
		$d->setCard($cl['client']);
		$d->setCli1c($cl['cli_1c']);
		$d->setCompany($cl['company']);
		$d->setCompanyFull($cl['company_full']);
		$d->setCon1c($cl['con_1c']);
		$d->setCorrAcc($cl['corr_acc']);
		$d->setInn($cl['inn']);
		$d->setPayAcc($cl['pay_acc']);
		$d->setKpp($cl['kpp']);
		$d->setFirma($cl['firma']);
		$d->setType($cl['type']);
		$d->setCurrency($cl['currency']);
		$d->setPriceType($cl['price_type']);
		return $d;
	}

	/**
	 * возвращает инициализированную сущность клиента
	 * @param MySQLDatabase $db приблуда для работы с базой
	 * @param string $cli_1c текстовый идентификатор договора 1с - clients.cli_1c
	 * @return struct_cardDetails структура клиентских реквизитов
	 */
	function getCli1c($db,$cli_1c){
		$cl = $db->GetRow("select * from clients where cli_1c='".addcslashes($cli_1c,"\\'")."' order by client limit 1");
		if(!$cl)
			return false;
		$d = new struct_cardDetails();
		$d->setAddressJur($cl['address_jur']);
		$d->setBankCity($cl['bank_name']);
		$d->setBankName($cl['bank_city']);
		$d->setBik($cl['bik']);
		$d->setCard($cl['client']);
		$d->setCli1c($cl['cli_1c']);
		$d->setCompany($cl['company']);
		$d->setCompanyFull($cl['company_full']);
		$d->setCon1c($cl['con_1c']);
		$d->setCorrAcc($cl['corr_acc']);
		$d->setInn($cl['inn']);
		$d->setPayAcc($cl['pay_acc']);
		$d->setKpp($cl['kpp']);
		$d->setFirma($cl['firma']);
		$d->setType($cl['type']);
		$d->setCurrency($cl['currency']);
		$d->setPriceType($cl['price_type']);
		return $d;
	}

	/**
	 * возвращает инициализированную сущность клиента
	 * @param MySQLDatabase $db приблуда для работы с базой
	 * @param string $con_1c текстовый идентификатор договора 1с - clients.con_1c
	 * @return struct_cardDetails структура клиентских реквизитов
	 */
	function getCard1c($db,$con_1c){
		$cl = $db->GetRow("select * from clients where con_1c='".addcslashes($con_1c,"\\'")."'");
		if(!$cl)
			return false;
		$d = new struct_cardDetails();
		$d->setAddressJur($cl['address_jur']);
		$d->setBankCity($cl['bank_name']);
		$d->setBankName($cl['bank_city']);
		$d->setBik($cl['bik']);
		$d->setCard($cl['client']);
		$d->setCli1c($cl['cli_1c']);
		$d->setCompany($cl['company']);
		$d->setCompanyFull($cl['company_full']);
		$d->setCon1c($cl['con_1c']);
		$d->setCorrAcc($cl['corr_acc']);
		$d->setInn($cl['inn']);
		$d->setPayAcc($cl['pay_acc']);
		$d->setKpp($cl['kpp']);
		$d->setFirma($cl['firma']);
		$d->setType($cl['type']);
		$d->setCurrency($cl['currency']);
		$d->setPriceType($cl['price_type']);
		return $d;
	}

	/**
	 * сохраняет изменения карточки клиента, или заводит новую
	 * @param MySQLDatabase $db приблуда для работы с базой
	 * @param struct_cardDetails $cli структура клиента
	 * @return bool результат выполнения. если поля не изменились - false
	 */
	function saveCard($db, struct_cardDetails $cli){
        /*
		if(!$cli->haveCardId())
			return false;
            */

		$curcard = getCard($db, $cli->getAtMask(struct_cardDetails::client));

		if(!$curcard){ // create new
			$r = $cli->getDetailsArr("\\'");
			$query = "insert into clients set ";
			foreach($r as $f=>$v)
                if(!in_array($f, array("card_id", "con_1c", "cli_1c")))
                    $query .= $f."='".$v."',";
			$query = substr($query, 0, -1);

            //file_put_contents("/tmp/sql".date("Y-m-d_H:i:s").rand(1,1000), $query);
			$db->Query($query);

            $cId = $db->GetInsertId();
            $db->Query("update clients set client = 'id".$cId."' where id = '".$cId."'");

			return array(true, $cId);
		}elseif($cli->eq($curcard, true)){
			return true;
		}else{

			$diff = $curcard->getDiffArr($cli,"\\'");

			if(!count($diff))
				return true;
			$query = "update clients set ";
			foreach($diff as $f=>$v)
				$query .= $f."='".$v."',";
			$query = substr($query, 0, -1);
			$query .= " where client='".addcslashes($cli->getAtMask(struct_cardDetails::client), "\\'")."'";

            //file_put_contents("/tmp/sql".date("Y-m-d_H:i:s").rand(1,1000), $query);

			$db->Query($query);
			SyncAdditionCards($db, $cli->getAtMask(struct_cardDetails::client), false);
			return true;
		}
	}

	/**
	 * ищет расчетный счет в поле clients.bank_properties, отбраковывая значение clients.corr_acc
	 * @param MySQLDatabase $db
	 * @param string $client текстовый идентификатор клиента - clients.client
	 * @param bool $update флаг обновления. Если true - обновляет значение базы clients.pay_acc найденным
	 * @return mixed false если не удалось найти, либо string со значением счета
	 */
	function findPayAcc($db, $client,$update=false){
		$c = $db->GetRow("select bank_properties,corr_acc from clients where client='".addcslashes($client, "\\'")."'");
		preg_match_all('/\d{20}/', $c['bank_properties'], $m);
		$pay_acc = false;
		if(count($m[0])>2)
			return false;
		for($i=0;$i<2;$i++)
			if($m[0][$i] != $c['corr_acc']){
				$pay_acc = $m[0][$i];
				break;
			}
		if(!$update)
			return $pay_acc;
		elseif($pay_acc){
			$db->Query("update clients set pay_acc='".$pay_acc."' where client='".addcslashes($client, "\\'")."'");
			return $pay_acc;
		}else
			return false;
	}

	function setSync1c($db, $client_tid, $val){
		$val = ($val)?'yes':'no';
		$db->Query("update clients set sync_1c='".$val."' where client='".addcslashes($client_tid, "\\'")."'");
		SyncAdditionCards($db, $client_tid, false);
	}

	function getClientByBillNo($db, $bill_no){
		$cl = $db->GetRow("select cl.* from clients cl inner join newbills nb on nb.bill_no='".addcslashes($bill_no, "\\'")."' and cl.id=nb.client_id");
		if(!$cl)
			return false;
		$d = new struct_cardDetails();
		$d->setAddressJur($cl['address_jur']);
		$d->setBankCity($cl['bank_name']);
		$d->setBankName($cl['bank_city']);
		$d->setBik($cl['bik']);
		$d->setCard($cl['client']);
		$d->setCli1c($cl['cli_1c']);
		$d->setCompany($cl['company']);
		$d->setCompanyFull($cl['company_full']);
		$d->setCon1c($cl['con_1c']);
		$d->setCorrAcc($cl['corr_acc']);
		$d->setInn($cl['inn']);
		$d->setPayAcc($cl['pay_acc']);
		$d->setKpp($cl['kpp']);
		$d->setFirma($cl['firma']);
		$d->setType($cl['type']);
		$d->setCurrency($cl['currency']);
		$d->setPriceType($cl['price_type']);
		return $d;
	}
?>
