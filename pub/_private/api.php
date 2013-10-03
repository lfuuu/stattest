<?php 

header("Content-Type: text/html; charset=UTF-8");

define("PATH_TO_ROOT", "../../");

include PATH_TO_ROOT."conf.php";


$function = get_param_raw("function");

try{
    $answer = do_func($function);
}catch(Exception $e)
{
    say("error", $e->getMessage());
	exit();
}

say("ok", $answer);

function say($status, $data)
{
		$say = array("status" => $status);

		if($status == "ok")
		{
				$say["return"] = $data;
		}else{
				$say["error"] = $data;
		}
		//echo json_encode($say);
		echo array_to_json($say);
		exit();
}


		

function do_func($function)
{
		switch($function)
		{
				case 'getBalance': return Func::getBalance(get_param_raw("client_id")); break;
				default: throw new Exception("Функция не определенна");
		}
}


class Func
{
		public function getBalance($clientId)
		{
				if(!$clientId || !preg_match("/^\d+$/", $clientId))
						throw new Exception("Неверный номер лицевого счета!");

				$c = ClientCard::find_by_id($clientId);

				if(!$c)
				{
						throw new Exception("Лицевой счет не найден!");
				}

				return array("balance" => $c->balance);
		}
}

function array_to_json( $array ){

		if( !is_array( $array ) ){
				return false;
		}

		$associative = count( array_diff( array_keys($array), array_keys( array_keys( $array )) ));
		if( $associative ){

				$construct = array();
				foreach( $array as $key => $value ){

						// We first copy each key/value pair into a staging array,
						// formatting each key and value properly as we go.

						// Format the key:
						if( is_numeric($key) ){
								$key = "key_$key";
						}
						$key = "\"".addslashes($key)."\"";

						// Format the value:
						if( is_array( $value )){
								$value = array_to_json( $value );
						} else if( !is_numeric( $value ) || is_string( $value ) ){
								$value = "\"".addslashes($value)."\"";
						}

						// Add to staging array:
						$construct[] = "$key: $value";
				}

				// Then we collapse the staging array into the JSON form:
				$result = "{ " . implode( ", ", $construct ) . " }";

		} else { // If the array is a vector (not associative):

				$construct = array();
				foreach( $array as $value ){

						// Format the value:
						if( is_array( $value )){
								$value = array_to_json( $value );
						} else if( !is_numeric( $value ) || is_string( $value ) ){
								$value = "\"".addslashes($value)."\"";
						}

						// Add to staging array:
						$construct[] = $value;
				}

				// Then we collapse the staging array into the JSON form:
				$result = "[ " . implode( ", ", $construct ) . " ]";
		}

		return $result;
}







