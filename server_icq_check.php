<?php

	define('NO_WEB',1);
	define('PATH_TO_ROOT','./');
	include PATH_TO_ROOT."conf.php";
    include INCLUDE_PATH."runChecker.php";

    if(runChecker::isRun())
        die(".");

    echo "\n".date("r").": ";

    runChecker::run();

    $inQueue = $db->GetValue("select count(*) from tt_send");
    $prev = $db->GetValue("select count from tt_send_count");

    echo $inQueue."/".$prev;

    if($inQueue > 0)
{
    if($prev != $inQueue)
        $db->Query("update tt_send_count set count = '".$inQueue."'");
    else
        _kill();
}

function _kill()
{
    echo " kill";
    exec("ps ax | grep server_icq.php | grep -v sh | grep -v grep | awk '{ print $1 }'", $o);
    if($o)
    {
        exec("kill -9 ".min($o));
    }else{
        echo " !!! nofing";
    }
}



    runChecker::stop();


