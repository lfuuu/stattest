<?php

	//этот файл может использоваться для аяксовых вызовов. и всё.
	define("PATH_TO_ROOT",'../../stat/');
	define('NO_WEB',1);
	include PATH_TO_ROOT."conf_yii.php";

    $id = get_param_protected("client_id","0");
    $dateFrom = get_param_protected("date_from","2000-01-01");
    $dateTo = get_param_protected("date_to","2029-01-01");

                                                                                                      
    header('Content-type: text/html; charset=utf-8');
    if($id)
{
    foreach($db->AllRecords(
                "SELECT b.bill_no, bill_date, b.client_id, l.dispatch, g.num_id, 
                    (select sum(p.sum) from newpayments p where (b.bill_no = p.bill_no)) as payment
                FROM `newbills` b, newbill_lines l , g_goods g, clients c
                where b.client_id = '".$id."' and bill_date > '".$dateFrom."' and bill_date <='".$dateTo."' 
                and l.bill_no = b.bill_no and g.id = l.item_id
                and c.id = b.client_id and c.company like '%D_%'") as $u)
    echo $u["bill_no"]."\t".$u["bill_date"]."\t".$u["dispatch"]."\t".$u["num_id"]."\t".($u["payment"]?$u["payment"]:0)."\n";
}



?>
