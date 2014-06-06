<?php

	define('NO_WEB',1);
	define('NUM',35);
	define('PATH_TO_ROOT','../');
	include PATH_TO_ROOT."conf.php";


		include INCLUDE_PATH."class.phpmailer.php";
		include INCLUDE_PATH."class.smtp.php";

        $f = file_get_contents("send_emails");
        $f = unserialize($f);

        $emails = $db->AllRecords(
                "SELECT distinct data 
                FROM client_contacts co ,`clients` c  
                WHERE 
                        client_id = c.id 
                    and co.type='email' 
                    and is_active 
                    and status ='work'
                    and client_id in (
                       SELECT distinct client_id FROM `newpayments` WHERE `type` = 'ecash'
                    )
                    /* and manager != 'Vavilova' */");

        //$emails = array(array("data" => "adima123@yandex.ru"));
        echo count($emails);
        //exit();


		$Mail = new PHPMailer();
		$Mail->SetLanguage("ru","include/");
		$Mail->CharSet = "utf-8";
		$Mail->From = "info@mcn.ru";
		$Mail->FromName="МСН Телеком";
		$Mail->Mailer='smtp';
		$Mail->Host=SMTP_SERVER;

        print_r($Mail);
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
        $Mail->Subject = "Моментальная оплата услуг связи МСН Телеком Яндекс.Деньгами";
        $Mail->IsHTML(true);
        $Mail->Body = "
            <!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
            <html>
            <head>
            <meta content=\"text/html; charset=UTF-8\" http-equiv=\"Content-Type\">
            </head>
            <body text=\"#000000\" bgcolor=\"#ffffff\">

            <p>Оператор МСН Телеком рад сообщить Вам о том, что теперь услуги связи можно оплачивать <b>Яндекс.Деньгами через Личный кабинет на сайте mcn.ru</b>.</p>
            <p>Обращаем Ваше внимание, что в этом случае <b><u>зачисление средств на Ваш Лицевой счет</u> будет производиться моментально</b>.</p>
            <p><u>Как оплатить:</u>
            <br>1. В Личном кабинете на нашем сайте на вкладке \"Счета и платежи\" в меню слева выберите пункт \"Пополнить лицевой счет\" и введите необходимую сумму платежа.
            <br>2. Нажмите на иконку \"Оплата Яндекс.Деньгами\" и вы перейдете на страницу оплаты в сервисе \"Яндекс.Деньги\".</p>
            <p><i>Подробная инструкция: <a href=\"http://telephony.mcn.ru/payment/\">http://telephony.mcn.ru/payment/</a></i></p>

            <p>Яндекс.Деньги - это сервис онлайн-платежей, который работает 24 часа в сутки и 7 дней в неделю. Пользоваться Яндекс.Деньгами можно сразу после создания электронного кошелька.</p>

            </body>
            </html>
            ";
        //$Mail->AddEmbeddedImage("mcn-2011.jpg", "part1.06010907.08000702@mcn.ru", "mcn-2011.jpg", "base64", "image/jpeg");
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

