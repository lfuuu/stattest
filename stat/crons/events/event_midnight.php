<?php

define("PATH_TO_ROOT", "../../");
include PATH_TO_ROOT."conf.php";
include INCLUDE_PATH."runChecker.php";

echo "\n".date("r").":";


event::go("midnight");
