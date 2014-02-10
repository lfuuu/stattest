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
		case 'getVpbxList': return Api::getVpbxList(get_param_raw("client_id")); break;
		case 'getInternetList': return Api::getInternetList(get_param_raw("client_id")); break;
		case 'getCollocationList': return Api::getCollocationList(get_param_raw("client_id")); break;
		case 'getExtraList': return Api::getExtraList(get_param_raw("client_id")); break;
		
		case 'getCollocationTarifs': return Api::getCollocationTarifs(); break;
		case 'getInternetTarifs': return Api::getInternetTarifs(); break;
		case 'getVpbxTarifs': return Api::getVpbxTarifs(); break;
		case 'getDomainTarifs': return Api::getDomainTarifs(); break;
		case 'getVoipTarifs': return Api::getVoipTarifs(); break;
		case 'getRegionList': return Api::getRegionList(); break;
		case 'getFreeNumbers': return Api::getFreeNumbers(); break;
		
		case 'orderInternetTarif': return Api::orderInternetTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("tarif_id")); break;
		case 'orderCollocationTarif': return Api::orderCollocationTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("tarif_id")); break;
		case 'orderVoipTarif': return Api::orderVoipTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("number"), get_param_raw("tarif_id"), get_param_raw("lines_cnt")); break;
		case 'orderVpbxTarif': return Api::orderVpbxTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("tarif_id")); break;
		case 'orderDomainTarif': return Api::orderDomainTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("tarif_id")); break;
		case 'orderEmailTarif': return Api::orderEmailTarif(get_param_raw("client_id"), get_param_raw("domain_id"), get_param_raw("local_part"), get_param_raw("password")); break;

		case 'changeInternetTarif': return Api::changeInternetTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeCollocationTarif': return Api::changeCollocationTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeVoipTarif': return Api::changeVoipTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeVpbxTarif': return Api::changeVpbxTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeDomainTarif': return Api::changeDomainTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeEmailTarif': return Api::changeEmailTarif(get_param_raw("client_id"), get_param_raw("email"), get_param_raw("password")); break;

		case 'disconnectInternet': return Api::disconnectInternet(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectCollocation': return Api::disconnectCollocation(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectVoip': return Api::disconnectVoip(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectVpbx': return Api::disconnectVpbx(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectDomain': return Api::disconnectDomain(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectEmail': return Api::disconnectEmail(get_param_raw("client_id"), get_param_raw("email")); break;

		case 'getStatisticsVoipPhones': return Api::getStatisticsVoipPhones(get_param_raw("client_id")); break;
		case 'getStatisticsVoipData': return Api::getStatisticsVoipData(get_param_raw("client_id"), get_param_raw("phone"), get_param_raw("from"), get_param_raw("to"), get_param_raw("detality"), get_param_raw("destination"), get_param_raw("direction"), get_param_raw("onlypay")); break;
		case 'getStatisticsInternetRoutes': return Api::getStatisticsInternetRoutes(get_param_raw("client_id")); break;
		case 'getStatisticsInternetData': return Api::getStatisticsInternetData(get_param_raw("client_id"), get_param_raw("from"), get_param_raw("to"), get_param_raw("detality"), get_param_raw("route")); break;
		case 'getStatisticsCollocationData': return Api::getStatisticsCollocationData(get_param_raw("client_id"), get_param_raw("from"), get_param_raw("to"), get_param_raw("detality"), get_param_raw("route")); break;
		
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





