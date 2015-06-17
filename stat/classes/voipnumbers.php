<?php

class voipNumbersChecker
{
    public static function check()
    {
        l::ll(__CLASS__,__FUNCTION__);
        global $db;

        $actual = self::load("actual");

        if($diff = self::diff(self::load("number"), $actual))
            voipDiff::apply($diff);

        $db->SwitchDB(SQL_DB);
    }

    private static $sqlActual = "select client_id, e164, no_of_lines, no_of_callfwd from (
        SELECT 
            c.id as client_id,
            trim(e164) as e164,
            u.no_of_lines, 
            u.no_of_callfwd,
            (select block from log_block where id= (select max(id) from log_block where service='usage_voip' and id_service=u.id)) is_block
        FROM 
            usage_voip u, clients c
        WHERE 
            ((actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') and actual_to >= DATE_FORMAT(now(), '%Y-%m-%d')) or actual_from > '3000-01-01')
            and u.client = c.client and (c.status in ('work','connecting','testing') or c.client ='id9130')
            /*and c.voip_disabled=0 */ having is_block =0 or is_block is null order by u.id)a";

    private static $sqlNumber=
        "SELECT client_id, number as e164, call_count as no_of_lines, callfwd_count as no_of_callfwd
        FROM v_number WHERE enabled = 'yes'
        # and client='id9011'
        order by id";

    private static function load($type)
    {
        l::ll(__CLASS__,__FUNCTION__,$type);
        global $db;

        $sql = "";

        switch($type)
        {
            case 'actual': $sql = self::$sqlActual; $db->SwitchDB(SQL_DB); break;
            case 'number': $sql = self::$sqlNumber; $db->SwitchDB(SQL_ATS_DB); break;
            default: throw new Exception("Unknown type");
        }

        $d = array();
        if ($rows = $db->AllRecords($sql)) {
            foreach ($rows as $l)
                $d[$l["e164"]] = $l;
        }

        if (!$d)
            throw new Exception("Data not load");

        return $d;
    }

    private static function diff($saved, $actual)
    {
        l::ll(__CLASS__,__FUNCTION__,/*$saved, $actual,*/ "...","...");

        $d = array(
                "added" => array(), 
                "deleted" => array(), 
                "changed_lines" => array(), 
                "changed_callfwd" => array(),
                "new_client" => array()
                );

        foreach(array_diff(array_keys($saved), array_keys($actual)) as $l)
            $d["deleted"][$l] = $saved[$l];

        foreach(array_diff(array_keys($actual), array_keys($saved)) as $l)
            $d["added"][$l] = $actual[$l];

        foreach($actual as $e164 => $l)
            if(isset($saved[$e164]) && $saved[$e164]["no_of_lines"] != $l["no_of_lines"]) 
                $d["changed_lines"][$e164] = $l + array("no_of_lines_prev" => $saved[$e164]["no_of_lines"]);

        foreach($actual as $e164 => $l)
            if(isset($saved[$e164]) && $saved[$e164]["no_of_callfwd"] != $l["no_of_callfwd"])
                $d["changed_callfwd"][$e164] = $l + array("no_of_callfwd_prev" => $saved[$e164]["no_of_callfwd"]);

        foreach($actual as $e164 => $l)
            if(isset($saved[$e164]) && $saved[$e164]["client_id"] != $l["client_id"])
                $d["new_client"][$e164] = $l + array("client_id_prev" => $saved[$e164]["client_id"]);


        foreach($d as $k => $v)
            if($v)
                return $d;

        return false;
    }

    private static function save(&$actual)
    {
        l::ll(__CLASS__,__FUNCTION__,"..."/*, $actual*/);
        global $db;

        $db->SwitchDB(SQL_DB);
        $db->Begin();
        $db->Query("truncate ".SQL_ATS_DB.".v_usage_save");
        $db->Query("insert into ".SQL_ATS_DB.".v_usage_save ".self::$sqlActual);
        $db->Commit();
    }
}

class voipNumbers
{
    public static function check()
    {
        l::ll(__CLASS__,__FUNCTION__);
        voipNumbersChecker::check();

        //define("voip_debug", 1);
        //ats2Numbers::check();
    }

