<?php
	//этот файл может использоваться для аяксовых вызовов. и всё.
	define("PATH_TO_ROOT",'./');
	define('NO_WEB',1);
	include PATH_TO_ROOT."conf.php";
    require_once 'Spreadsheet/Excel/Writer.php';

    //echo "<table border=1><tr><td>#заявки</td><td>заказ</td><td>статус</td><td>итог</td><td>коментарий</td></tr>";

    $filePath = '/tmp/wimax_mcn_report'.date("Y-m-d").'.xls';
    $fileName = 'wimax_mcn_report_'.date("Y-m-d").'.xls';

@unlink($filePath);

$workbook = new Spreadsheet_Excel_Writer($filePath);
$workbook->setVersion(8);
$sheet =& $workbook->addWorksheet();
$sheet->setInputEncoding('koi8-r');

$fHeader =& $workbook->addFormat();
$fHeader->setHAlign('center');
$fHeader->setSize(10);
$fHeader->setBorder(1);
$fHeader->setBold();


$fData =& $workbook->addFormat();
$fData->setSize(8);
$fData->setBorder(1);

    $sheet->write(2, 0, "#заявки", $fHeader);
    $sheet->write(2, 1, "заказ", $fHeader);
    $sheet->write(2, 2, "текущий статус", $fHeader);
    $sheet->write(2, 3, "итог", $fHeader);
    $sheet->write(2, 4, "комментарии", $fHeader);

    $is29459 = false;

    $no = 0;
    foreach($db->AllRecords("
                SELECT 
                i.req_no, 
                (
                 select group_concat(item) 
                 from newbill_lines nl 
                 where nl.bill_no = i.bill_no
                ) as param,
                (select sum(if(type='service',1,0)) from newbill_lines where bill_no  = i.bill_no) as is_service_in,
                st.name as state_name, 
                convert((
                        select group_concat( concat(date_edit, ' ', st.name,' ',comment) SEPARATOR ' // ')  
                        from tt_troubles tr2 
                        left join tt_stages ts2 on (tr2.id = ts2.trouble_id)
                        left  join tt_states st2 on st2.id = ts2.state_id
                        where tr2.bill_no = i.bill_no
                        ) using koi8r) as comment
                FROM newbills as b
                inner join `newbills_add_info` i using (bill_no)
                left join tt_troubles tr using (bill_no)
                left join tt_stages ts on (tr.cur_stage_id = ts.stage_id)
                left  join tt_states st on st.id = ts.state_id
    where client_id = 9322 and b.bill_date > '2010-11-11' order by b.bill_date") as  $q){

    if(!$q["req_no"] || !$q["param"] || $q["req_no"] == "8") continue;

    if($q["req_no"] == "29459") {
        if(!$is29459)
            $is29459 = true;
        else
            continue;
    }


    //echo "<tr>";

    if($q["state_name"] == "WiMax") $q["state_name"] = "Новая";
    if($q["state_name"] == "К отгрузке") $q["state_name"] = "Выезд";
    if($q["state_name"] == "Отгружен") $q["state_name"] = "Выезд совершен";

    $statusTotal = "В работе";

    if($q["state_name"] == "Отказ") $statusTotal = "Отказ";
    if($q["state_name"] == "Закрыт")
        $statusTotal = $q["is_service_in"] ? "активирован" : "выезд совершен";

    $sheet->write(3+$no, 0, $q["req_no"], $fData);
    $sheet->write(3+$no, 1, $q["param"], $fData);
    $sheet->write(3+$no, 2, $q["state_name"], $fData);
    $sheet->write(3+$no, 3, $statusTotal, $fData);
    $sheet->write(3+$no, 4, $q["comment"], $fData);
    $sheet->write(3+$no, 5, " ");

        /*
    echo "<td>".$q["req_no"]."</td>";
    echo "<td>".$q["param"]."</td>";
    echo "<td>".$q["state_name"]."</td>";
    echo "<td>".$statusTotal."</td>";
    echo "<td>".$q["comment"]."</td>";

    echo "</tr>";
    */
    //

    $no++;
    }

echo "\n".date("r")." заказов отослано: ".$no;

$workbook->close();

		include INCLUDE_PATH."class.phpmailer.php";
		include INCLUDE_PATH."class.smtp.php";


		$Mail = new PHPMailer();
		$Mail->SetLanguage("ru","include/");
		$Mail->CharSet = "koi8-r";
		$Mail->From = "wimax-comstar@mcn.ru";
		$Mail->FromName="МАРКОМНЕТ";
		$Mail->Mailer='smtp';
		$Mail->Host="mail.mcn.ru";

        /*
		$Mail->AddAddress("dga@mcn.ru");
		$Mail->AddAddress("tanya@mcn.ru");
        */

		$Mail->AddAddress("Karpenko_VA@comstar-uts.ru");
		$Mail->AddAddress("dashalym@mts.ru");
		$Mail->AddAddress("wimax-comstar@mcn.ru");
        
        
		$Mail->ContentType='text/plain';
		$Mail->Subject = "MCN - Заявки wimax на ".date("Y-m-d");
		$Mail->Body = "MCN - Заявки wimax на ".date("Y-m-d");

        $Mail->AddAttachment($filePath, $fileName, "base64", "application/vnd.ms-excel");

		$Mail->Send();

@unlink($filePath);

//echo "</table>";



