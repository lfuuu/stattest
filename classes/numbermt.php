<?php

class numberMT
{
    public function load($id, $struct = false)
    {
        global $db;

        $s = "";
        $sa = array();
        foreach($db->QuerySelectAll("v_number_mt", array("sip_id" => $id, "enabled" => "yes")) as $l)
            if($struct)
            {
                $sa[$l["number_id"]] = $l["direction"];
            }else{
                $s .= ($s ? "," : "").$l["number_id"]."=".$l["direction"];
            }

        return $struct ? $sa : $s;
    }

    public function _parse($numbers)
    {
        $mt = array();
        if(!$numbers) return $mt;
        foreach(explode(",", $numbers) as $l)
        {
            list($numberId, $direction) = explode("=", $l."=");
            $mt[$numberId] = $direction;
        }
        return $mt;
    }

    public function save($id, $numbers)
    {
        global $db;

        $db->QueryDelete("v_number_mt", array("sip_id" => $id));

        foreach(self::_parse($numbers) as $numberId => $direction)
        {
            $db->QueryInsert("v_number_mt", array(
                        "sip_id" => $id,
                        "number_id" => $numberId,
                        "direction" => $direction
                        )
                    );
        }
    }

    public function getCallCount($s)
    {
        global $db;
        if($s)
            return $db->GetValue("select sum(call_count) from v_number where id in ('".implode("','",array_keys(self::_parse($s)))."')");

        return 0;
    }
}