    public static function getNumberId($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db;

        static $c = array();
        $key = $l["client_id"]."--".$l["e164"];
        if(!isset($c[$key]))
        {
            $c[$key] = $db->getValue("select id from ".SQL_ATS_DB.".v_number 
                    where number = '".$l["e164"]."' 
                    and client_id='".$l["client_id"]."'");
        }

        return $c[$key];
    }
    
    public static function isUsed($l, $lookInDeleted = false)
    {
        l::ll(__CLASS__,__FUNCTION__,$l, $lookInDeleted);
        global $db;

        $numberId = self::getNumberId($l);

        if(!$numberId) return false;

        foreach($db->AllRecords("SELECT * FROM `v_sip` 
                    WHERE atype='number'
                    and client_id='".$l["client_id"]."'".
                    (!$lookInDeleted ? " and enabled='yes'":"")) as $n)
        {
            if($n["type"] == "multitrunk")
            {
                $numbersMt = numberMT::load($n["id"], true);
                if(isset($numbersMt[$numberId])) return $n;
            }else{
                if($n["number"] == $numberId) return $n;
            }
        }
        return false;
    }


    public static function isMarkedForDelet($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db;

        $numberId = self::getNumberId($l);

        return $db->GetValue("select enabled from v_number where id = ".$numberId) == "no";
    }

    public static function add($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db;

        return $db->QueryInsert("v_number", array(
                    "client_id" => $l["client_id"],
                    "number" => $l["e164"],
                    "call_count" => $l["no_of_lines"],
                    )
                );
    }

    public static function delet($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db;

        return $db->QueryDelete("v_number", array(
                    "client_id" => $l["client_id"],
                    "number" => $l["e164"]
                    )
                );
    }

    public static function unMarkDelet($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db;

        $numberId = self::getNumberId($l);

        $db->QueryUpdate("v_number", "id",
                array(
                    "id" => $numberId,
                    "enabled" => "yes",
                    )
                );

        // type != multitrunk
        if($n = $db->GetRow("select id, type from v_sip where number='".$numberId."' and atype='number'"))
        {
            voipNumbers::unMarkDeletSIP($n["id"]);
        // type == multitrunk
        }elseif($n = $db->GetRow("select sip_id from v_number_mt where number_id = '".$numberId."'")){
            voipNumbers::recoverInMT($numberId, $n["sip_id"]);
        }
    }


    public static function markDelete($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db;

        $numberId = self::getNumberId($l);

        $db->QueryUpdate("v_number", 
                array("id"), 
                array(
                    "id" => $numberId,
                    "enabled" => "no",
                    "disabled_date" => array("NOW()"),
                    )
                );

        if($n = voipNumbers::isUsed($l))
        {
            if($n["type"] == "multitrunk")
            {
                voipNumbers::archiveFromMT($l, $n["id"]);
            }else{
                voipNumbers::markDeletSIP($n["id"]);
            }
        }
    }

    public static function archiveFromMT($l, $id)
    {
        l::ll(__CLASS__,__FUNCTION__, $l, $id);
        global $db;

        $db->QueryUpdate("v_number_mt", 
                array("sip_id", "number_id"), 
                array(
                    "sip_id" => $id,
                    "number_id" => voipNumbers::getNumberId($l),
                    "enabled" => "no",
                    "disabled_date" => array("NOW()")
                    )
                );

        vSip::recalcCallCount($id);
    }

    public static function recoverInMT($numberId, $sipId)
    {
        l::ll(__CLASS__,__FUNCTION__, $numberId, $sipId);
        global $db;

        $db->QueryUpdate("v_number_mt", array("number_id"),
                array(
                    "number_id" => $numberId,
                    "enabled" => "no"
                    )
                );

        vSip::recalcCallCount($sipId);

    }

    public static function markDeletSIP($id)
    {
        l::ll(__CLASS__,__FUNCTION__, $id);
       global $db;

       $db->QueryUpdate("v_sip", "id", array(
                   "id" => $id,
                   "enabled" => "no"
                   )
               );

       $db->QueryUpdate("v_sip", "parent_id", array(
                   "parent_id" => $id,
                   "enabled" => "no"
                   )
               );
    }

    public static function unMarkDeletSIP($id)
    {
        l::ll(__CLASS__,__FUNCTION__, $id);
       global $db;

       $db->QueryUpdate("v_sip", "id", array(
                   "id" => $id,
                   "enabled" => "yes"
                   )
               );

       $db->QueryUpdate("v_sip", "parent_id", array(
                   "parent_id" => $id,
                   "enabled" => "yes"
                   )
               );
    }


    public static function delIfUsedOftherClient($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db;

        foreach($db->AllRecords(
                    "select client_id,number as e164, call_count 
                    from v_number 
                    where number = '".$l["e164"]."' and client_id != '".$l["client_id"]."'") as $n)
        {
            voipNumberAction::delFull($n);
        }
    }

}

class voipDiff
{
    public static function apply(&$diff)
    {
        global $db;
        l::ll(__CLASS__,__FUNCTION__,$diff);

        $db->SwitchDB(SQL_ATS_DB);

        if($diff["added"])
            self::add($diff["added"]);

        if($diff["deleted"])
            self::del($diff["deleted"]);

        if($diff["changed_lines"])
            self::numOfLineChanged($diff["changed_lines"]);

        if($diff["changed_callfwd"])
            self::numOfCallFwdChanged($diff["changed_callfwd"]);

        if($diff["new_client"])
            self::clientChanged($diff["new_client"]);
    }

    private static function add(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            voipNumberAction::add($l);
    }

    private static function del(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            voipNumberAction::del($l);
    }

    private static function numOfLineChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            voipNumberAction::numOfLineChanged($l);
    }

    private static function numOfCallFwdChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            voipNumberAction::numOfCallFwdChanged($l);
    }

    private static function clientChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $e164 => $l)
            voipNumberAction::clientChanged($l);
    }
}

