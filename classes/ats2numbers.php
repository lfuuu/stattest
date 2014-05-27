<?php

include_once(PATH_TO_ROOT."modules/ats2/account.php");
include_once(PATH_TO_ROOT."modules/ats2/freeaccount.php");
include_once(PATH_TO_ROOT."modules/ats2/linedb.php");
include_once(PATH_TO_ROOT."modules/ats2/reservaccount.php");

class ats2NumbersChecker
{
    public function check()
    {
        l::ll(__CLASS__,__FUNCTION__);

        $actual = self::load("actual");

        if($diff = self::diff(self::load("number"), $actual))
        {
            ats2Diff::apply($diff);
        }
    }

    private function sqlClient($client = null)
    {
        global $db;
        if($client == null)
        {
            $client = $_SESSION["clients_client"];
        }
        
        static $c = array();

        if(!isset($c[$client]))
            $c[$client] = $db->GetValue("select id from clients where client = '".mysql_escape_string($client)."'");

        return "client_id='".$c[$client]."'";
    }

    private static $sqlActual = "select client_id, e164, no_of_lines, region, allowed_direction as direction, is_virtual from (
        SELECT 
            c.id as client_id,
            trim(e164) as e164,
            u.no_of_lines, 
            u.region,
            (select block from log_block where id= (select max(id) from log_block where service='usage_voip' and id_service=u.id)) is_block,
            ifnull((select is_virtual from log_tarif lt, tarifs_voip tv where service = 'usage_voip' and id_service = u.id and id_tarif = tv.id order by lt.date_activation desc, lt.id desc limit 1), 0) as is_virtual,
            allowed_direction
        FROM 
            usage_voip u, clients c
        WHERE 
            (actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') and actual_to >= DATE_FORMAT(now(), '%Y-%m-%d'))
            and u.client = c.client and ((c.status in ('work','connecting','testing')) or c.id = 9130) and LENGTH(e164) >= 3
            /*and c.voip_disabled=0 */ having is_block =0 or is_block is null order by u.id)a";

    private static $sqlNumber=
        "SELECT client_id, number as e164, call_count as no_of_lines, region, direction, number_type
        FROM a_number WHERE enabled = 'yes'
        order by id";

    private function load($type)
    {
        l::ll(__CLASS__,__FUNCTION__,$type);
        global $db, $db_ats;

        $sql = "";

        switch($type)
        {
            case 'actual': $sql = self::$sqlActual; $_db = &$db;     break;
            case 'number': $sql = self::$sqlNumber; $_db = &$db_ats; break;
            default: throw new Exception("Unknown type");
        }

        $d = array();
        foreach($_db->AllRecords($sql) as $l)
            $d[$l["e164"]] = $l;

        return $d;
    }

