<?php
/**
 * ErrorException codes
 * 1: can't connect to remote database
 * 2: client doesn't exists
 * 3: client is ambiguous
 * 4: mysql statement error
 * 5: transaction error. can't get remote mysql_insert_id
 * 6: fatal transaction error! you should be fix it by your hands!
 *
 * 7: invalid order number
 * 8: invalid client
 */
class all4net_integration{
	private static $db_connection_data = array(
		'host'=>'85.94.33.252',
		'user'=>'u_mcn_shop',
		'passwd'=>'XVMmYAmNg90A',
		'database'=>'mcn_shop'
	);
	private static $db_connection = null;
	private static $db_local = null;
	private $client = array('stat'=>array(),'all4net'=>array());

	public function __construct(){
		global $db;
		self::$db_local =& $db;
		$this->create_connection();
		self::chconn();
	}
	private function create_connection(){
		if(!is_null(self::$db_connection)){
			return true;
		}

		$res = mysql_connect(self::$db_connection_data['host'], self::$db_connection_data['user'], self::$db_connection_data['passwd']);

		if(!$res)
			throw new ErrorException(
				mysql_error(),
				1,
				1,
				__FILE__,
				__LINE__
			);

		$flag = mysql_select_db(self::$db_connection_data['database']);
		if(!$flag)
			throw new ErrorException(
				mysql_error(),
				1,
				1,
				__FILE__,
				__LINE__
			);
		self::$db_connection = $res;
		mysql_query('set names cp1251',$res);
		return true;
	}
	/**
	 * @return boolean успешно ли соединение с сервером
	 */
	public static function chconn(){
		if(!is_null(self::$db_connection) && self::$db_local)
			return true;
		else
			throw new ErrorException(
				"Can't connect to ".self::$db_connection_data['host'],
				1,
				1,
				__FILE__,
				__LINE__
			);
	}

