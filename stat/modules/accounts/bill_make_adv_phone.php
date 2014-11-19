<?php
die('This function marked for deleting');
    error_reporting(E_ALL);
    set_magic_quotes_runtime(0);
    include  "../../include_archaic/lib.php";
    include  "bill_make_lib.php";
    db_open();
    $client=$_GET['client'];
    if (strlen($client)<=0){
		echo "no client specified";
		exit;
    }
	$period=date("Y-m");
	$bill_date=date("Y-m-d");
    $bill_no=do_make_bill_generate_number(substr($period,0,4).substr($period,5,2));
	do_make_add_line($bill_no,"Подключение телефонного номера",1,199,"${period}-01");
	do_make_add_line($bill_no,"Подключение телефонной линии",1,99,"${period}-01");
	do_make_add_line($bill_no,"Залог за VoIP шлюз",1,SUM_PHONE_ADVANCE,"${period}-01");
    do_make_bill_register($bill_no,$bill_date,$client,SUM_PHONE_ADVANCE,'connection',0);
	echo "Счёт выставлен";
?>
