<?php

echo date("r")."\n";
	define('NO_WEB',1);
	define('NUM',20);
	define('PATH_TO_ROOT','./');
	define('INCLUDE_PATH','./include/');
    define('DEBUG_LEVEL', 0);
    require_once('./include/MyDBG.php');
    require_once('./include/sql.php');
    require_once('./include/util.php');
    require_once('./include/bill.php');
    require_once('./include/writeoff.php');
    //include "./include/1c_integration.php";



    class UserUtilDelOrder {
        function Get($n)
        {
            if($n = "name") return "automat";
            else die("параметр не найден: ".$n);

        }
    }

$user = new UserUtilDelOrder();


    $db	= new MySQLDatabase("localhost", "latyntsev", "kxpyLNJ", "nispd");
    $db->Query("set names koi8r");

    /*
    foreach($db->AllRecords("SELECT bill_no FROM `newbills_add_info` where req_no = '29459' and bill_no != '201011/0447'") as $b)
{
    echo "\n".$b["bill_no"];
        Bill::RemoveBill($b["bill_no"]);
}
*/

    for($i = 1; $i <= 409; $i++)
{
    $bill = "201112-".sprintf("%04.0f", $i);
    echo "\n".$bill;
    Bill::RemoveBill($bill);
}



