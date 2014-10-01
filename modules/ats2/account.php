<?php


class account
{
    public function get($id = 0, $clientId = null)
    {
        global $db_ats;
            
        if($id == 0)
        {
            $newAcc = freeAccount::get();
            return array(
                    "id" => "0",
                    "is_group" => "1",
                    "subaccount_count" => 0,
                    "account" => $newAcc["account"],
                    "serial" => $newAcc["serial"],
                    "password" => "********",
                    "parent_id" => "0",
                    "sequence" => "0",
                    "permit" => "",
                    "codec" => "alaw,g729",
                    "context" => "c-realtime-out",
                    "last_update" => "0000-00-00 00:00:00",
                    "enabled" => "yes",
                    "permit_on" => "auto",
                    "host_type" => "dynamic",
                    "host_static" => "",
                    "host_port_static" => "5060",
                    "dtmf" => "rfc2833",
                    "insecure" => ""
                        );
        }

        if(!$id) return false;

        $r = $db_ats->GetRow("select * from a_line l, a_connect c where l.c_id = c.id and l.id = ".$id);
        if($r)
        {
            $r["id"] = $id;

            unset($r["priority"]);

            if($r["is_group"])
                $r["subaccount_count"] = (int)$db_ats->GetValue("select count(1) from a_line where parent_id = '".$r["id"]."'");

            $r["account"] = account::make($r);
        }

        return $r;
    }

    public function make(&$l)
    {
        return 
            sprintf("%06d", $l["serial"]).
            (!isset($l["is_group"]) ||  $l["is_group"] ? "" : sprintf("%02d", $l["sequence"]));
    }

    public function parse($acc)
    {
        $acc = trim($acc);

        if(strlen($acc) == 6 || strlen($acc) == 8)
        {
            if(preg_match("/^([0-9]{6})([0-9]{2})?/", $acc, $o))
            {
                $r = array(
                        "serial" => (int)$o[1],
                        "sequence" => isset($o[2]) ? $o[2] : 0
                        );

                return $r;

            }else{
                throw new Exception("Ошибка добавления аккаунта");
            }
        }else{
            throw new Exception("Ошибка добавления аккаунта");
        }
    }

    public function getSubaccounts($id)
    {
        global $db_ats;

        $dd = array();
        foreach($db_ats->AllRecords("select id from a_line where parent_id = '".$id."' order by account") as $l)
            $dd[] = self::get($l["id"]);

        return $dd;

    }

    public function getMaxSequence(&$d)
    {
        global $db_ats;

        $r = $db_ats->GetValue(
                "select max(sequence) 
                from a_line
                where 
                        serial='".$d["serial"]."' 
                    and is_group = 0");

        if(!$r) return 0;

        return $r;
    }

    public function change_subaccount(&$d, $to)
    {
        $from = count(self::getSubaccounts($d["id"]));
        if($from >= $to)
        {
            $d["subaccount_count"] = $from;
            return;
        }

        $sequence = account::getMaxSequence($d);

        $d1 = $d;

        unset($d1["subaccount_count"], $d1["id"], $d1["c_id"]);
        $d1["is_group"] = 0;
        $d1["parent_id"] = $d["id"];

        $addedIds = array();

        $addLines = $to-$from;
        $addLines = $addLines > 110 ? 110 : $addLines;

        for($i = 0; $i< $addLines; $i++)
        {
            $d1["sequence"] = $sequence+1+$i;
            $d1["account"] = account::make($d1);
            $d1["password"] = password_gen();

            $addedIds[] = lineDB::insert($d1);
        }

        return $addedIds;
    }
}