	public function init_client($client){
		self::chconn();
		$db =& self::$db_local;
		if(is_numeric($client))
			$query = "
				select
					cl.*,
					(select `data` from `client_contacts` where `client_id`=`cl`.`id` and `is_active`=1 and `type`='email' limit 1) `email`,
					(select `data` from `client_contacts` where `client_id`=`cl`.`id` and `is_active`=1 and `type`='phone' and data rlike '[\d-]+' limit 1) `phone`,
					(select `data` from `client_contacts` where `client_id`=`cl`.`id` and `is_active`=1 and `type`='fax' and data rlike '[\d-]+' limit 1) `fax`,
					4 as `type_client`
				from
					clients cl
				where
					id = ".$client;
		else{
			$client = addcslashes($client,"\\\\'");
			$query = "
				select
					cl.*,
					(select `data` from `client_contacts` where `client_id`=`cl`.`id` and `is_active`=1 and `type`='email' limit 1) `email`,
					(select `data` from `client_contacts` where `client_id`=`cl`.`id` and `is_active`=1 and `type`='phone' and data rlike '[\d-]+' limit 1) `phone`,
					(select `data` from `client_contacts` where `client_id`=`cl`.`id` and `is_active`=1 and `type`='fax' and data rlike '[\d-]+' limit 1) `fax`,
					4 as `type_client`
				from
					clients cl
				where
					cl.client = '".$client."'
			";
		}

		$client_rows = $db->AllRecords($query,null,MYSQL_ASSOC);
		if(!$client_rows){
			throw new ErrorException(
				"Sorry, but client with id: '".$client."' doesn't exists in stat database...",
				2,
				1,
				__FILE__,
				__LINE__
			);
		}elseif(count($client_rows)>1){
			throw new ErrorException(
				"Sorry.. this client is ambiguous",
				3,
				1,
				__FILE__,
				__LINE__
			);
		}
		$this->client['stat'] =& $client_rows[0];

		if($this->client['stat']['id_all4net']>0){
			$query = "
				select
					*
				from
					users
				where
					id = ".$this->client['stat']['id_all4net'];
			$res = mysql_query($query,self::$db_connection);
			if(mysql_errno()){
				throw new ErrorException(
					"Hey guys.. we have a problem here.. see it: ".mysql_error(),
					4,
					1,
					__FILE__,
					__LINE__
				);
			}
			$client_rows = mysql_fetch_assoc($res);
			if(!count($client_rows))
				$this->client['all4net'] = null;
			else
				$this->client['all4net'] = $client_rows;
		}else{
			$this->client['all4net'] = null;
		}
		return true;
	}
	public function sync_client($client){
		self::chconn();

		if(is_null($this->client['stat'])){
			$this->init_client($client);
		}
		if(is_null($this->client['all4net'])){
			return $this->create_client();
		}else{
			return $this->update_client();
		}
	}
	public function get_client($destination='stat'){
		return $this->client[$destination];
	}
	private function create_client(){
		$query_insert = "
			insert into `users`
				(
					`id_client_stat`,
					`nick`,
					`password`,
					`email`,
					`phone`,
					`fio`,
					`firm`,
					`address`,
					`date_time_reg`,
					`type_client`
				)
			values
				(
					".$this->client['stat']['id'].",
					'STAT',
					'".addcslashes($this->client['stat']['password'],"\\\\'")."',
					'".addcslashes($this->client['stat']['email'],"\\\\'")."',
					'".addcslashes($this->client['stat']['phone'],"\\\\'")."',
					'".addcslashes($this->client['stat']['company'],"\\\\'")."',
					'".addcslashes($this->client['stat']['company_full'],"\\\\'")."',
					'".addcslashes($this->client['stat']['address_jur'],"\\\\'")."',
					NOW(),
					4
				)
		";
		$query_insert_reqs = "
			insert into `clients`
				(
					`client`,
					`password`,
					`password_type`,
					`company`,
					`comment`,
					`address_jur`,
					`status`,
					`usd_rate_percent`,
					`company_full`,
					`address_post`,
					`address_post_real`,
					`type`,
					`manager`,
					`support`,
					`login`,
					`inn`,
					`kpp`,
					`bik`,
					`bank_properties`,
					`signer_name`,
					`signer_position`,
					`signer_nameV`,
					`firma`,
					`currency`,
					`currency_bill`,
					`stamp`,
					`nal`,
					`telemarketing`,
					`sale_channel`,
					`uid`,
					`site_req_no`,
					`signer_positionV`,
					`hid_rtsaldo_date`,
					`hid_rtsaldo_RUB`,
					`hid_rtsaldo_USD`,
					`credit`,
					`user_impersonate`,
					`address_connect`,
					`phone_connect`,
					`id_all4net`,
					`fax`
				)
			values
				(
					'".addcslashes($this->client['stat']['client'],"\\\\'")."',
					'".addcslashes($this->client['stat']['password'],"\\\\'")."',
					'".$this->client['stat']['password_type']."',
					'".addcslashes($this->client['stat']['company'],"\\\\'")."',
					'".addcslashes($this->client['stat']['comment'],"\\\\'")."',
					'".addcslashes($this->client['stat']['address_jur'],"\\\\'")."',
					'".$this->client['stat']['status']."',
					'".$this->client['stat']['usd_rate_percent']."',
					'".addcslashes($this->client['stat']['company_full'], "\\\\'")."',
					'".addcslashes($this->client['stat']['address_post'], "\\\\'")."',
					'".addcslashes($this->client['stat']['address_post_real'],"\\\\'")."',
					'".$this->client['stat']['type']."',
					'".addcslashes($this->client['stat']['manager'],"\\\\'")."',
					'".addcslashes($this->client['stat']['support'],"\\\\'")."',
					'".addcslashes($this->client['stat']['login'],"\\\\'")."',
					'".addcslashes($this->client['stat']['inn'],"\\\\'")."',
					'".addcslashes($this->client['stat']['kpp'],"\\\\'")."',
					'".addcslashes($this->client['stat']['bik'],"\\\\'")."',
					'".addcslashes($this->client['stat']['bank_properties'],"\\\\'")."',
					'".addcslashes($this->client['stat']['signer_name'],"\\\\'")."',
					'".addcslashes($this->client['stat']['signer_position'],"\\\\'")."',
					'".addcslashes($this->client['stat']['signer_nameV'],"\\\\'")."',
					'".addcslashes($this->client['stat']['firma'],"\\\\'")."',
					'".$this->client['stat']['currency']."',
					'".$this->client['stat']['currency_bill']."',
					'".$this->client['stat']['stamp']."',
					'".$this->client['stat']['nal']."',
					'".addcslashes($this->client['stat']['telemarketing'],"\\\\'")."',
					'".addcslashes($this->client['stat']['sale_channel'],"\\\\'")."',
					'".addcslashes($this->client['stat']['uid'],"\\\\'")."',
					'".addcslashes($this->client['stat']['site_req_no'],"\\\\'")."',
					'".addcslashes($this->client['stat']['signer_positionV'],"\\\\'")."',
					'".addcslashes($this->client['stat']['hid_rtsaldo_date'],"\\\\'")."',
					'".addcslashes($this->client['stat']['hid_rtsaldo_RUB'],"\\\\'")."',
					'".addcslashes($this->client['stat']['hid_rtsaldo_USD'],"\\\\'")."',
					'".addcslashes($this->client['stat']['credit'],"\\\\'")."',
					'".addcslashes($this->client['stat']['user_impersonate'],"\\\\'")."',
					'".addcslashes($this->client['stat']['address_connect'],"\\\\'")."',
					'".addcslashes($this->client['stat']['phone_connect'],"\\\\'")."',
					'%d',
					'".addcslashes($this->client['stat']['fax'],"\\\\'")."'
				)
		";
		$query_update = "update clients set id_all4net=%d where id=%d";

		mysql_query(iconv('utf-8','cp1251',$query_insert),self::$db_connection);
		if(mysql_errno()){
			throw new ErrorException(
				"MySQL error: ".mysql_error(),
				4,
				1,
				__FILE__,
				__LINE__
			);
		}
		$id_remote = mysql_insert_id(self::$db_connection);
		if(!$id_remote)
			throw new ErrorException(
				"Transaction error!",
				5,
				1,
				__FILE__,
				__LINE__
			);
		self::$db_local->Query(iconv('utf-8','cp1251',sprintf($query_update,$id_remote,$this->client['stat']['id'])));
		if(mysql_errno()){
			$err = mysql_error();
			mysql_query("delete from users where id=".$id_remote,self::$db_connection);
			if(mysql_errno(self::$db_connection)){
				throw new ErrorException(
					"Fatal transaction error! Client id is: '".$id_remote."'",
					6,
					1,
					__FILE__,
					__LINE__
				);
			}
			throw new ErrorException(
				"MySQL statement error: ".$err,
				4,
				1,
				__FILE__,
				__LINE__
			);
		}
		$this->client['stat']['id_all4net'] = $id_remote;
		mysql_query(iconv('utf-8','cp1251',sprintf($query_insert_reqs,$id_remote)),self::$db_connection);
		if(mysql_errno()){
			throw new ErrorException(
				"MySQL statement error: ".mysql_error(),
				4,
				1,
				__FILE__,
				__LINE__
			);
		}
	}
	private function update_client(){
		$query_update_u = "
			update
				`users`
			set
				`nick` = 'STAT',
				`id_client_stat` = ".$this->client['stat']['id'].",
				`password` = '".addcslashes($this->client['stat']['password'],"\\\\'")."',
				`email` = '".addcslashes($this->client['stat']['email'],"\\\\'")."',
				`phone` = '".addcslashes($this->client['stat']['phone'],"\\\\'")."',
				`fio` = '".addcslashes($this->client['stat']['company'],"\\\\'")."',
				`firm` = '".addcslashes($this->client['stat']['company_full'],"\\\\'")."',
				`address` = '".addcslashes($this->client['stat']['address_jur'],"\\\\'")."'
			where
				`id` = ".$this->client['stat']['id_all4net'];

		$query_update_c = "
			update
				`clients`
			set
				`client` = '".addcslashes($this->client['stat']['client'],"\\\\'")."',
				`password` = '".addcslashes($this->client['stat']['password'],"\\\\'")."',
				`password_type` = '".addcslashes($this->client['stat']['password_type'],"\\\\'")."',
				`company` = '".addcslashes($this->client['stat']['company'],"\\\\'")."',
				`address_jur` = '".addcslashes($this->client['stat']['address_jur'],"\\\\'")."',
				`status` = '".addcslashes($this->client['stat']['status'],"\\\\'")."',
				`usd_rate_percent` = '".addcslashes($this->client['stat']['usd_rate_percent'],"\\\\'")."',
				`company_full` = '".addcslashes($this->client['stat']['company_full'],"\\\\'")."',
				`address_post` = '".addcslashes($this->client['stat']['address_post'],"\\\\'")."',
				`address_post_real` = '".addcslashes($this->client['stat']['address_post_real'],"\\\\'")."',
				`type` = '".addcslashes($this->client['stat']['type'],"\\\\'")."',
				`manager` = '".addcslashes($this->client['stat']['manager'],"\\\\'")."',
				`support` = '".addcslashes($this->client['stat']['support'],"\\\\'")."',
				`login` = '".addcslashes($this->client['stat']['login'],"\\\\'")."',
				`inn` = '".addcslashes($this->client['stat']['inn'],"\\\\'")."',
				`kpp` = '".addcslashes($this->client['stat']['kpp'],"\\\\'")."',
				`bik` = '".addcslashes($this->client['stat']['bik'],"\\\\'")."',
				`bank_properties` = '".addcslashes($this->client['stat']['bank_properties'],"\\\\'")."',
				`signer_name` = '".addcslashes($this->client['stat']['signer_name'],"\\\\'")."',
				`signer_position` = '".addcslashes($this->client['stat']['signer_position'],"\\\\'")."',
				`signer_nameV` = '".addcslashes($this->client['stat']['signer_nameV'],"\\\\'")."',
				`firma` = '".addcslashes($this->client['stat']['firma'],"\\\\'")."',
				`currency` = '".addcslashes($this->client['stat']['currency'],"\\\\'")."',
				`currency_bill` = '".addcslashes($this->client['stat']['currency_bill'],"\\\\'")."',
				`stamp` = '".addcslashes($this->client['stat']['stamp'],"\\\\'")."',
				`nal` = '".addcslashes($this->client['stat']['stamp'],"\\\\'")."',
				`telemarketing` = '".addcslashes($this->client['stat']['telemarketing'],"\\\\'")."',
				`sale_channel` = '".addcslashes($this->client['stat']['sale_channel'],"\\\\'")."',
				`uid` = '".addcslashes($this->client['stat']['uid'],"\\\\'")."',
				`site_req_no` = '".addcslashes($this->client['stat']['site_req_no'],"\\\\'")."',
				`signer_positionV` = '".addcslashes($this->client['stat']['signer_positionV'],"\\\\'")."',
				`hid_rtsaldo_date` = '".addcslashes($this->client['stat']['hid_rtsaldo_date'],"\\\\'")."',
				`hid_rtsaldo_RUB` = '".addcslashes($this->client['stat']['hid_rtsaldo_RUB'],"\\\\'")."',
				`hid_rtsaldo_USD` = '".addcslashes($this->client['stat']['hid_rtsaldo_USD'],"\\\\'")."',
				`user_impersonate` = '".addcslashes($this->client['stat']['user_impersonate'],"\\\\'")."',
				`address_connect` = '".addcslashes($this->client['stat']['address_connect'],"\\\\'")."',
				`phone_connect` = '".addcslashes($this->client['stat']['phone_connect'],"\\\\'")."',
				`fax` = '".addcslashes($this->client['stat']['fax'],"\\\\'")."'
			where
				`id_all4net` = ".$this->client['stat']['id_all4net'];

		$insert_c = "
			insert into
				`clients`
			set
				`client` = '".addcslashes($this->client['stat']['client'],"\\\\'")."',
				`password` = '".addcslashes($this->client['stat']['password'],"\\\\'")."',
				`password_type` = '".addcslashes($this->client['stat']['password_type'],"\\\\'")."',
				`company` = '".addcslashes($this->client['stat']['company'],"\\\\'")."',
				`address_jur` = '".addcslashes($this->client['stat']['address_jur'],"\\\\'")."',
				`status` = '".addcslashes($this->client['stat']['status'],"\\\\'")."',
				`usd_rate_percent` = '".addcslashes($this->client['stat']['usd_rate_percent'],"\\\\'")."',
				`company_full` = '".addcslashes($this->client['stat']['company_full'],"\\\\'")."',
				`address_post` = '".addcslashes($this->client['stat']['address_post'],"\\\\'")."',
				`address_post_real` = '".addcslashes($this->client['stat']['address_post_real'],"\\\\'")."',
				`type` = '".addcslashes($this->client['stat']['type'],"\\\\'")."',
				`manager` = '".addcslashes($this->client['stat']['manager'],"\\\\'")."',
				`support` = '".addcslashes($this->client['stat']['support'],"\\\\'")."',
				`login` = '".addcslashes($this->client['stat']['login'],"\\\\'")."',
				`inn` = '".addcslashes($this->client['stat']['inn'],"\\\\'")."',
				`kpp` = '".addcslashes($this->client['stat']['kpp'],"\\\\'")."',
				`bik` = '".addcslashes($this->client['stat']['bik'],"\\\\'")."',
				`bank_properties` = '".addcslashes($this->client['stat']['bank_properties'],"\\\\'")."',
				`signer_name` = '".addcslashes($this->client['stat']['signer_name'],"\\\\'")."',
				`signer_position` = '".addcslashes($this->client['stat']['signer_position'],"\\\\'")."',
				`signer_nameV` = '".addcslashes($this->client['stat']['signer_nameV'],"\\\\'")."',
				`firma` = '".addcslashes($this->client['stat']['firma'],"\\\\'")."',
				`currency` = '".addcslashes($this->client['stat']['currency'],"\\\\'")."',
				`currency_bill` = '".addcslashes($this->client['stat']['currency_bill'],"\\\\'")."',
				`stamp` = '".addcslashes($this->client['stat']['stamp'],"\\\\'")."',
				`nal` = '".addcslashes($this->client['stat']['stamp'],"\\\\'")."',
				`telemarketing` = '".addcslashes($this->client['stat']['telemarketing'],"\\\\'")."',
				`sale_channel` = '".addcslashes($this->client['stat']['sale_channel'],"\\\\'")."',
				`uid` = '".addcslashes($this->client['stat']['uid'],"\\\\'")."',
				`site_req_no` = '".addcslashes($this->client['stat']['site_req_no'],"\\\\'")."',
				`signer_positionV` = '".addcslashes($this->client['stat']['signer_positionV'],"\\\\'")."',
				`hid_rtsaldo_date` = '".addcslashes($this->client['stat']['hid_rtsaldo_date'],"\\\\'")."',
				`hid_rtsaldo_RUB` = '".addcslashes($this->client['stat']['hid_rtsaldo_RUB'],"\\\\'")."',
				`hid_rtsaldo_USD` = '".addcslashes($this->client['stat']['hid_rtsaldo_USD'],"\\\\'")."',
				`user_impersonate` = '".addcslashes($this->client['stat']['user_impersonate'],"\\\\'")."',
				`address_connect` = '".addcslashes($this->client['stat']['address_connect'],"\\\\'")."',
				`phone_connect` = '".addcslashes($this->client['stat']['phone_connect'],"\\\\'")."',
				`fax` = '".addcslashes($this->client['stat']['fax'],"\\\\'")."',
				`id_all4net` = ".$this->client['stat']['id_all4net'];

		mysql_query(iconv('utf-8','cp1251',$query_update_u),self::$db_connection);
		if(mysql_errno()){
			throw new ErrorException(
				"Can't modify client information at all4net backend. MySQL error: ".mysql_error(),
				4,
				1,
				__FILE__,
				__LINE__
			);
		}
		mysql_query(iconv('utf-8','cp1251',$query_update_c),self::$db_connection);
		if(mysql_errno()){
			throw new ErrorException(
				"Can't modify client information at all4net backend. MySQL error: ".mysql_error(),
				4,
				1,
				__FILE__,
				__LINE__
			);
		}
		if(mysql_affected_rows(self::$db_connection) === 0){
			$query_select_c = "select id from clients where id_all4net=".$this->client['stat']['id_all4net'];
			$res = mysql_query($query_select_c,self::$db_connection);
			if(!mysql_fetch_assoc($res)){
				mysql_query(iconv('utf-8','cp1251',$insert_c),self::$db_connection);
				if(mysql_errno()){
					throw new ErrorException(
						"Can't modify client information at all4net backend. MySQL error: ".mysql_error(),
						4,
						1,
						__FILE__,
						__LINE__
					);
				}
			}
		}
		return true;
	}

