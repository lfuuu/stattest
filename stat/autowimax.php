<?php
// this line has to be modified for each server
	define('NO_WEB',1);
	define('PATH_TO_ROOT','./');
	include PATH_TO_ROOT."conf_yii.php";
	
	set_time_limit(0);
	require_once(INCLUDE_PATH.'mysmarty.php');
	@include_once (MODULES_PATH."/stats/module.php");
		include INCLUDE_PATH."class.phpmailer.php";
		include INCLUDE_PATH."class.smtp.php";

	$design = new MySmarty();
	$module_stats = new m_stats();

    list($date, $r) = $module_stats->stats_report_wimax(null, true);


		$Mail = new PHPMailer();
		$Mail->SetLanguage("ru","include/");
		$Mail->CharSet = "utf-8";
		$Mail->From = "info@mcn.ru";
		$Mail->FromName="MCN";
		$Mail->Mailer='smtp';
		$Mail->Host=SMTP_SERVER;
		$Mail->AddAddress("dga@mcn.ru");
		$Mail->ContentType='text/html';
		$Mail->Subject = "Отчет по WiMax от MCN ".$date;
		$Mail->Body = $r;

		echo "\n".date("r").": ".($Mail->Send() ? "ok" : "error");
