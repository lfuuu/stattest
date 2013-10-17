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
		case 'getBalance': return Func::getBalance(get_param_raw("client_id")); break;
		case 'getBalanceList': return Func::getBalanceList(get_param_raw("client_id")); break;
		case 'getUserBillOnSum': return Func::getUserBillOnSum(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'getBillURL': return Func::getBillURL(get_param_raw("bill_no")); break;
		case 'getReceiptURL': return Func::getReceiptURL(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'getPropertyPaymentOnCard': return Func::getPropertyPaymentOnCard(get_param_raw("client_id"), get_param_raw("sum")); break;
		case 'updateUnitellerOrder': return Func::updateUnitellerOrder(get_param_raw("order_id")); break;
		default: throw new Exception("Функция не определенна");
	}
}


class Func
{
	public function getBalance($clientIds)
	{
		if(!is_array($clientIds))
			$clientIds = array($clientIds);

		foreach ($clientIds as $clientId)
		{
			if(!$clientId || !preg_match("/^\d{1,6}$/", $clientId))
				throw new Exception("Неверный номер лицевого счета!");
		}

		$result = array();	
		foreach ($clientIds as $clientId)
		{

			$c = ClientCard::find_by_id($clientId);

			if(!$c)
			{
				throw new Exception("Лицевой счет не найден!");
			}
			$result[$c->id] = array("id" => $c->id, "balance" => $c->balance);
		}

		return $result;
	}

	public function getBalanceList($clientId)
	{
		if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
			throw new Exception("Неверный номер лицевого счета!");

		
		$c = ClientCard::find_by_id($clientId);
		if(!$c)
		{
			throw new Exception("Лицевой счет не найден!");
		}

		$params = array("client_id" => $c->id, "client_currency" => $c->currency);
		
		list($R, $sum, ) = BalanceSimple::get($params);

		$bills = array();
		foreach ($R as $r)
		{
			$b = $r["bill"];
			$bill = array(
				"bill_no"   => $b["bill_no"], 
				"bill_date" => $b["bill_date"], 
				"sum"       => $b["sum"], 
				"type"      => $b["nal"], 
				"pays"      => array()
				);

			foreach ($r["pays"] as $p)
			{
				$bill["pays"][] = array(
					"no"   => $p["payment_no"], 
					"date" => $p["payment_date"], 
					"type" => $p["type"], 
					"sum"  => $p["sum_rub"]
					);
			}
			$bills[] = $bill;
		}

		$sum = $sum["RUR"];
	
        $p = Payment::first(array(
			"select" => "sum(sum_rub) as sum",
			"conditions" => array("client_id" => $c->id)
			)
		);

		$nSum = array(
			"payments" => $p ? $p->sum : 0.00,
			"bills" => $sum["bill"],
			"saldo" => $sum["delta"],
			"saldo_date" => $sum["ts"] ? date("Y-m-d", $sum["ts"]) : ""
			);

		return array("bills" => $bills, "sums" => $nSum);

	}

	public function getUserBillOnSum($clientId, $sum)
	{
		if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
			throw new Exception("Неверный номер лицевого счета!");

		$sum = (float)$sum;

		if(!$sum || $sum < 1 || $sum > 1000000)
			throw new Exception("Ошибка в данных");


		$c = ClientCard::find_by_id($clientId);
		if(!$c)
			throw new Exception("Лицевой счет не найден!");

		/* !!! проверка поличества созданных счетов */


		$bill = self::_getUserBillOnSum_fromDB($clientId, $sum);

		if(!$bill)
		{
			include_once INCLUDE_PATH.'bill.php';

			$newBill = new Bill(null, $clientId, strtotime(date("Y-m-d")), 0, 'RUR', true, true);
			$newBill->AddLine("RUR", Encoding::toKoi8r("Авансовый платеж"), 1, $sum/1.18, 'zadatok');
			$newBill->save();

			$bill = self::_getUserBillOnSum_fromDB($clientId, $sum);
		}

		if(!$bill)
			throw new Exception("Невозможно создать счет");

		return $bill;
	}

	private function _getUserBillOnSum_fromDB($clientId, $sum)
	{
		global $db;

		return $db->GetValue(
			"SELECT 
				b.bill_no 
			FROM 
				`newbills` b, newbill_lines l 
			where 
					b.bill_no = l.bill_no 
				and client_id = '".$clientId."' 
				and is_user_prepay = 1
				and l.sum = '".$sum."'");
	}

	public function getBillUrl($billNo)
	{
		$bill = NewBill::first(array("bill_no" => $billNo));
		if(!$bill)
			throw new Exception("Счет не найден");

		if(!defined('API__print_bill_url') || !APP__print_bill_url)
			throw new Exception("Не установлена ссылка на печать документов");

		$R = array('bill'=>$billNo,'object'=>"bill-2-RUR",'client'=>$bill->client_id);
		return API__print_bill_url.udata_encode_arr($R);
	}

	public function getReceiptURL($clientId, $sum)
	{
		if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
			throw new Exception("Неверный номер лицевого счета!");

		$sum = (float)$sum;

		if(!$sum || $sum < 1 || $sum > 1000000)
			throw new Exception("Ошибка в данных");


		$c = ClientCard::find_by_id($clientId);
		if(!$c)
			throw new Exception("Лицевой счет не найден!");

		$R = array("sum" => $sum, 'object'=>"receipt-2-RUR",'client'=>$c->id);
		return API__print_bill_url.udata_encode_arr($R);
	}

	public function getPropertyPaymentOnCard($clientId, $sum)
	{
		global $db;

		if(!defined("UNITELLER_SHOP_ID") || !defined("UNITELLER_PASSWORD"))
			throw new Exception("Не заданы параметры для UNITELLER в конфиге");

		if (is_array($clientId) || !$clientId || !preg_match("/^\d{1,6}$/", $clientId))
			throw new Exception("Неверный номер лицевого счета!");

		$sum = (float)$sum;

		if(!$sum || $sum < 1 || $sum > 1000000)
			throw new Exception("Ошибка в данных");


		$c = ClientCard::find_by_id($clientId);
		if(!$c)
			throw new Exception("Лицевой счет не найден!");


		$sum = number_format($sum, 2, '.', '');			
		$orderId = $db->QueryInsert('payments_orders', array(
			'type' => 'card',
			'client_id' => $clientId,
			'sum' => $sum
			)
		);

		$signature = strtoupper(md5(UNITELLER_SHOP_ID . $orderId . $sum . UNITELLER_PASSWORD));

		return array("sum" => $sum, "order" => $orderId, "signature" => $signature);
	}

	public function updateUnitellerOrder($orderId)
	{
		return true;
		exit();
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





