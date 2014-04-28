<?php
define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);

define("voip_debug", 1);
//define("print_sql", 1);
define("exception_sql", 1);


include PATH_TO_ROOT."conf.php";
include INCLUDE_PATH."runChecker.php";


if(runChecker::isRun())
	die(date("r").": locked...");

runChecker::run();



for($i = 0; $i <= 55; $i++)
{
	echo "\n".date("r");
	$all = $db_ats->AllRecords("select client_id from a_update_client");
	foreach($all as $r)
	{
		exec("/usr/bin/php converter.php ".$r["client_id"]." >> ".LOG_DIR."voip_converter.log", $o);
		$db_ats->QueryDelete("a_update_client", array("client_id" => $r["client_id"]));
	}
	if($i < 55) sleep(1);
}

runChecker::stop();

