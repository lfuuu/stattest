<?php
	define("PATH_TO_ROOT",'../../stat/');
	include PATH_TO_ROOT."conf_yii.php";

    $c = new CyberplatProcessor();
    $c->proccessRequest();



