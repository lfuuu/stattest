<?php


define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);

define("voip_debug", 1);
define("print_sql", 1);


//$_SERVER['SERVER_NAME'] = "89.235.136.20";

include PATH_TO_ROOT."conf.php";

define("log_path", "../../");


echo "
".date("r").":";
echo "
";





virtPbx::check();







