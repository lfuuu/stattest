<?php
	define("PATH_TO_ROOT",'../');
	include PATH_TO_ROOT."conf.php";
	if (sendBadStat())
	{
		echo "\n" . Encoding::toUtf8("Файл был отправлен\n\n");
	}
	function sendBadStat() 
	{
		global $design;
		$from = strtotime('first day of this month 00:00:00');
		$to = strtotime('today  00:00:00');
		$data = VirtpbxStat::getBadStat($from, $to);
		if (empty($data))
		{
			echo "\n" . Encoding::toUtf8("Все данные были получены\n\n");
			return false;
		}
		$design->assign('data', $data);
		$message = $design->fetch('stats/virtpbx_bad_data_message.tpl');

		include_once INCLUDE_PATH."class.phpmailer.php";
		include_once INCLUDE_PATH."class.smtp.php";
		$Mail = new PHPMailer();
		$Mail->SetLanguage("en", INCLUDE_PATH);
		$Mail->CharSet = "utf-8";
		$Mail->IsHTML(true);
		$Mail->From = "info@mcn.ru";
		$Mail->FromName="МСН Телеком";
		$Mail->Mailer='mail';
		$Mail->Host=SMTP_SERVER;
		$Mail->AddAddress(ADMIN_EMAIL);
		$Mail->Body = Encoding::toUtf8($message);
		$subject = Encoding::toUtf8('Не полученные данные в период c ' . date("d-m-Y", $from) . ' по ' .  date("d-m-Y", $to));
		$Mail->Subject = $subject;
		return $Mail->Send();
	}
?>