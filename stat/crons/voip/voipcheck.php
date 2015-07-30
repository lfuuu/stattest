<?php


define("PATH_TO_ROOT", "../../");
define("NO_WEB", 1);

define("voip_debug", 1);
define("log_path", "./");

include PATH_TO_ROOT."conf_yii.php";

echo "
".date("r").":";
echo "
";




voipNumbers::check();







