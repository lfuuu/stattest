<?php

    echo date("r")."\n";
	define('NO_WEB',1);
	define('NUM',20);
	define('PATH_TO_ROOT','./');
	define('INCLUDE_PATH',			PATH_TO_ROOT.'include/');
    define('DEBUG_LEVEL', 0);
    require_once('./include/MyDBG.php');
    require_once('./include/sql.php');
    require_once('./include/util.php');
    include "./include/1c_integration.php";



class UserNetByNet
{
    function Get($n)
    {
        if($n = "name") return "update1";
        else die("параметр не найден: ".$n);

    }
}

$user = new UserNetByNet();


    $db2= new MySQLDatabase("85.94.32.194", "b_wificomstar", "90516ac423d", "welltone_new3");
    //$db	= new MySQLDatabase("localhost", "latyntsev", "kxpyLNJ", "test_operator");
    $db	= new MySQLDatabase("localhost", "latyntsev", "kxpyLNJ", "nispd");
    $db->Query("set names koi8r");

	$bm = new \_1c\billMaker($db);

$bb = array();
$lastMailId = $db->GetValue("select id from nbn_mail order by id desc limit 1");
if(!$lastMailId)
    $lastMailId = 0;

$mailId = $db2->GetValue("select mail_id from b_inbox_netbynet where mail_id > ".$lastMailId." limit 1");
if($mailId)
{
    $mail = $db2->GetValue("select full from `b_inbox_netbynet` where mail_id = '".$mailId."'");
    $mail = str_replace("\r", "", $mail);
    $oMail = parseMail($mail);
}

foreach($bb as $m)
{
    if(strpos($m["head"], "Content-Type") !== false &&  strpos($m["head"], "ms-excel") !== false)
    {
        $body = $m["bodyes"];
        $body = base64_decode(str_replace("\n", "", $body));

        $r = rand(1,10000);
        @unlink("/tmp/nbn".$r.".xls");
        file_put_contents("/tmp/nbn".$r.".xls", $body);

        $w = open_file("/tmp/nbn".$r.".xls");

        list($startCol, $startRow) = findStartPosition();

        $dd = fillData($startCol, $startRow);
        @unlink("/tmp/nbn".$r.".xls");
        saveTo1C($dd);
    }
}

if($mailId)
    $db->Query("update nbn_mail set id = '".$mailId."'");



exit();
    




// check


//print_r($dd);
//print_r($q);

function saveTo1C($dd)
{

    $zeroDescr = "00000000-0000-0000-0000-000000000000";

    $count = 0;

    foreach($dd as $reqNo => $d)
    {

        echo "\n".$reqNo;
        //if($count++ > 0) break;

        $metro = "";
        if(preg_match_all("/м\.(.*)$/six",$d['address'], $o ))
            $metro = $o[1][0];

        if($metro == "-")
            $metro = "";


        $ai = array (
                'ФИО' => $d['fio'],
                'Адрес' => $d['address'],
                'НомерЗаявки' => $d["req_no"],
                'ЛицевойСчет' => '',
                'НомерПодключения' => '',
                'Комментарий1' => '',
                'Комментарий2' => '',
                'ПаспортСерия' => '',
                'ПаспортНомер' => '',
                'ПаспортКемВыдан' => '',
                'ПаспортКогдаВыдан' => '',
                'ПаспортКодПодразделения' => '',
                'ПаспортДатаРождения' => '',
                'ПаспортГород' => '',
                'ПаспортУлица' => '',
                'ПаспортДом' => '',
                'ПаспортКорпус' => '',
                'ПаспортСтроение' => '',
                'ПаспортКвартира' => '',
                'Email' => '',
                'ПроисхождениеЗаказа' => '',
                'КонтактныйТелефон' => $d["phone"],
                'Метро' => $metro,
                'Логистика' => '',
                'ВладелецЛинии' => '',
                );

        //print_r($ai);
        //exit();

        $_d = $d;
        unset($_d["order"]);

        $res = array(
                "client_tid" => "nbn",//WiMaxComstar",
                "order_number" => false,//"201109/0094",//false,
                "items_list" => array(
                    array("id" => "4e8cc21b-d476-11e0-9255-d485644c7711".":".$zeroDescr, "quantity" => 1, "code_1c" => 0, "price" => 1), // 13340
                    array("id" => "a449a3f7-d918-11e0-bdf8-00155d21fe06".":".$zeroDescr, "quantity" => 1, "code_1c" => 0, "price" => 1)  // 13363
                    ),
                "order_comment" => \_1c\trr(@implode("\n", $_d)),
                "is_rollback" => false,
                "add_info" => $ai
                );

        global $bm;

        try{
            $null = null;
            $ret = $bm->saveOrder($res, $null, false);
        }catch(Exception $e){
            print_r($res);
            var_dump($e);
            exit();
        }

        $c1error = '';
        $cl = new stdClass();
        $cl->order = $ret;
        $cl->isRollback = false;

        $bill_no = $ret->Номер;

        echo "\n".date("r").": ".$bill_no;

        //define("print_sql", 1);

        $sh = new \_1c\SoapHandler();
        $sh->statSaveOrder($cl, $bill_no, $c1error);

        //$db->Query("insert ignore into newbill_wimax_orders set order_mail_id = '".$m["mail_id"]."', bill_no = '".$bill_no."'");
    }
}

