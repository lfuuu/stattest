<?php

class ActaulizerVoipNumbers
{
    public function actualize()
    {
        l::ll(__CLASS__,__FUNCTION__);

        $actual = $this->load("actual");

        if($diff = $this->diff($this->load("number"), $actual))
        {
            $this->diffApply($diff);
        }
    }

    private static $sqlActual = "
        SELECT 
            client_id, 
            e164 AS number, 
            region, 
            no_of_lines AS call_count, 
            IF(is_virtual, 'vnumber', IF(LENGTH(e164) > 5,'number','nonumber')) AS number_type,
            allowed_direction AS direction, 
            line7800_id,
            is_blocked, 
            voip_disabled AS is_disabled

        FROM (
                SELECT
                    c.id AS client_id,
                    TRIM(e164) AS e164,
                    u.no_of_lines,
                    u.region,
                    IFNULL((SELECT an.id FROM usage_voip u7800, actual_number an WHERE u7800.id = u.line7800_id and an.number = u7800.e164), 0) AS line7800_id,
                    IFNULL((SELECT block FROM log_block WHERE id= (SELECT MAX(id) FROM log_block WHERE service='usage_voip' AND id_service=u.id)), 0) AS is_blocked,
                    IFNULL((
                        SELECT 
                            is_virtual 
                        FROM 
                            log_tarif lt, tarifs_voip tv 
                        WHERE 
                                service = 'usage_voip' 
                            AND id_service = u.id 
                            AND id_tarif = tv.id 
                        ORDER BY lt.date_activation DESC, lt.id DESC 
                        LIMIT 1), 0) AS is_virtual,
                    allowed_direction,
                    c.voip_disabled
                FROM
                    usage_voip u, clients c
                WHERE
                    (actual_from <= DATE_FORMAT(now(), '%Y-%m-%d') and actual_to >= DATE_FORMAT(now(), '%Y-%m-%d'))
                    and u.client = c.client 
                    and ((c.status in ('work','connecting','testing')) or c.id = 9130) 
                    and LENGTH(e164) > 3
                ORDER BY u.id
            )a
            ";

    private static $sqlNumber=
        "
        SELECT 
            client_id, 
            number, 
            region, 
            call_count, 
            number_type, 
            direction, 
            line7800_id,
            is_blocked, 
            is_disabled 
        FROM 
            actual_number a
        ORDER BY id";

    private function load($type)
    {
        l::ll(__CLASS__,__FUNCTION__,$type);
        global $db;

        $sql = "";

        switch($type)
        {
            case 'actual': $sql = self::$sqlActual; break;
            case 'number': $sql = self::$sqlNumber; break;
            default: throw new Exception("Unknown type");
        }

        $d = array();
        foreach($db->AllRecords($sql) as $l)
            $d[$l["number"]] = $l;

        //if (!$d)
        //    throw new Exception("Data not load");

        return $d;
    }

    private function diff($saved, $actual)
    {
        l::ll(__CLASS__,__FUNCTION__,/*$saved, $actual,*/ "...","...");

        $d = array(
                "added" => array(), 
                "deleted" => array(), 
                "changed" => array(), 
                );

        $d["deleted"] = array_diff(array_keys($saved), array_keys($actual));

        foreach(array_diff(array_keys($actual), array_keys($saved)) as $l)
            $d["added"][$l] = $actual[$l];


        foreach(["client_id", "region", "call_count", "number_type", "direction", "line7800_id", "is_blocked", "is_disabled"] as $field)
        {
            foreach($actual as $number => $l)
            {
                if(isset($saved[$number]) && $saved[$number][$field] != $l[$field]) 
                {
                    $d["changed"][$number][$field] = $l[$field];
                }
            }
        }

        foreach($d as $k => $v)
            if($v)
                return $d;

        return false;
    }

    private function diffApply($diff)
    {
        l::ll(__CLASS__,__FUNCTION__,$diff);

        if ($diff["added"])
            $this->applyAdd($diff["added"]);

        if ($diff["deleted"])
            $this->applyDeleted($diff["deleted"]);

        if ($diff["changed"])
            $this->applyChanged($diff["changed"]);
    }

    private function applyAdd($numbers)
    {
        global $db;

        foreach($numbers as $number)
        {
            $db->QueryInsert("actual_number", $number);
        }
    }

    private function applyDeleted($numbers)
    {
        global $db;

        foreach($numbers as $number)
        {
            $db->QueryDelete("actual_number", ["number" => $number]);
        }
    }

    private function applyChanged($numbers)
    {
        l::ll(__CLASS__,__FUNCTION__, $numbers);
        global $db;

        foreach($numbers as $number => $data)
        {
            $data["number"] = $number;
            $db->QueryUpdate("actual_number", "number", $data);
        }
    }
}

