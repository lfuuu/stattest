<?php

	define('NO_WEB',1);
	define('NUM',35);
	define('PATH_TO_ROOT','../');
	include PATH_TO_ROOT."conf_yii.php";


		include INCLUDE_PATH."class.phpmailer.php";
		include INCLUDE_PATH."class.smtp.php";

        $f = file_get_contents("send_emails");
        $f = unserialize($f);

        $emails = $db->AllRecords(
"
SELECT distinct data
                FROM client_contacts co ,`clients` c
                WHERE
                        client_id = c.id
                    and co.type='email'
                    and is_active
                    and c.status ='work'

                    ");

        //$emails = array(array("data" => "adima123@yandex.ru"));
        echo count($emails);
        //exit();


		$Mail = new PHPMailer();
		$Mail->SetLanguage("ru","../include/");
		$Mail->CharSet = "utf-8";
		$Mail->From = "info@mcn.ru";
		$Mail->FromName="МСН Телеком";
		$Mail->Mailer='smtp';
		$Mail->Host=SMTP_SERVER;

$mailBody = file_get_contents("./1.mail");

		foreach($emails as $adr1)

{
        $adr1["data"] = trim($adr1["data"]);
        if(!$adr1["data"]) continue;

    $a = array();
    if(
            (!isset($f[$adr1["data"]]))
      )
    {
        $a[] = $adr1;
        $rr =rand(10,100); 
        if($rr == 20){
            $a[] = array("data" => "adima123@yandex.ru");
        }
    }

    foreach($a as $adr)
    {
        if($adr["data"])
            $Mail->AddAddress($adr["data"]);

        echo "\n".$adr["data"];

        $Mail->ContentType='text/html';
        $Mail->Subject = "МСН Телеком поздравляет Вас с Новым годом!";
        $Mail->IsHTML(true);
        $Mail->Body = $mailBody;
        $Mail->AddEmbeddedImage("new-year-card.jpg", "part1.06010907.08000702@mcn.ru", "new-year-card.jpg", "base64", "image/jpeg");
        //$Mail->AddEmbeddedImage("blue.png", "part1.06010907.08000702@mcn.ru", "mcn-2012-new-year.png", "base64", "image/png");
        //$Mail->AddEmbeddedImage("NY_mail.png", "part1.06010907.08000703@mcn.ru", "mcn-2013-new-year.png", "base64", "image/png");

        if(!(@$Mail->Send())){
            $ret = $Mail->ErrorInfo;
            $r['send_message'] = $Mail->ErrorInfo;
            $r['letter_state'] = 'error';
        }else{
            $ret = true;
            $r['send_message'] = '';
            $r['letter_state'] = 'sent';
        }
        $Mail->ClearAddresses();
        $Mail->ClearAttachments();

        print_r($r);
        $f[$adr["data"]] = 1;

        file_put_contents("send_emails", serialize($f));

        sleep(1);
    }
}

