<?php 

header("Content-Type: application/json; charset=UTF-8");

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
		case 'getBalance': return Api::getBalance(get_param_raw("client_id"), false); break;
		case 'getBalanceList': return Api::getBalanceList(get_param_raw("client_id")); break;
		case 'getUserBillOnSum': return Api::getUserBillOnSum(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'getBillURL': return Api::getBillURL(get_param_raw("bill_no")); break;
		case 'getReceiptURL': return Api::getReceiptURL(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'getPropertyPaymentOnCard': return Api::getPropertyPaymentOnCard(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'updateUnitellerOrder': return Api::updateUnitellerOrder(get_param_raw("order_id")); break;
		case 'getBill': return Api::getBill(get_param_raw("client_id"), get_param_raw("bill_no")); break;
		case 'getDomainList': return Api::getDomainList(get_param_raw("client_id")); break;
		case 'getEmailList': return Api::getEmailList(get_param_raw("client_id")); break;
		case 'getVoipList': return Api::getVoipList(get_param_raw("client_id")); break;
		case 'getInternetList': return Api::getInternetList(get_param_raw("client_id")); break;
		case 'getCollocationList': return Api::getCollocationList(get_param_raw("client_id")); break;
		case 'getExtraList': return Api::getExtraList(get_param_raw("client_id")); break;
		default: throw new Exception("Функция не определенна");
	}
}

function array_to_json( $array )
{

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
				//$key = "key_$key";
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