	public function sync_bill($order_number){
		self::chconn();
		$order_data = $this->get_order($order_number);
		if(!is_numeric($order_data['id_client_stat']))
			throw new ErrorException(
				"This client don't sync with stat!",
				8,
				1,
				__FILE__,
				__LINE__
			);
		if(!$order_data['items'])
			return false;
		if($order_data['bill_no']<>'')
			return $this->update_bill($order_data);
		else
			return $this->create_bill($order_data);
	}
	private function get_order($order_number){
		global $db;

		if(!is_numeric($order_number))
			throw new ErrorException(
				"Invalid order number: ".$order_number,
				7,
				1,
				__FILE__,
				__LINE__
			);
		/*
		 * o - order
		 * s - set
		 * i - item
		 * c - client
		 */
		$query = '
			select
				`c`.`id_client_stat`,
				`c`.`id` `id_all4net`,
				`o`.`date_begin` `order_date`,
				`o`.`id_zakaz_stat` `bill_no`,
				`o`.`zakaz_status` `order_status`,
				`s`.`count_tovar` `item_count`,
				`s`.`price` `item_total_price`,
				`s`.`kurs` `item_price_rate`,
				`i`.`name` `item_name`,
				IF(`i`.`type_tovar`=0,"good","service") `item_type`
			from
				`zakazy` `o`
			left join
				`sostav_zakaz` `s`
			on
				`s`.`idzakaz` = `o`.`id`
			left join
				`tovar` `i`
			on
				`i`.`id` = `s`.`idtovar`
			left join
				`users` `c`
			on
				`c`.`id` = `o`.`iduser`
			where
				`o`.`id` = '.$order_number;

		$res = mysql_query($query,self::$db_connection);
		$items = array(
			'order_id'=>$order_number,
			'bill_no'=>'',
			'id_client_stat'=>'',
			'id_all4net'=>'',
			'order_date'=>'',
			'order_status'=>'',
			'bill_currency'=>'',
			'current_rate'=>'',
			'items'=>array()
		);

		while(($row = mysql_fetch_assoc($res))!==false){
			$items['id_client_stat'] = $row['id_client_stat'];
			$items['id_all4net'] = $row['id_all4net'];
			$items['order_date'] = $row['order_date'];
			$items['order_status'] = $row['order_status'];
			$items['bill_no'] = $row['bill_no'];
			$items['items'][] = array(
				'item_name'=>$row['item_name'],
				'item_count'=>$row['item_count'],
				'item_price_rate'=>$row['item_price_rate'],
				'item_total_price'=>$row['item_total_price'],
				'item_type'=>$row['item_type']
			);
		}

		$query_sel_cur = "
			select
				IF(`cl`.`currency_bill`='',`cl`.`currency`,`cl`.`currency_bill`) `currency`,
				`bcr`.`rate`
			from
				`clients` `cl`
			left join
				`bill_currency_rate` `bcr`
			on
				`bcr`.`date` = DATE(NOW())
			and
				`bcr`.`currency` = 'USD'
			where
				`cl`.`id` = ".$items['id_client_stat'];

		$cur = $db->GetRow($query_sel_cur);
		$items['bill_currency'] = $cur['currency'];
		$items['current_rate'] = $cur['rate'];
		@file_put_contents(dirname(__FILE__).'/../log/all4net_states.log',print_r($items,true),FILE_APPEND);
		return $items;
	}
	private function create_bill($order_data){
		global $db;

		$bill = new Bill((!$order_data['bill_no'] || !preg_match('/\d{6}-\d{4}-\d+/',$order_data['bill_no']))?null:$order_data['bill_no'],$order_data['id_client_stat'],time(),0,null,true);
		$bill_no = $bill->GetNo();

		$data = array();
		$bill_sum = 0;
		$bill_sum_rub = 0;

		$order_data['bill_currency'] = $bill->Get('currency');

		foreach($order_data['items'] as $item){
			if($item['item_count']<=0)
				continue;
			if($order_data['bill_currency']=='USD'){
				$price = $item['item_total_price']*(($item['item_price_rate']==0) ? 1 : $item['item_price_rate'])/$order_data['current_rate']/1.18;
			}else{
				$price = $item['item_total_price']*(($item['item_price_rate']==0) ? 1 : $item['item_price_rate'])/1.18;
			}
			if($order_data['order_status'] == 50){ // если отказ
				$price = 0;
				$item['item_total_price'] = 0;
			}
			$data[] = array(
				0=>$order_data['bill_currency'],
				1=>iconv('cp1251','utf-8',$item['item_name']),
				2=>$item['item_count'],
				3=> $price,
				4=>$item['item_type'],
				5=>'all4net',
				6=>'-1',
				7=>$order_data['order_date'],
				8=>$order_data['order_date'],
				9=>($order_data['bill_currency']=='USD')?$item['item_total_price']/$order_data['current_rate']:$item['item_total_price']
			);

			if($order_data['order_status'] == 40){ // если выполнен
				$bill_sum_rub += $item['item_total_price']*$item['item_count'];
				$bill_sum += $price*$item['item_count']*1.18;
			}else{
				$bill_sum_rub = 0;
				$bill_sum = 0;
			}
		}

		ob_start();
		$flag = $bill->AddLines($data);
		unset($bill);
		ob_end_clean();
		$query1 = "
			UPDATE
				newbills
			SET
				bill_no = concat(bill_no,'-".$order_data['order_id']."'),
				`sum` = ".$bill_sum."
			WHERE
				bill_no = '".$bill_no."'";

		$query2 = "
			UPDATE
				newbill_lines
			SET
				bill_no = concat(bill_no,'-".$order_data['order_id']."')
			WHERE
				bill_no = '".$bill_no."'";

		if(!$order_data['bill_no'] || !preg_match('/\d{6}-\d{4}-\d+/',$order_data['bill_no'])){
			$db->Query($query1);
			if(mysql_errno()){
				throw new ErrorException(
					"MySQL error:".mysql_error(),
					4,
					1,
					__FILE__,
					__LINE__
				);
			}
			$db->Query($query2);
			if(mysql_errno()){
				throw new ErrorException(
					"MySQL error:".mysql_error(),
					4,
					1,
					__FILE__,
					__LINE__
				);
			}

		}else{
			$db->Query("
				update
					newbills
				set
					sum = ".$bill_sum.$cur_rate."
				where
					bill_no='".$order_data['bill_no']."'
			");
		}
		if(preg_match('^\d+-\d+-\d+$',$bill_no))
			return $bill_no;
		return $bill_no."-".$order_data['order_id'];
	}
	private function update_bill($order_data){
		global $db;
		$delete_bill_lines = "delete from `newbill_lines` where `bill_no` = '".$order_data['bill_no']."'";
		$db->Query($delete_bill_lines);
		return $this->create_bill($order_data);
	}

	public function payment($bill_no){
		if(!preg_match('/^\d+-\d+-\d+$/',$bill_no))
			return;
		$query = "
			select
				concat(
					`nb`.`bill_date`,'\t',
					`nb`.`bill_no`,'\t',
					`nb`.`sum`,'\t',
					`np`.`sum_rub` - `nb`.`sum`,'\t',
					`np`.`sum_rub`,'\t',
					`np`.`payment_date`,' - No',`np`.`payment_no`,' / ',
					case `np`.`type` when 'bank' then 'b' when 'prov' then 'p' when 'neprov' then 'n' end,
					' - ',`np`.`oper_date`,'\t',
					concat(`u`.`user`,'/',`u`.`name`),if(`np`.`bill_no`<>`np`.`bill_vis_no`,concat('\t',`np`.`bill_vis_no`),''),
					'\n'
				) `comm`
			from
				`newbills` `nb`
			inner join
				`newpayments` `np`
			on
				`np`.`bill_no` = `nb`.`bill_no`
			left join
				`user_users` `u`
			on
				`u`.`id` = `np`.`add_user`
			where
				`nb`.`bill_no` = '".addslashes($bill_no)."'
		";

		$r = self::$db_local->GetRow($query);
		$r = iconv('utf-8','cp1251',addcslashes($r['comm'],"\\'"));

		$query_all4net = "
			update
				`zakazy`
			set
				`comment` = if(
					`comment` is null,
					'".$r."',
					concat(`comment`,'".$r."')
				)
			where
				`id_zakaz_stat` = '".addslashes($bill_no)."'
		";

		mysql_query($query_all4net, self::$db_connection);
		return;
	}
}
?>