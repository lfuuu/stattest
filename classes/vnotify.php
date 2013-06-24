<?php

class vNotify
{
    static $c = array();
    function anonse($n)
    {
        vNotifier::notify($n, false);

        self::$c[$n] = 1;
    }

    function send()
    {
        foreach(self::$c as $k => $v)
        {
            vNotifier::notify($k);
        }
    }
}

class vNotifyWatcher
{
    function __construct()
    {
        vNotify::anonse("start");
    }
    function __destruct()
    {
        vNotify::send();
    }
}

$_vNotifyWatcher = new vNotifyWatcher();

class vNotifier
{
    function notify($n, $r = true)
    {
        $fp = fopen("/tmp/notify".($r ? "" : "_r"), "a+");
        fwrite($fp, "\n".date("r").": ".$n);
        fclose($fp);
    }
}
