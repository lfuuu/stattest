<?php


	function objectsIntoArray($arrObjData, $arrSkipIndices = array())
	{
	    $arrData = array();
	    
	    // if input is object, convert into array
	    if (is_object($arrObjData)) {
	        $arrObjData = get_object_vars($arrObjData);
	    }
	    
	    if (is_array($arrObjData)) {
	        foreach ($arrObjData as $index => $value) {
	            if (is_object($value) || is_array($value)) {
	                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
	            }else{
	            	$value = iconv('utf-8', 'koi8-r', $value);
	            }
	            if (in_array($index, $arrSkipIndices)) {
	                continue;
	            }
	            $arrData[$index] = $value;
	        }
	    }
	    return $arrData;
	}
		
	
class uniteller {
	var $login = '279';
	var $password = 'nxbIHHJYU3GWVJFtYxtcpfw2dp1Pte15f0iNqEe74TifwXaXz92jXes7rQrcL54b0FsL1pXVGHZKfuFZ';
	var $shop_id = '5879813581-557';
	var $url = 'https://test.wpay.uniteller.ru';
		
	function getOrder($order_id){
		try{
			$url = $this->url . "/results/?Format=4&Shop_ID={$this->shop_id}&Login={$this->login}&Password={$this->password}&ShopOrderNumber=$order_id";
			$xmlStr = file_get_contents($url);
			$xmlObj = simplexml_load_string($xmlStr);
			$arrXml = objectsIntoArray($xmlObj);
			$order = $arrXml['orders']['order'];
			if (is_array($order)) return $order;
		}catch(Exception $e){ }
		return false;
	}
	function updateOrderInfo($order_id){
		global $db;
		$order = $this->getOrder($order_id);
		if ($order !== false) {
			$order2 = $db->GetRow("select * from payments_orders where id={$order['ordernumber']} ");
			$db->QueryUpdate('payments_orders', 'id', array('id'=>$order['ordernumber'],'status'=>$order['status'], 'sum'=>$order['total'],'details'=>serialize($order)));
			if ($db->mError != '') return false;
			if ($order['status'] == 'Paid' && $order2['status'] != 'Paid') $db->Query("update payments_orders set datepaid=now() where id={$order['ordernumber']}");
			if ($order['status'] == 'Authorized' && $order2['status'] != 'Authorized') {

				$bill = new Bill(null,$order2['client_id'],time(),0,'RUR');
				$bill->AddLine('RUR','payment to balance',1,$order2['sum'],'zadatok','','','','');
				try{
					$bill->Save();
				}catch(Exception $e){}
				$no = $bill->GetNo();
				
				$payment_id = $db->QueryInsert('newpayments', array(
														'client_id'=>$order2['client_id'], 
														'payment_no'=>$order2['id'],
														'bill_no'=>$no,
														'bill_vis_no'=>$no,
														'payment_date'=>$order2['datestart'],
														'oper_date'=>$order2['datestart'],
														'payment_rate'=>'1',
														'type'=>'bank',
														'sum_rub'=>$order2['sum'],
														'currency'=>'RUR',
														'sync_1c'=>'yes',
												));
												
				$db->Query("update payments_orders set dateauthorize=now(), bill_no='{$no}', bill_payment_id='{$payment_id}' where id={$order['ordernumber']}");												
			} 
			if ($order['status'] == 'Canceled' && $order2['status'] != 'Canceled') {
				$db->Query("update payments_orders set datecancel=now() where id={$order['ordernumber']}");	
				$db->Query("delete from newpayments where id='{$order2['bill_payment_id']}'");
				$db->Query("delete from newbill_lines where bill_no='{$order2['bill_no']}'");
				$db->Query("delete from newbills where bill_no='{$order2['bill_no']}'");
			}
			
			return true;
		} 
		return false;
	}
	
	function cancelOrder($order_id){
		global $db;
		try{
			$order = $db->GetRow('select * from payments_orders where id='.intval($_GET['order_id']));
			$data = unserialize($order['details']);
			$url = $this->url . "/unblock/?Format=1&Shop_ID={$this->shop_id}&Login={$this->login}&Password={$this->password}&Billnumber={$data['billnumber']}";
			$res = file_get_contents($url);
			/*
			$mres = explode("\n", $res);
			$rres = explode(';', $mres[0]);
			if (count($rres)<=3) return false;
			*/
			$order = $this->updateOrderInfo($order_id);
			
			return $order['status'] == 'Canceled';
		}catch(Exception $e){}
		return false;
		return $order;
	}
		
}
