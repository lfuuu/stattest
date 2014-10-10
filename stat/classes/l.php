<?php


class l
{
    static public function ll($c,$f,$p1=null, $p2=null, $p3=null)
    {
        static $isStarted = false;

            $l = "<br><b>".$c."::".$f."</b>(<i>".($p1 === null ? "" : var_export($p1,true)).($p2 === null ? "" : ",".var_export($p2,true)).($p3 === null ? "" : ",".var_export($p3,true))."</i>)";

        if(defined("voip_debug"))
            echo "\n".$l;

        if($fp = fopen("/var/log/nispd/voipcheck.log", "a+"))
        {
            fwrite($fp, "\n".date("r").": ".$l);
            fclose($fp);
        }
    }
}