    private function diff(&$saved, &$actual)
    {
        l::ll(__CLASS__,__FUNCTION__,/*$saved, $actual,*/ "...","...");

        $d = array(
                "added" => array(), 
                "deleted" => array(), 
                "changed_lines" => array(), 
                "new_client" => array(),
                "clients" => array(),
                "region" => array(),
                "direction" => array(),
                "number_type" => array()
                );

        foreach(array_diff(array_keys($saved), array_keys($actual)) as $l)
            $d["deleted"][$l] = $saved[$l];

        foreach(array_diff(array_keys($actual), array_keys($saved)) as $l)
            $d["added"][$l] = $actual[$l];

        foreach($actual as $e164 => $l)
            if(isset($saved[$e164]) && $saved[$e164]["no_of_lines"] != $l["no_of_lines"]) 
                $d["changed_lines"][$e164] = $l + array("no_of_lines_prev" => $saved[$e164]["no_of_lines"]);

        foreach($actual as $e164 => $l)
            if(isset($saved[$e164]) && $saved[$e164]["client_id"] != $l["client_id"])
                $d["new_client"][$e164] = $l + array("client_id_prev" => $saved[$e164]["client_id"]);

        foreach($actual as $e164 => $l)
            if(isset($saved[$e164]) && $saved[$e164]["region"] != $l["region"]) 
                $d["region"][$e164] = $l + array("region_prev" => $saved[$e164]["region"]);

        foreach($actual as $e164 => $l)
            if(isset($saved[$e164]) && $saved[$e164]["direction"] != $l["direction"]) 
                $d["direction"][$e164] = $l + array("direction_prev" => $saved[$e164]["direction"]);

        foreach ($actual as $e164 => $l) {
            if (isset($saved[$e164])) {

                $numberType = (strlen($e164) < 5 ? "no" : ($l["is_virtual"] ? "v" : ""))."number";

                if ($saved[$e164]["number_type"] != $numberType) {
                    $d["number_type"][$e164] = $l + array("number_type" => $numberType, "number_type_prev" => $saved[$e164]["number_type"]);
                }
            }
        }

        //collect clients
        foreach($d as $k => $v)
        {
            if($k == "clients") continue;
            foreach($v as $l)
            {
                $d["clients"][$l["client_id"]] = $l["client_id"];

                if($k == "new_client")
                    $d["clients"][$l["client_id_prev"]] = $l["client_id_prev"];

            }
        }



        foreach($d as $k => $v)
            if($v)
                return $d;

        return false;
    }

    private function save(&$actual)
    {
        l::ll(__CLASS__,__FUNCTION__,"..."/*, $actual*/);
        global $db_ats;

        $db_ats->Begin();
        $db_ats->Query("truncate v_usage_save");
        $db_ats->Query("insert into v_usage_save ".self::$sqlActual);
        $db_ats->Commit();
    }
}

class ats2Numbers
{
    public function check()
    {
        l::ll(__CLASS__,__FUNCTION__);
        ats2NumbersChecker::check();
    }

    public function autocreateAccounts($usageId, $isTrunk)
    {
        ats2Helper::autocreateAccounts($usageId, $isTrunk);
    }

    public function getNumberId($l, $clientId = null, $isFromCache = true)
    {
        l::ll(__CLASS__,__FUNCTION__, $l, $clientId);
        global $db_ats;

        if ($clientId !== null)
        {
            $number = $l;
        } else {
            $clientId = $l["client_id"];
            $number = $l["e164"];
        }

        static $c = array();
        $key = $clientId."--".$number;
        if(!$isFromCache || !isset($c[$key]))
        {
            $c[$key] = $db_ats->getValue("select id from a_number 
                    where number = '".$number."' 
                    and client_id='".$clientId."'");
        }

        return $c[$key];
    }

    public function getNumberById($clientId, $numberId, $isFull = false)
    {
        global $db_ats;

        $r = $db_ats->GetRow("select ".($isFull ? "*" : "number")." from a_number where id = '".$numberId."' and client_id='".$clientId."'");

        return $isFull ? $r : $r["number"];
    }

    public function add($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;

        return $db_ats->QueryInsert("a_number", array(
                    "client_id"     => $l["client_id"],
                    "number"        => $l["e164"],
                    "call_count"    => $l["no_of_lines"],
                    "region"        => $l["region"]
                    )
                );
    }

    public function switchEnabled($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        $numberId = self::getNumberId($l);
        if($numberId)
            self::_db_switchEnabled($numberId, true);
    }

    public function switchDisabled($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        $numberId = self::getNumberId($l);
        if($numberId)
            self::_db_switchEnabled($numberId, false);
    }

    private function _db_switchEnabled($numberId, $isEnabled)
    {
        l::ll(__CLASS__,__FUNCTION__, $numberId, $isEnabled);
        global $db_ats;

        $db_ats->QueryUpdate("a_number", "id", array(
                    "id" => $numberId, 
                    "enabled" => $isEnabled ? "yes" : "no",
                    "last_update" => array("NOW()")
                    )
                );
    }

    public function isNumberEnabled($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;

        $r= $db_ats->GetValue($q = "select enabled from a_number where client_id = '".$l["client_id"]."' and number='".$l["e164"]."'") == "yes";

        return $r;
    }

    public function isUsed($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        $numberId = self::getNumberId($l);

        return (bool)self::getLinkCount($numberId);
    }

    public function getLinkCount($numberId)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;
        return $db_ats->GetValue("select count(*) from a_link where number_id = '".$numberId."'");
    }

