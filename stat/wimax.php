<?php

//define("print_sql", 1);

//if($_SERVER["argv"][1] != "go") exit();

echo date("r")."\n";
	define('NO_WEB',1);
	define('NUM',20);
	define('PATH_TO_ROOT','./');
	define('INCLUDE_PATH',			PATH_TO_ROOT.'include/');
    define('DEBUG_LEVEL', 0);
    require_once('./include/sql.php');
    require_once('./include/util.php');
    include "./include/1c_integration.php";



    class UserWimax {
        function Get($n)
        {
            if($n = "name") return "update1";
            else die("параметр не найден: ".$n);

        }
    }

$user = new UserWimax();


    $db2		= new MySQLDatabase("85.94.32.194", "b_wificomstar", "90516ac423d", "welltone_new3");
    //$db	= new MySQLDatabase("localhost", "latyntsev", "kxpyLNJ", "test_operator");
    $db	= new MySQLDatabase("localhost", "latyntsev", "kxpyLNJ", "nispd");
    $db->Query("set names utf8");

	$bm = new \_1c\billMaker($db);
/*
    $a = array();
    foreach($db->AllRecords(
                "select  t.folder, substring(n1.bill_no, 8) as b_no,
                n1.*,n2.* from  (select req_no, fio from `newbills_add_info`  where order_mail_id > 0) n2, `newbills_add_info` n1  
                left join tt_troubles t on t.bill_no = n1.bill_no
                where n1.req_no = n2.req_no and n1.fio = n2.fio and substring(n1.bill_no, 8) > 147") as $r)
{
    $a[$r["req_no"]][] =$r;
}


foreach($a as $r)
{
    if(count($r) == 2){

        echo "\n--------------";
        foreach($r as $R)
        echo "\n".$R["req_no"].": ".$R["bill_no"]." ".$R["order_mail_id"];

        $orderMailId=  $r[0]["order_mail_id"];
        $bill_no = $r[1]["bill_no"];
        $bill_no2 = $r[0]["bill_no"];

        if($r[0]["order_mail_id"] == 0){
            $orderMailId=  $r[1]["order_mail_id"];
            $bill_no = $r[0]["bill_no"];
            $bill_no2 = $r[1]["bill_no"];
        }
        $q = "insert into newbill_wimax_orders values ('".$bill_no."', '".$orderMailId."')";
        echo "\n $q";
        $db->Query($q);
        echo "\n to delete: ".$bill_no2;
        delOrder($bill_no2);
        //print_r($r);
    }else{
        echo "\n--------------";
        echo "\n count: ".count($r);

        foreach($r as $R)
        echo "\n".$R["req_no"].": ".$R["bill_no"]." ".$R["order_mail_id"]." ".$R["folder"];

        if(count($r) == 3) {
            $order_mail_id = $r[2]["order_mail_id"];
            $to_del_bill = $r[2]["bill_no"];
            $to_set_bill = $r[1]["bill_no"];
            $q = "insert into newbill_wimax_orders values ('".$to_set_bill."', '".$order_mail_id."')";
            echo "\n $q";
            $db->Query($q);
            echo "\nto delete: ".$to_del_bill;
            delOrder($to_del_bill);
        }
    }
}

function delOrder($bill_no)
{
    global $bm, $db;
    $newStateId = 21; //отказ
    $t = $db->GetRow("select id from tt_troubles where bill_no = '".$bill_no."'");
    if($t)
    {
        $id = $db->QueryInsert('tt_stages',array(
                    "trouble_id" => $t["id"], 
                    "state_id" => $newStateId,
                    "user_main" => "update1",
                    "date_start" => date("Y-m-d H:m:s")
                    )
                );

        $db->Query('update tt_troubles set cur_stage_id = LAST_INSERT_ID(), folder=(select folder from tt_states where id='.$newStateId.') where id='.$t["id"]);
    }

    $f = $bm->setOrderStatus($bill_no, \_1c\trr("Отказ"), $fault);
		if(!$f){
            $msg = explode('|||',$fault->getMessage(),3);
			trigger_error2("Не удалось обновить статус заказа:".$bill_no."\n ".$msg[1]);
			exit();
		}
}


    exit();
    */

    $null = null;

		$adds = array(
			'ФИО'=>'fio',
            'Адрес'=>'address',
            'НомерЗаявки'=>'req_no',                  'ЛицевойСчет'=>'acc_no',
			'НомерПодключения'=>'connum',             'Комментарий1'=>'comment1',    'Комментарий2'=>'comment2',
			'ПаспортСерия'=>'passp_series',           'ПаспортНомер'=>'passp_num'   ,'ПаспортКемВыдан'=>'passp_whos_given',
			'ПаспортКогдаВыдан'=>'passp_when_given',  'ПаспортКодПодразделения'=>'passp_code',
			'ПаспортДатаРождения'=>'passp_birthday',  'ПаспортГород'=>'reg_city',
			'ПаспортУлица'=>'reg_street',             'ПаспортДом'=>'reg_house',     'ПаспортКорпус'=>'reg_housing',
			'ПаспортСтроение'=>'reg_build',           'ПаспортКвартира'=>'reg_flat', 'Email'=>'email',
			'ПроисхождениеЗаказа'=>'order_given',     'КонтактныйТелефон'=>'phone'

            ,
            'Метро' => 'comment2',
            'Логистика' => 'logistic',
            "ВладелецЛинии" => 'line_owner'
		);

    $f = array(
            "Оборудование/Услуга:" => "what",
            "Оборудование:" => "what",
            "Аренда:" => "what",
            "Тариф:" => "tarif",
            "Цена:" => "price",
            "Цвет:" => "color",
            "Контактное лицо:" => "fio",
            "Паспорт серия:" => "passp_series",
            "Паспорт номер:" => "passp_num",
            "Паспорт кем выдан:" => "passp_whos_given",
            "Паспорт дата выдачи:" => "passp_when_given",
            "Паспорт дата рождения:" => "passp_birthday",
            "Паспорт город:" => "reg_city",
            "Паспорт улица:" => "reg_street",
            "Паспорт дом:" => "reg_house",
            "Паспорт корпус:" => "reg_housing",
            "Паспорт квартира:" => "reg_flat",
            "Номер договора с &laquo;КОМСТАР-Директ&raquo;:" => "comment1_2",
            "Адрес доставки город:" => "address_1",
            "Адрес доставки улица/дом/квартира:" => "address_2",
            "Адрес доставки метро:" => "comment2", //metro_id
            "Дополнительная информация:" => "comment1",
            "Контактный телефон:" => "phone",
            "Email:" => "email",
            "IP-адрес:" => "comment1_1",
            "Получать информационные сообщения от Комстар WiMAX:" => "comment1_3",
            "Лицевой счет Стрим: " => "n/a",
            "Лицевой счет Комстар WiMAX: "=>"n/a"
            );


            $error = array();

            $aGoods = array();

            $gNumToId = array();

            foreach($db->AllRecords("select id, num_id ,name from g_goods where 
                        num_id in (
                            3234, 3235, 3236 , 3230 , 3231 , 3232 , 3233  , 3229  , 3358  , 3415  ,3416  ,
                            3566,3604,3605,3606,3865,4201,10935, 4216, 13944 )
                        ") as $g){
                $aGoods[$g["name"]] = $g["num_id"];
                $gNumToId[$g["num_id"]] = $g["id"];
            }

    foreach($db2->AllRecords("select mail_id from b_inbox_wificomstar where mail_id > 6260577") as $m){
        if($db->GetRow("select order_mail_id from newbill_wimax_orders where order_mail_id = '".$m["mail_id"]."'")) continue;
        $m = $db2->GetRow("select * from b_inbox_wificomstar where mail_id = '".$m["mail_id"]."'");

        
        //echo $m["html"];
        $d = array();
        preg_match_all("@<strong>([^<]+)</strong> *?([^<]+) *<@U", $m["html"], $out, PREG_SET_ORDER);
        if(!$out) {
            if(strpos($m["html"], "MCN - Заявки wimax на") === 0) continue;
            $error[] = $m["mail_id"]." - not parsed"; 
            continue;
        }

        preg_match_all("@Заявка[^\d]+(\d+?)@U", $m["html"], $z);
        //print_r($z);
        $z = $z[1][0];
    

        $fAll = true;
        foreach($out as $o) {
            unset($o[0]);
            if(isset($f[$o[1]])) {
                $d[$f[$o[1]]] = $o[2];
            }else{
                $fAll = false;
                $d[$o[1]] = $o[2];
            }
        }

        if(!$fAll){
            $error[] = var_export("mail_id=>".$m["mail_id"], true)."\n".var_export($d, true);
            continue;
        }

        $d["comment1"] = @$d["comment1"];
        $d["comment1"] .= (@$d["comment1_1"] ? ($d["comment1"] ? ", " :"")."IP-адрес: ".$d["comment1_1"] : "");
        if(isset($d["color"]) && $d["color"] && $d["color"] != "-")
            $d["what"] .= " - ".$d["color"];

        unset($d["comment1_1"],$d["comment1_2"], $d["comment1_3"]);

        $d["address"] = (@$d["address_1"] ? "г. ".$d["address_1"] : "").(@$d["address_2"] ? ", ул. ".$d["address_2"] : "");
        unset($d["address_1"],$d["address_2"]);
        if(@$d["tarif"] == "1") $d["tarif"] = "M1";
        if(@$d["tarif"] == "2") $d["tarif"] = "M2";
        $d["req_no"] = $z;

        print_r($d);
        if($metro = $db->GetRow("select id from metro where name = '".$db->escape(trim($d["comment2"]))."'")) {
            $d["metro_id"] = $metro["id"];
        }
        $dFull = $d;

        unset($d["what"], $d["price"], $d["tarif"]);

        /*
        $d["bill_no"] = 610754;


        $db2->QueryDelete("newbills_add_info", array("bill_no" => $d["bill_no"]));
        $db2->QueryInsert("newbills_add_info", $d);

        $db2->QueryDelete("newbill_lines", array("bill_no" => $d["bill_no"]));
        */

        $goods = array();
        if(isset($dFull["tarif"]) && isset($aGoods[$dFull["tarif"]])){
            $goods[] = $aGoods[$dFull["tarif"]];
        }

        $dFull["metro"] = $dFull["comment2"];


        $ai = array();
        foreach($adds as $name => $af){
            $ai[$name] = isset($dFull[$af]) ?  html_entity_decode($dFull[$af]) : "";
        }


        $res = array(
                "client_tid" => "WiMaxComstar",
                "order_number" => false,
                "items_list" => getGoods($dFull["what"], $goods),
                "order_comment" => \_1c\trr($dFull["what"]),
                "is_rollback" => false,
                "add_info" => $ai,
                "store_id" => "8e5c7b22-8385-11df-9af5-001517456eb1"
                );


        try{
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

        print_r($ret);
        $bill_no = $ret->Номер;


        $sh = new \_1c\SoapHandler();
        $sh->statSaveOrder($cl, $bill_no, $c1error);

        $db->Query("insert ignore into newbill_wimax_orders set order_mail_id = '".$m["mail_id"]."', bill_no = '".$bill_no."'");

        //print_r($ret);

    }

$dd = (int)date("Hi");
if($dd > 55 and $dd < 103) //between 00:55 and 01:0
$a = 1; else exit();


$aResp = array(
        6284458, 6434880, 6847089, 6964866, 6965884, 6965888, 6967601, 
        6967613, 6967643, 6971971, 6971981, 6971982, 6971993, 6976766, 
        6976767, 6976768, 7031122, 7030631, 7081178, 7156984, 7157371, 
        7165664, 7169916, 7171323, 7172490, 7177263, 7182491, 7338885,
        7362956, 7391731, 7633765, 7657505, 7657506, 7657514, 7657518, 
        7657529, 7657530, 7657531, 7657536, 7658578, 7658622, 7662296, 
        7662823, 7666509, 7666701, 7669602, 7669603, 7670133, 7670134, 
        7687343, 7692365, 7696932
);


if($error)
    foreach($error as $idx => $mess)
{

    foreach($aResp as $n)
        if(strpos($mess, $n." ") !== false){

            unset($error[$idx]);
            break;
        }
}

/*
if($error){
    mail("dga@mcn.ru", "wimax errors", var_export($error, true));

    var_export($error);
}*/


function getGoodsLines($goods)
{
    global $db;
    if(!$goods) return array();
    return $db->AllRecords("select g.id, 1 as quantity, 0 as code_1c,1 as price FROM g_goods g where g.num_id in (".implode(", ", $goods).") ");
}




function getGoods($t, $goods)
{

    // parse
    $u = explode("+", $t);
    if(isset($u[1]) && trim($u[1]) && !trim(@$u[2]))
    {
        $u[2] = trim($u[1]);
        $u[1] = "";
    }
    if(preg_match("@(\d+) \* *(.+?) *?@U", $u[0], $o)){
        $st = array("count" => $o[1], "dev" => trim($o[2]));
    }
    $st["t1"] = trim(@$u[1]);
    $st["t2"] = trim(@$u[2]);

    print_r($st);


    // add goods
    
    $addBM325 = true;
    //3865 - аренда

    /*
        +--------------------------------------+--------+------------------------------------------------------------+
        | id                                   | num_id | name                                                       |
        +--------------------------------------+--------+------------------------------------------------------------+
        | 2ce73166-98a3-11df-95ab-001517456eb1 |   3605 | ColibriComstar Нетбук, без подп?лючения                     |
        | 2ce73167-98a3-11df-95ab-001517456eb1 |   3606 | WiMAX                                                      |
        | 32fe70ff-98a3-11df-95ab-001517456eb1 |   3865 | BM325 АРЕНДА Модем, USB WIMAX                              |
        | 2ce73163-98a3-11df-95ab-001517456eb1 |   3566 | BM325 Модем, USB WIMAX                                     |
        | c385987e-a6fb-11df-8ceb-001517456eb1 |   4201 | Seowon Модем, USB WIMAX                                    |
        | c7b3eca8-c0cf-11df-9864-001517456eb1 |  10935 | Wi-Spot Роутер                                             |
        +--------------------------------------+--------+------------------------------------------------------------+
        */


    global $gNumToId;

    $wiSpotDescr = array(
            "white" => "2b5def5b-e33b-11df-9f2d-001517456eb0",
            "black" => "2b5def5a-e33b-11df-9f2d-001517456eb0"
            );
    $zeroDescr = "00000000-0000-0000-0000-000000000000";


    if(strpos($st["t1"], "Colibri Comstar") !== false)
        if($st["t2"] == "без подключения к услуге"){
            $goods[] = 3605;
            $addBM325 = false;
        }elseif(strpos($st["t2"], "год безлимитного WiMAX") !== false){
            $goods[] = 3605;
            $goods[] = 3606;
            $addBM325 = false;
        }



    if(strpos($st["dev"],"Seowon") !== false){
        $goods[] = 4201;
        $addBM325 = false;
    }elseif(strpos($st["dev"], "Wi-Spot New") !== false){

        $goodNum = "13944";
        $descr = $zeroDescr;

        return array(array("id" => $gNumToId[$goodNum].":".$descr, "quantity" => 1, "code_1c" => 0, "price" => 1));

    }elseif(strpos($st["dev"], "Wi-Spot") !== false){
        $goodNum = "10935";
        if(strpos($st["dev"], "Белый") !== false){
            $descr = $wiSpotDescr["white"];
        }elseif(strpos($st["dev"], "Черный") !== false){
            $descr = $wiSpotDescr["black"];
        }else{
            $descr = $zeroDescr;
        }
        return array(array("id" => $gNumToId[$goodNum].":".$descr, "quantity" => 1, "code_1c" => 0, "price" => 1));
        $goods[] = "10935";
        $addBM325 = false;
    }

    if($addBM325)
        $goods[] = $st["t2"] == "Аренда модема" ? 3865: 3566;

    $nGoods = array();
    foreach($goods as $g) {
        $nGoods[] = array("id" => $gNumToId[$g].":".$zeroDescr, "quantity" => 1, "code_1c" => 0, "price" => 1);
    }
    return $nGoods;
}
