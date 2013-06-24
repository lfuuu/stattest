<?php

	define('NO_WEB',1);
	define('NUM',35);
	define('PATH_TO_ROOT','./');
	include PATH_TO_ROOT."conf.php";


		include INCLUDE_PATH."class.phpmailer.php";
		include INCLUDE_PATH."class.smtp.php";

        $f = file_get_contents("send_emails3");
        $f = unserialize($f);

        $emails = $db->AllRecords(
                "SELECT distinct data 
                FROM client_contacts co ,`clients` c  
                WHERE 
                        client_id = c.id 
                    and co.type='email' 
                    and is_active 
                    and status ='work'
                    /* and manager != 'Vavilova' */");

        //$emails = array(array("data" => "dga@mcn.ru"));

        //unset($f["my_inbox2006@mail.ru"], $f["shadow_d_@mail.ru"], $f["neomatrixer@mail.ru"], $f["my_inbox2007@mail.ru"]);

        /*
        $emails = array(
                array("data" => "my_inbox2007@mail.ru"),
                array("data" => "my_inbox2006@mail.ru"),
                array("data" => "shadow_d_@mail.ru"),
                array("data" => "dga@mcn.ru"),
                array("data" => "neomatrixer@mail.ru")
                );
                */

		$Mail = new PHPMailer();
		$Mail->SetLanguage("ru","include/");
		$Mail->CharSet = "utf-8";
		$Mail->From = "info@mcn.ru";
		$Mail->FromName="МСН Телеком";
		$Mail->Mailer='smtp';
		$Mail->Host=SMTP_SERVER;
		foreach($emails as $adr1)

{

    echo "!";
    $a = array();
    if(
            ($adr1["data"] && (!isset($f[$adr1["data"]])))
      )
    {
        $a[] = $adr1;
        $rr =rand(10,50); 
        echo "*".$rr."*";
        if($rr == 20){
            //$a[] = array("data" => "dga@mcn.ru");
            //print_r($a);
        }
    }

    foreach($a as $adr)
    {
        $adr["data"] = trim($adr["data"]);
        if(!$adr["data"]) continue;
        if(isset($f[$adr["data"]]) && $adr["data"] != "dga@mcn.ru") continue;


        if($adr["data"])
            $Mail->AddAddress($adr["data"]);

        echo "\n".$adr["data"];

        $Mail->ContentType='text/html';
        $Mail->Subject = "Компания  \"МСН Телеком\" поздравляет Вас С Новым Годом!";
        $Mail->IsHTML(true);
        $Mail->Body = "
            <!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
            <html>
            <head>
            <meta content=\"text/html; charset=UTF-8\" http-equiv=\"Content-Type\">
            </head>
            <body text=\"#000000\" bgcolor=\"#ffffff\">
            <img alt=\"С Новый Годом!\" src=\"cid:part1.06010907.08000703@mcn.ru\">
            </body>
            </html>
            ";
        //$Mail->AddEmbeddedImage("mcn-2011.jpg", "part1.06010907.08000702@mcn.ru", "mcn-2011.jpg", "base64", "image/jpeg");
        //$Mail->AddEmbeddedImage("blue.png", "part1.06010907.08000702@mcn.ru", "mcn-2012-new-year.png", "base64", "image/png");
        $Mail->AddEmbeddedImage("NY_mail.png", "part1.06010907.08000703@mcn.ru", "mcn-2013-new-year.png", "base64", "image/png");

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

        file_put_contents("send_emails3", serialize($f));

        sleep(1);
    }
}

