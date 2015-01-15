<?php


class lineDB
{
    public static function insert($d)
    {
        global $db_ats;

        /*
        (
         [account] => 99000001
         [host_type] => dynamic
         [host_static] => 
         [host_port_static] => 5060
         [password] => 679qotqraaju
         [dtmf] => rfc2833
         [insecure] => 
         [permit_on] => no
         [permit] => 
         [codec] => alaw,g729
         [context] => c-realtime-out
         [serial] => 1
         [sequence] => 0
         [is_group] => 1
         [client_id] => 9130
        )
        */

        unset($d["subaccount_count"]);

        $line = array();
        foreach(array("parent_id", "account", "serial", "sequence", "is_group", "client_id", "priority", "format") as $l)
        {
            if (isset($d[$l]))
            {
                $line[$l] = $d[$l];
            }
            unset($d[$l]);
        }

        $lineId = $db_ats->QueryInsert("a_line", $line, true);

        $cId = $db_ats->QueryInsert("a_connect", $d, true);

        $db_ats->QueryUpdate("a_line", "id", array("id" => $lineId, "c_id" => $cId));
           
        reservAccount::reset();

        return $lineId;
    }

    public static function update(&$d)
    {
        global $db_ats;

        foreach(array("parent_id", "account", "serial", "sequence", "is_group", "client_id", "c_id") as $l)
            unset($d[$l]);

        if(count($d) <= 1) return; // если есть только id, то ничего не делаем

        $d["id"] = self::getConnectId($d["id"]);

        $db_ats->QueryUpdate("a_connect", "id", $d);
    }

    public static function getConnectId($lineId)
    {
        global $db_ats;

        return $db_ats->GetValue("select c_id from a_line where id = '".$lineId."' and ".sqlClient());
    }

    public static function bulk_del($numbers)
    {
	global $db_ats;
	foreach ($numbers as $v)
	{
		$links = $db_ats->AllRecordsAssoc('SELECT c_id FROM a_link WHERE number_id = ' . $v, 'c_id', 'c_id');
		foreach ($links as $id)
		{
			self::del($id);
		}
	}
    }
    public static function del($id)
    {
        global $db_ats;

        if($id && $r = account::get($id))
        {
            if($r["is_group"])
            {
                foreach($db_ats->AllRecords("select id, c_id from a_line where parent_id = '".$r["id"]."'") as $l)
                    self::_del($l);
            }

            self::_del($r);
            ats2sync::updateClient($r["client_id"]);
        }
    }

    private static function _del($l)
    {
        global $db_ats;

        $db_ats->QueryDelete("a_line", array("id" => $l["id"], "c_id" => $l["c_id"]));
        $db_ats->QueryDelete("a_connect", array("id" => $l["c_id"]));

        $db_ats->QueryDelete("a_link", array("c_id" => $l["id"], "c_type" => "line"));
        $db_ats->QueryDelete("a_link", array("c_id" => $l["id"], "c_type" => "trunk"));

        $db_ats->QueryDelete("a_virtpbx_link", array("type_id" => $l["id"], "type" => "account"));
    }
}
