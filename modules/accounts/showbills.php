<?php
$customer=get_param_protected('clients_client','');
$query="SELECT clients.company, bill_bills.bill_no as bill_no, 
	bill_bills.must_pay as must_pay,
	bill_bills.type as type,
	bill_bills.sum as sum, bill_bills.bill_date as date, 
	bill_bills.client as client, bill_bills.state as state FROM  bill_bills ";
$where="WHERE 1 ";
$join=" INNER JOIN clients ON clients.client=bill_bills.client ";

if ($customer=="" ) { 
	$customer="Для всех клиентов";
}else {
	$where.=" and bill_bills.client='$customer' ";
}
$design->assign("customer",$customer);

$bill_no=get_param_protected('bill_no');
$year=get_param_protected("year");
$month=get_param_protected("month");
$notpaid=get_param_protected('notpaid');
$cancelled=get_param_protected('cancelled');
$router=get_param_protected("router");
$manager=get_param_protected("manager");

if ($bill_no!="") {
	$where.=" and bill_no='$bill_no' ";
} else {
	if ($year) $where.=" and YEAR(bill_bills.bill_date)='$year' ";
	if ($month) $where.=" and MONTH(bill_bills.bill_date)='$month' ";
	if ($notpaid) $where.=" and bill_bills.state='ready' ";
	if (!$cancelled) $where.=" and bill_bills.state!='cancelled' ";

	if ($manager) {
		$where.=" and clients.manager='$manager' ";
	}

	if ($router!=""){
		//TODO9
		echo "Извините, показ счетов определённой коллективной точки пока невозможен";
/*		$query="SELECT bill_bills.bill_no as bill_no, 
				bill_bills.sum as sum, bill_bills.bill_date as date, 
				bill_bills.client as client, 
				bill_bills.state as state 
				FROM  bill_bills, usage_ip_ports ";
		$where.= " and bill_bills.client=usage_ip_ports.client and usage_ip_ports.node='$router' ";*/
	}
}

$query.=$join.$where. " order by client, bill_date desc";

$db->Connect();
$db->Query($query);
$bills=array();
$sum=0;
while ($row=$db->NextRecord()){
	$sum+=floatval($row['sum']);
	$bills[]=$row;
 
};
//echo "<pre>";print_r($bills);echo "</pre>";
$design->assign("bills",$bills);

$design->assign("query",$query);
$design->assign("acc_sum",$sum);


$design->AddMain("accounts/list_bills.tpl");
?>
