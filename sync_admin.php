<?php

echo date("r")."\n";
	define('NO_WEB',1);
	define('NUM',20);
	define('PATH_TO_ROOT','./');
    define('DEBUG_LEVEL', 0);
    require_once(PATH_TO_ROOT.'include/runChecker.php');
    if(runChecker::isRun())
        die("already run\n");
    require_once('conf.php');


    runChecker::run();

    //$db->Query("use nispd");

    $count = 0;
    while($count++ < 34)
    {
	    foreach($db->AllRecords("select bill_no, event,comment from z_sync_admin") as $s)
		{
		    echo "\n".$s["bill_no"];

		    if(preg_match("/^\d{6,7}$/", $s["bill_no"]))
		    {
                    $s["comment"] = iconv("koi8r","cp1251", $s["comment"]);

                if($s["event"] == "create")
                {
    			    $v = file_get_contents($q = "http://admin.marcomnet.ru/admin/_scripts/stat.html?user=stat&password=df9c03be7c65f8558aa56fc5b13dd075861e7800&order_id=".urlencode($s["bill_no"])."&action=create_order&comment=".urlencode($s["comment"]));
                }elseif($s["event"] == "to_admin")
                {
    			    $v = file_get_contents($q = "http://admin.marcomnet.ru/admin/_scripts/stat.html?user=stat&password=df9c03be7c65f8558aa56fc5b13dd075861e7800&order_id=".urlencode($s["bill_no"])."&action=set_active&comment=".urlencode($s["comment"]));
                }
			    echo $q." => ".$v;

                if($v == "1")
			    	echo " ok";

                $db->Query("delete from z_sync_admin where bill_no = '".$s["bill_no"]."'");
		    }else{
		    	$db->Query("delete from z_sync_admin where bill_no = '".$s["bill_no"]."'");
		    	echo " drop";
		    }
		}
		sleep(5);
    }

runChecker::stop();