    public function getGroupLinkId($numberId)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;

        $firstLineId = $db_ats->GetValue("select c_id from a_link where number_id = '".$numberId."' and c_type in ('line', 'trunk')");

        return $db_ats->GetValue("select parent_id from a_line where id = '".$firstLineId."'");
    }

    public function getLastGroupId($clientId)
    {
        l::ll(__CLASS__,__FUNCTION__, $clientId);
        global $db_ats;

        return $db_ats->GetValue("select id from a_line where is_group=1 and client_id = '".$clientId."' order by id desc limit 1");
    }


    public function delFull($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;

        $numberId = self::getNumberId($l);

        $db_ats->QueryDelete("a_link", array("number_id" => $numberId));
        $db_ats->QueryDelete("a_number", array("id" => $numberId));
    }
}

class ats2Diff
{
    public function apply(&$diff)
    {
        l::ll(__CLASS__,__FUNCTION__,$diff);


        if($diff["added"])
            self::add($diff["added"]);

        if($diff["deleted"])
            self::del($diff["deleted"]);

        if($diff["changed_lines"])
            self::numOfLineChanged($diff["changed_lines"]);

        if($diff["region"])
            self::regionChanged($diff["region"]);

        if($diff["direction"])
            self::directionChanged($diff["direction"]);

        if($diff["number_type"])
            self::numberTypeChanged($diff["number_type"]);

        if($diff["new_client"])
            self::clientChanged($diff["new_client"]);

        if($diff["clients"])
            self::updateClients($diff["clients"]);
    }

    private function add(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            ats2NumberAction::add($l);
    }

    private function del(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            ats2NumberAction::del($l);
    }

    private function numOfLineChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            ats2NumberAction::numOfLineChanged($l);
    }

    private function regionChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            ats2NumberAction::regionChanged($l);
    }

    private function directionChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            ats2NumberAction::directionChanged($l);
    }

    private function numberTypeChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            ats2NumberAction::numberTypeChanged($l);
    }

    private function clientChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            ats2NumberAction::clientChanged($l);
    }

    private function updateClients(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $clientId)
            ats2sync::updateClient($clientId);

    }

}

class ats2NumberAction
{
    public function add(&$l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);


