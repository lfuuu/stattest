<?php


define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);

define("voip_debug", 1);
define("print_sql", 1);
define("log_path", "./");

include PATH_TO_ROOT."conf.php";

echo "
".date("r").":";
echo "
";




voipNumbers::check();







