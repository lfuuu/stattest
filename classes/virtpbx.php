<?php

class virtPbxChecker
{
    public function check()
    {
        l::ll(__CLASS__,__FUNCTION__);

        $actual = self::load("actual");

        if($diff = self::diff(self::load("saved"), $actual))
            virtPbxDiff::apply($diff);
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

    private static $sqlActual = "
            SELECT
                c.id as client_id, tarif_id
            FROM
                usage_virtpbx u, clients c
            WHERE
                ((actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') AND actual_to >= DATE_FORMAT(now(), '%Y-%m-%d')) OR actual_from >= '2029-01-01')
                AND u.client = c.client 
                AND (   
                           c.status IN ('work','connecting','testing') 
                        OR c.client = 'id9130'
                        )
            GROUP BY u.client
            ORDER BY u.id";


    private static $sqlSaved=
        "SELECT client_id, tarif_id
        FROM a_virtpbx WHERE enabled = 'yes'
        order by id";

    private function load($type)
    {
        l::ll(__CLASS__,__FUNCTION__,$type);
        global $db, $db_ats;

        $sql = "";

        switch($type)
        {
            case 'actual': $sql = self::$sqlActual; $_db = &$db;     break;
            case 'saved':  $sql = self::$sqlSaved;  $_db = &$db_ats; break;
            default: throw new Exception("Unknown type");
        }

        $d = array();
        foreach($_db->AllRecords($sql) as $l)
            $d[$l["client_id"]] = $l;

        return $d;
    }

    private function diff(&$saved, &$actual)
    {
        l::ll(__CLASS__,__FUNCTION__,/*$saved, $actual,*/ "...","...");

        $d = array(
                "added" => array(), 
                "deleted" => array(), 
                "changed_tarif" => array(), 
                );

        foreach(array_diff(array_keys($saved), array_keys($actual)) as $l)
            $d["deleted"][$l] = $saved[$l];

        foreach(array_diff(array_keys($actual), array_keys($saved)) as $l)
            $d["added"][$l] = $actual[$l];

        foreach($actual as $clientId => $l)
            if(isset($saved[$clientId]) && $saved[$clientId]["tarif_id"] != $l["tarif_id"]) 
                $d["changed_tarif"][$clientId] = $l + array("prev_tarif_id" => $saved[$clientId]["tarif_id"]);


        foreach($d as $k => $v)
            if($v)
                return $d;

        return false;
    }
}

class virtPbxStatus
{
    private $status = null;
    private $isStarted = null;

    public function __construct($status, $isStarted)
    {
        $this->status = $status;
        $this->isStarted = $isStarted;
    }

    public function isInDB()
    {
        return $this->status != "notfound";
    }

    public function isEnabled()
    {
        return $this->status == "enabled";
    }

    public function isStarted()
    {
        return $this->isStarted == "yes";
    }


}

class virtPbx
{
    public function check()
    {
        l::ll(__CLASS__,__FUNCTION__);
        virtPbxChecker::check();
    }

    public function getList($clientId = null)
    {
        global $db_ats;

        if ($clientId === null)
            $clientId = getClientId();

        $numbers = array();
        $accounts = array();

        $vpbxId = 0;

        $isVirtPbxAvalible = false;
        $isStarted = false;
        foreach($db_ats->AllRecords(
                    $q = "
                    SELECT 
                        v.id as virtpbx_id, 
                        type, 
                        if(type = 'number', n.id, a.id) as id, 
                        if(type = 'number', n.number, a.account) as name,
                        direction,
                        is_started
                    FROM `a_virtpbx` v
                    LEFT JOIN a_virtpbx_link l on (v.id = l.virtpbx_id)
                    LEFT JOIN a_line a ON (a.id = type_id and l.type = 'account' and a.client_id = '".$clientId."')
                    LEFT JOIN a_number n ON (n.id = type_id and l.type = 'number' and n.client_id = '".$clientId."')
                    WHERE v.client_id = '".$clientId."'
                    ORDER BY a.account, n.number
                    ") as $l)
        {
            $vpbxId = $l["virtpbx_id"];

            if ($l["type"] == "number")
            {
                $numbers[$l["id"]] = array("id" => $l["id"], "number" => $l["name"], "direction" => $l["direction"]);
            } else if ($l["type"] == "account"){
                $accounts[$l["id"]] = array("id" => $l["id"], "account" => $l["name"]);
            }
            
            $isVirtPbxAvalible = true;
            $isStarted = $l["is_started"] == "yes";
        }

        return array(
                "id" => $vpbxId,
                "is_avalible" => $isVirtPbxAvalible,
                "is_started" => $isStarted,
                "accounts" => $accounts,
                "numbers" => $numbers
                );
    }

    public function getStatus($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;

        $r = $db_ats->GetRow("select enabled, is_started from a_virtpbx where client_id = '".$l["client_id"]."'");

        if (!$r)
        {
            $r = array("status" => 'notfound', "is_started" => false);
        } else {
            $r["status"] = $r["enabled"] == "yes" ? "enabled" : "disabled";
        }

        return new virtPbxStatus($r["status"], $r["is_started"]);
    }

    public function addNumber($clientId, $number, $direction = "full")
    {
        global $db_ats;

        $vpbx = self::getList($clientId);

        if (!$vpbx && !$vpbx["id"])
        {
            throw new Exception("VPBX у клиента не нейдана");
        }

        $numberId = ats2Numbers::getNumberId(array("client_id" => $clientId, "e164" => $number));

        if (!$numberId)
        {
            throw new Exception("Номер не найден у клиент (db:ats)");
        }

        if (isset($vpbx["numbers"][$numberId]))
            return true;

        return $db_ats->QueryInsert("a_virtpbx_link", array("virtpbx_id" => $vpbx["id"], "type" => "number", "type_id" => $numberId, "direction" => $direction));
    }


    public function delNumber($clientId, $number)
    {
        global $db_ats;

        $vpbx = self::getList($clientId);

        if (!$vpbx && !$vpbx["id"])
        {
            throw new Exception("VPBX у клиента не нейдана");
        }

        $numberId = ats2Numbers::getNumberId(array("client_id" => $clientId, "e164" => $number));

        if (!$numberId)
        {
            throw new Exception("Номер не найден у клиент (db:ats)");
        }

        if (!isset($vpbx["numbers"][$numberId]))
            return false;

        return $db_ats->QueryDelete("a_virtpbx_link", array("virtpbx_id" => $vpbx["id"], "type" => "number", "type_id" => $numberId));
    }

    public function changeDirection($clientId, $numberId, $newDirection)
    {
        global $db_ats;
        $vpbx = self::getList($clientId);

        if (!$vpbx && !$vpbx["id"])
        {
            throw new Exception("VPBX у клиента не нейдана");
        }

        $db_ats->QueryUpdate("a_virtpbx_link", array("virtpbx_id", "type_id", "type"), array("virtpbx_id" => $vpbx["id"], "type" => "number", "type_id" => $numberId, "direction" => $newDirection));
    }


    public function add($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;

        return $db_ats->QueryInsert("a_virtpbx", array(
                    "client_id" => $l["client_id"],
                    "tarif_id" => $l["tarif_id"],
                    )
                );
    }

    public function delet($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;

        return $db_ats->QueryDelete("a_virtpbx", array(
                    "client_id" => $l["client_id"]
                    )
                );
    }

    public function unMarkDelet($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);
        global $db_ats;

        $db_ats->QueryUpdate("a_virtpbx", "client_id",
                array(
                    "client_id" => $l["client_id"],
                    "enabled" => "yes",
                    )
                );
    }


    public function markDelete($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db_ats;

        $db_ats->QueryUpdate("a_virtpbx", 
                array("client_id"), 
                array(
                    "client_id" => $l["client_id"],
                    "enabled" => "no",
                    "disabled_date" => array("NOW()"),
                    )
                );

    }

    public function setStarted($clientId)
    {
        l::ll(__CLASS__,__FUNCTION__, $clientId);

        global $db_ats;

        $db_ats->QueryUpdate("a_virtpbx", 
                "client_id", 
                array(
                    "client_id" => $clientId,
                    "is_started" => "yes"
                    )
                );
    }


}

class virtPbxDiff
{
    public function apply(&$diff)
    {
        l::ll(__CLASS__,__FUNCTION__,$diff);

        if($diff["added"])
            self::add($diff["added"]);

        if($diff["deleted"])
            self::del($diff["deleted"]);

        if($diff["changed_tarif"])
            self::tarifChanged($diff["changed_tarif"]);

    }

    private function add(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $clientId => $l)
            virtPbxAction::add($l);
    }

    private function del(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $clientId => $l)
            virtPbxAction::del($l);
    }

    private function tarifChanged(&$d)
    {
        l::ll(__CLASS__,__FUNCTION__, $d);

        foreach($d as $cleintId => $l)
            virtPbxAction::tarifChanged($l);
    }

}

class virtPbxAction
{
    public function add(&$l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        $status = virtPbx::getStatus($l);

        if(!$status->isInDB())
        {
            virtPbx::add($l);
        }else{
            if(!$status->isEnabled())
            {
                virtPbx::unMarkDelet($l);
            }
        }

    }

    public function del(&$l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        $status = virtPbx::getStatus($l);

        l::ll(__CLASS__, __FUNCTION__, var_export($status, true));

        if (!$status->isInDB()) return;

        
        if($status->isStarted())
        {
            virtPbx::markDelete($l);
        }else{
            virtPbx::delet($l);
        }
    }

    public function delFull($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        $status = virtPbx::getStatus($l);

        if (!$status->isInDB()) return;

        virtPbx::delet($l);
    }

    public function tarifChanged($l)
    {
        l::ll(__CLASS__,__FUNCTION__, $l);

        global $db_ats;

        $db_ats->QueryUpdate("a_virtpbx", "client_id", array(
                    "client_id" => $l["client_id"],
                    "tarif_id" => $l["tarif_id"]
                    )
                );
    }
    
}
