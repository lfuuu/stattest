<?php
	class MyDBG{
		public static function check(){
			return @$_COOKIE['dns_dbg_value'];
		}
		public static function fout($val,$exit=false){
			self::out($val,$exit,false);
		}
		public static function out($val,$exit=false,$check=true){
			if($check && !self::check())
				return null;
			echo "<pre>";
			if(is_bool($val)){
				var_export($val);
			}else
				print_r($val);
			echo "</pre>";
			if($exit)
				exit();
		}

		public static function fcout($val,$exit=false){
			self::cout($val,$exit,false);
		}
		public static function cout($val,$exit=false,$check=true){
			if($check && !self::check())
				return null;
			echo "\n";
			if(is_bool($val)){
				var_export($val);
			}else
				print_r($val);
			echo "\n";
			if($exit)
				exit();
		}
		public static function eva($php_code,$check=true){
			if($check && !self::check())
				return null;
			eval($php_code);
		}
	}
?>