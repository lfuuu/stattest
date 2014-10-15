<?php

	define('NO_WEB',1);
	define('PATH_TO_ROOT','../../');
	include PATH_TO_ROOT."conf_yii.php";
    include INCLUDE_PATH."runChecker.php";
    include INCLUDE_PATH.'icq/WebIcqPro.class.php';


    $lifeH = rand(3,12);


    if(runChecker::isRun())
        die(".");
    echo "\n".date("r").": start";

    runChecker::run();

    $icq = new WebIcqPro();
    $count = 0;
    $countSend = 0;
    if($icq->connect("415601006", 'iddqd111'))
    {
        while(true)
        {
            echo ".";
            if(!$icq->isConnected())
            {
                exit();
            }

            if($r = $db->GetRow("select * from tt_send limit 1"))
            {
            echo "->";
                $uin = $db->GetValue("select icq from user_users where user = '".$r["user"]."'");

                if($uin && preg_match("/^[0-9]+$/", $uin))
                {
                    echo "\n".date("r").": sent - ".$r["user"]." ";


                    echo "\n".$r["text"];
                    if(!$icq->sendMessage($uin, iconv("utf-8", "cp1251//translit", strim($r["text"]))))
                    {
                        echo "error: ".$icq->error;
                    }else{
                        echo "OK";
                    }
                    $countSend++;
                }

                /*
                $r["text"] .= "\nsend to: ".$r["user"].($uin ? " (".$uin.")" : "");
                $uin = "343093320";
                if(!$icq->send_message($uin, iconv("utf-8", "cp1251//translit", $r["text"])))
                {
                    echo "\n".date("r").": error: ".$icq->error;
                }else{
                    echo "\n".date("r").": Message sent";
                }
                */


                $db->Query("delete from tt_send where id = '".$r["id"]."'");
            }
            sleep(5);
            if($count++ > $lifeH*60*60/5) {
                $icq->disconnect();
                exit(); // reconnect after 6 hours
            }
        }
    }else{
            echo "\n".date("r").":: ".$icq->error;
            $icq->disconnect();
            sleep(180);
    }

    runChecker::stop();

function strim($a)
{
    $maxLen = 400;

    if (strlen($a) > $maxLen)
    {
        $a = substr($a, 0, $maxLen-1);

        $rpos = strrpos($a, " ");

        $a = substr($a, 0, $rpos);

        $a = trim($a);

    }

    return $a;
}

