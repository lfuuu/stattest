<?php
namespace welltime;

	class __state{
		private static $data = array(
			'DBG'=>false,
			'wsdl'=>'http://thiamis.mcn.ru/welltime/wsdl/manage_trunks.wsdl',
			#'wsdl'=>'http://10.210.12.253:90/welltime/wsdl/manage_trunks.wsdl',
			'login'=>'',
			'password'=>''
		);
		public static function set($k,$v){
			self::$data[$k] = $v;
		}
		public static function get($k){
			return self::$data[$k];
		}
	}

	function tr($var){
		return $var;
	}
	function trr($var){
		return $var;
	}

	class MBox_syncer{
		private $soap;
		private $db;

		public function __construct($db){
			$this->db = $db;
			$wsdl = __state::get('wsdl');
			$login = __state::get('login');
			$pass = __state::get('password');
			$params = array('encoding'=>'UTF-8','trace'=>1);
			if($login && $pass){
				$params['login'] = $login;
				$params['password'] = $pass;
			}
			$this->soap = new \SoapClient($wsdl,$params);
		}

		public function UpdateMailBox($mbox,&$fault=null){
			try{
				$ret = $this->soap->UpdateMailBox(array(
					'box_name'=>$mbox['local_part'].'@'.$mbox['domain'],
					'user_name'=>$mbox['local_part'],
					'user_pass'=>$mbox['password'],
					'pop3_host'=>"mail.mcn.ru",
					'clear_msg'=>false
				));
			}catch(\SoapFault $e){
				$fault = $e;
				return false;
			}

			return $ret;
		}

		public function DeleteMailBox($mbox,&$fault=null){
			try{
				$ret = $this->soap->DeleteMailBox($mbox);
			}catch(\SoapFault $e){
				$fault = $e;
				return false;
			}

			return $ret;
		}
	}
?>
