<?php
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
    if (!isset($_GET['go'])) {
		echo "<form action='?go=1&client=".$client."' method=post>Размер задатка: <input type=text name=sum_virtual value='".SUM_ADVANCE."'>";
		echo "<br>Текст: <input type=text name=text value='Задаток за подключение интернет-канала'><br>";
		echo "<input type=submit value='выставить'></form><br>";
		return;
	}
	$sum_virtual=floatval($_POST['sum_virtual']);
	$text=$_POST['text'];
	$period=date("Y-m");
	$bill_date=date("Y-m-d");
	$bill_no=do_make_bill_generate_number(substr($period,0,4).substr($period,5,2));
	do_make_add_line($bill_no,$text,1,0,"${period}-01");
	do_make_bill_register($bill_no,$bill_date,$client,0,'advance',0,$sum_virtual);
	echo "Счёт выставлен";
?>