class voipNumberAction
{
    public static function add(&$l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        if(voipNumbers::getNumberId($l)) // is in DB
        {
            if(voipNumbers::isMarkedForDelet($l))
            {
                voipNumbers::unMarkDelet($l);
            }
        }else{
            voipNumbers::add($l);
        }

        voipNumbers::delIfUsedOftherClient($l);
    }

    public static function del(&$l, $lookInDeleted = false)
    {
        l::ll(__CLASS__,__FUNCTION__, $l, var_export($lookInDeleted, true));

        if($n = voipNumbers::isUsed($l, $lookInDeleted))
        {
            l::ll(__CLASS__,__FUNCTION__."+", $n);
            voipNumbers::markDelete($l);
        }else{
            voipNumbers::delet($l);
        }
    }

    public static function delFull($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        if($n = voipNumbers::isUsed($l, true))
        {
            if($n["enabled"] == "yes")
            {
                voipNumbers::markDelete($l);
            }

            if($n["type"] != "multitrunk")
                vSip::del($n["id"]);
        }
        voipNumbers::delet($l);
    }

    public static function numOfLineChanged($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db;

        $db->QueryUpdate("v_number", "number", array(
                    "number" => $l["e164"],
                    "call_count" => $l["no_of_lines"]
                    )
                );

        if($n = voipNumbers::isUsed($l))
            vSip::recalcCallCount($n["id"]);
    }
    
    public static function numOfCallFwdChanged($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db;

        $db->QueryUpdate("v_number", "number", array(
                    "number" => $l["e164"],
                    "callfwd_count" => $l["no_of_callfwd"]
                    )
                );
    }

    public static function clientChanged($l)
    {
        global $db;

        $numberId = $db->getValue("select id from ".SQL_ATS_DB.".v_number 
                where number = '".$l["e164"]."'");

        foreach($db->AllRecords("select sip_id from v_number_mt where number_id = '".$numberId."'") as $s)
        {
            $db->QueryUpdate("v_sip", "id", array("id" => $s["sip_id"], "client_id" => $l["client_id"]));
        }

        $db->QueryUpdate("v_number", "id", array("id" => $numberId, "client_id" => $l["client_id"]));

    }
}