        if(ats2Numbers::getNumberId($l))
        { 
            if(ats2Numbers::isNumberEnabled($l))
            {
                // error
            }else{
                ats2Numbers::switchEnabled($l);
            }

            //ats2Numbers::updateCallCountInMT
        }else{
            //посмотрит, если этот номер у когото другого
            global $db_ats;
            if($db_ats->GetValue("select client_id from a_number where number = '".$l["e164"]."'"))
            {
                ats2NumberAction::clientChanged($l);
            }else{
                ats2Numbers::add($l);
            }
        }
    }

    public function del(&$l, $lookInDeleted = false)
    {
        l::ll(__CLASS__,__FUNCTION__, $l, var_export($lookInDeleted, true));

        if( ats2Numbers::isNumberEnabled($l))
        {
            ats2Numbers::switchDisabled($l);
            //ats2Numbers::updateCallCountInMT
        }
    }

    public function delFull(&$l)
    {
        if(!isUsed($l))
            delFull($l);
    }

    public function numOfLineChanged($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db_ats;

        $db_ats->QueryUpdate("a_number", "number", array(
                    "number" => $l["e164"],
                    "call_count" => $l["no_of_lines"]
                    )
                );

        /*
        if($n = ats2Numbers::isUsed($l))
            vSip::recalcCallCount($n["id"]);
            */
    }
    
    public function regionChanged($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db_ats;

        $db_ats->QueryUpdate("a_number", "number", array(
                    "number" => $l["e164"],
                    "region" => $l["region"]
                    )
                );
    }

    public function directionChanged($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db_ats;

        $db_ats->QueryUpdate("a_number", "number", array(
                    "number" => $l["e164"],
                    "direction" => $l["direction"]
                    )
                );
    }

    public function numberTypeChanged($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db_ats;

        $db_ats->QueryUpdate("a_number", "number", array(
                    "number" => $l["e164"],
                    "number_type" => $l["number_type"]
                    )
                );
    }

    public function clientChanged($l)
    {
        global $db_ats;

        $numberId = $db_ats->GetValue("select id from a_number 
                where number = '".$l["e164"]."'");

        if($numberId)
        {
            $db_ats->QueryDelete("a_link", array("number_id" => $numberId));
            $db_ats->QueryUpdate("a_number", "id", array(
                        "id" => $numberId, 
                        "client_id" => $l["client_id"], 
                        "last_update" => array("NOW()"),
                        "enabled" => "yes")
                    );
        }

    }
}

class ats2Helper
{
    public function autocreateAccounts($usageId, $isTrunk, $isSync = false)
    {
        global $db, $db_ats;

        $usage = $db->GetRow("select * from usage_voip where id = '".$usageId."'");
        if (!$usage) {
            throw new Exception("Usage voip с id=".$usageId." не найден!");
        }

        $clientId = $db->GetValue("select id from clients where client = '".mysql_escape_string($usage["client"])."'");

        if (!$clientId) {
            throw new Exception("Клиент не найден");
        }

        $usage["client_id"] = $clientId;

        // extract numberId
        $count = 0;
        do { 
            if ($count > 0) {
                sleep(1);
            }

            $numberId = ats2Numbers::getNumberId($usage["E164"], $clientId, false);
        } while($count++ < 10 && !$numberId); // expect to create number

        if (!$numberId) {
            throw new Exception("Номер не найден");
        }

        $vpbxId = $db_ats->GetValue("select virtpbx_id from a_virtpbx_link where type='number' and type_id = '".$numberId."'");

        if ($vpbxId)
            throw new Exception("Номер заведен на vpbx");


        $currentCountAccounts = ats2Numbers::getLinkCount($numberId);

        $needLines = $isTrunk ? 1 : $usage["no_of_lines"];

        //exract group line id
        if ($currentCountAccounts)
        {
            $lineId = ats2Numbers::getGroupLinkId($numberId); //получаем группу по номеру
        } else {
            $lineId = ats2Numbers::getLastGroupId($clientId); //последная группа у клиента

            if (!$lineId)
            {
                // create group account
                $line = account::get();
                $line["client_id"] = $clientId;
                $lineId = lineDB::insert($line);
            }

            $currentCountAccounts = 0;
        }

        if (!$lineId) {
            throw new Exception("LineId не установлен");
        }

        //is need add accounts
        if ($currentCountAccounts < $needLines)
        {
            $line = account::get($lineId);

            if (!$line) {
                throw new Exception("Акакунт не найден");
            }

            $from = count(account::getSubaccounts($line["id"]));

            $plusLines = $from+($needLines-$currentCountAccounts);
            $plusLines = $plusLines > 110 ? 110 : $plusLines;

            foreach(account::change_subaccount($line, $plusLines) as $lineId) //всегда для привязки создаем новые подключения
            {
                $db_ats->QueryInsert("a_link", array(
                            "c_type" => "line",
                            "c_id" => $lineId,
                            "number_id" => $numberId
                            )
                        );
            }

            if ($isSync)
                ats2sync::updateClient($clientId);
        }
    }
}


