<?php


class reservAccount
{
    private static function clear()
    {
        global $db_ats;

        $db_ats->Query("delete from a_reserv_account where (date+interval 5 minute) < now()");
    }

    public static function get()
    {
        global $db_ats;

        self::clear();

        $sessionId = session_id();

        $v = $db_ats->GetRow("select serial from a_reserv_account where session = '".$sessionId."'");
        if($v)
        {
            $v["account"] = account::make($v);
        }

        return $v;
    }

    public static function set($l)
    {
        global $db_ats;

        $db_ats->QueryInsert("a_reserv_account", array(
                    "serial" => $l["serial"],
                    "session" => session_id()
                    )
                );
    }

    public static function reset() 
    {
        global $db_ats;

        $db_ats->QueryDelete("a_reserv_account", array(
                    "session" => session_id()
                    )
                );
    }

    public static function isReservedOther($serial)
    {
        global $db_ats;

        return $db_ats->GetValue(
                "select serial 
                from a_reserv_account 
                where 
                        serial ='".$serial."' 
                    and session != '".session_id()."'");
    }
}