function parseMail($mail)
{
    global $bb;
    $pos = strpos($mail, "\n\n");

    $h = trim(substr($mail, 0, $pos));
    $b = trim(substr($mail, $pos));

    if($h == "--" || trim($h) == "") return false;

    $retObj = array("head" => $h, "bodyes" => array());

    if(preg_match_all("/boundary=\"?([^\r\n\"]+)/", $h, $o))
    {
        $bs = explode("--".$o[1][0], $b);

        foreach($bs as $o)
        {
            $v = parseMail($o);
            if($v)
            {
                if(!is_array($v["bodyes"]))
                    $bb[] = $v;

                $retObj["bodyes"][] = $v;
            }
        }
    }else{
        $retObj["bodyes"] = $b;
    }

    return $retObj;
}
function findStartPosition()
{
    $startCol = $startRow = 1;
    for($i = 0; $i < 6 ; $i++)
        for($j = 0; $j < 6 ; $j++)
            if(val($startCol+$i, $startRow+$j) == "Номер" && val($startCol+1+$i, $startRow+$j) == "Дата" && trim(val($startCol+6+$i, $startRow+$j)) == "Вид")
                return array($i+1, $j+1);

    return array(0,0);
}

function fillData($startCol, $startRow)
{
    $dd = array();

    if($startCol && $startRow)
    {
        $d = array();
        for($i = 1; $i < 10000; $i++)
        {
            $count = val($startCol+8, $startRow+$i);
            if(!$count) break;

            $id = val($startCol, $startRow+$i);
            //if($id != "ИМ-9965952") continue;

            if($id)
            {
                if($d)
                    $dd[$d["req_no"]] = $d;

                $d = array(
                        "req_no" => $id,
                        "date" => val($startCol+1, $startRow+$i),
                        "fio" => val($startCol+2, $startRow+$i),
                        "pin" => val($startCol+3, $startRow+$i),
                        "phone" => val($startCol+4, $startRow+$i),
                        "address" => val($startCol+5, $startRow+$i),
                        "order" => array()
                        );

            }
            $d["order"][] = array(
                    "type" => val($startCol+6, $startRow+$i) == "Товар" ? "good" : "service",
                    "art" => val($startCol+7, $startRow+$i),
                    "count" => val($startCol+8, $startRow+$i)
                    );

        }
        if($d)
            $dd[$d["req_no"]] = $d;
    }
    return $dd;
}

function val($col, $row)
{
    global $w;
    $v = $w->getCell(c($col, $row))->getValue();
    $v = iconv("koi8-r", "utf-8",$v);
    return $v;
}


function c($col, $row)
{
    $a = ord("A")-1;
    return chr($a+$col).$row;
}

//$t = read_table($w, array());
//print_r($w);

	function &open_file($filename, $format='Excel5'){
		require_once INCLUDE_PATH.'exel/PHPExcel.php';
		require_once INCLUDE_PATH.'exel/PHPExcel/IOFactory.php';
		$excelReader = PHPExcel_IOFactory::createReader($format);
		$excelReader->setReadDataOnly(true);
	
		$objExcel = $excelReader->load($filename);
		if(!$objExcel) { $objExcel = false; return $objExcel; }
		$objWorksheet = @$objExcel->getActiveSheet();
		if(!$objWorksheet) { $objWorksheet = false; return $objWorksheet; }
		
		return $objWorksheet;
	}

