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
		case 'getBalanceList': return ApiLk::getBalanceList(get_param_raw("client_id")); break;
		case 'getUserBillOnSum': return ApiLk::getUserBillOnSum(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'getBillURL': return ApiLk::getBillURL(get_param_raw("bill_no")); break;
		case 'getReceiptURL': return ApiLk::getReceiptURL(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'getPropertyPaymentOnCard': return ApiLk::getPropertyPaymentOnCard(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'updateUnitellerOrder': return ApiLk::updateUnitellerOrder(get_param_raw("order_id")); break;
		case 'getBill': return ApiLk::getBill(get_param_raw("client_id"), get_param_raw("bill_no")); break;
		case 'getDomainList': return ApiLk::getDomainList(get_param_raw("client_id")); break;
		case 'getEmailList': return ApiLk::getEmailList(get_param_raw("client_id")); break;
		case 'getVoipList': return ApiLk::getVoipList(get_param_raw("client_id")); break;
		case 'getVpbxList': return ApiLk::getVpbxList(get_param_raw("client_id")); break;
		case 'getInternetList': return ApiLk::getInternetList(get_param_raw("client_id")); break;
		case 'getCollocationList': return ApiLk::getCollocationList(get_param_raw("client_id")); break;
		case 'getExtraList': return ApiLk::getExtraList(get_param_raw("client_id")); break;
		
		case 'getCollocationTarifs': return ApiLk::getCollocationTarifs(); break;
		case 'getInternetTarifs': return ApiLk::getInternetTarifs(); break;
		case 'getVpbxTarifs': return ApiLk::getVpbxTarifs(); break;
		case 'getDomainTarifs': return ApiLk::getDomainTarifs(); break;
		case 'getVoipTarifs': return ApiLk::getVoipTarifs(); break;
		case 'getRegionList': return ApiLk::getRegionList(); break;
		case 'getFreeNumbers': return ApiLk::getFreeNumbers(get_param_raw("region_id")); break;
		
		case 'orderInternetTarif': return ApiLk::orderInternetTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("tarif_id")); break;
		case 'orderCollocationTarif': return ApiLk::orderCollocationTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("tarif_id")); break;
		case 'orderVoip': return ApiLk::orderVoip(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("number"), get_param_raw("tarif_id"), get_param_raw("lines_cnt")); break;
		case 'orderVpbxTarif': return ApiLk::orderVpbxTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("tarif_id")); break;
		case 'orderDomainTarif': return ApiLk::orderDomainTarif(get_param_raw("client_id"), get_param_raw("region_id"), get_param_raw("tarif_id")); break;
		case 'orderEmail': return ApiLk::orderEmail(get_param_raw("client_id"), get_param_raw("domain_id"), get_param_raw("local_part"), get_param_raw("password")); break;

		case 'changeInternetTarif': return ApiLk::changeInternetTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeCollocationTarif': return ApiLk::changeCollocationTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeVoipTarif': return ApiLk::changeVoipTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeVpbxTarif': return ApiLk::changeVpbxTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeDomainTarif': return ApiLk::changeDomainTarif(get_param_raw("client_id"), get_param_raw("service_id"), get_param_raw("tarif_id")); break;
		case 'changeEmail': return ApiLk::changeEmail(get_param_raw("client_id"), get_param_raw("email_id"), get_param_raw("password")); break;
		case 'changeEmailSpamAct': return ApiLk::changeEmailSpamAct(get_param_raw("client_id"), get_param_raw("email_id"), get_param_raw("spam_act")); break;
		case 'getEmailAccess': return ApiLk::getEmailAccess(get_param_raw("client_id")); break;
		
		case 'disconnectInternet': return ApiLk::disconnectInternet(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectCollocation': return ApiLk::disconnectCollocation(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectVoip': return ApiLk::disconnectVoip(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectVpbx': return ApiLk::disconnectVpbx(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectDomain': return ApiLk::disconnectDomain(get_param_raw("client_id"), get_param_raw("service_id")); break;
		case 'disconnectEmail': return ApiLk::disconnectEmail(get_param_raw("client_id"), get_param_raw("email_id"), get_param_raw("action")); break;

		case 'getStatisticsVoipPhones': return ApiLk::getStatisticsVoipPhones(get_param_raw("client_id")); break;
		case 'getStatisticsVoipData': return ApiLk::getStatisticsVoipData(get_param_raw("client_id"), get_param_raw("phone"), get_param_raw("from"), get_param_raw("to"), get_param_raw("detality"), get_param_raw("destination"), get_param_raw("direction"), get_param_raw("onlypay")); break;
		case 'getStatisticsInternetRoutes': return ApiLk::getStatisticsInternetRoutes(get_param_raw("client_id")); break;
		case 'getStatisticsInternetData': return ApiLk::getStatisticsInternetData(get_param_raw("client_id"), get_param_raw("from"), get_param_raw("to"), get_param_raw("detality"), get_param_raw("route")); break;
		case 'getStatisticsCollocationData': return ApiLk::getStatisticsCollocationData(get_param_raw("client_id"), get_param_raw("from"), get_param_raw("to"), get_param_raw("detality"), get_param_raw("route")); break;

        //vpbx
        case 'getClientPhoneNumbers': return ApiVpbx::getClientPhoneNumbers(get_param_raw("client_id")); break;
        case 'setClientVatsPhoneNumbers': return ApiVpbx::setClientVatsPhoneNumbers(get_param_raw("client_id"), get_param_raw("phones")); break;
        case 'vpbx_addDid': return ApiVpbx::addDid(get_param_raw("client_id"), get_param_raw("phone")); break;
        case 'vpbx_delDid': return ApiVpbx::delDid(get_param_raw("client_id"), get_param_raw("phone")); break;

        case 'getClientPhoneNumbers': return Api::getClientPhoneNumbers(get_param_raw("client_id")); break;
        case 'setClientVatsPhoneNumbers': return Api::setClientVatsPhoneNumbers(get_param_raw("client_id"), get_param_raw("phones")); break;

        case 'getServiceOptions': return ApiLk::getServiceOptions(get_param_protected("service"), get_param_integer("client_id")); break;

		case 'getClientData': return ApiLk::getClientData(get_param_raw("client_id")); break;
		case 'saveClientData': return ApiLk::saveClientData(get_param_raw("client_id"), get_param_raw("data")); break;
		case 'getCompanyName': return ApiLk::getCompanyName(get_param_raw("client_id")); break;
		
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





