<?php

use app\models\ActualNumber;
use app\models\NumberCreateParams;

class ActaulizerVoipNumbers
{
    public function actualize()
    {
        l::ll(__CLASS__,__FUNCTION__);

        if(
            $diff = $this->diff(
                $this->load("saved"), 
                $this->load("actual")
            )
        )
        {
            $this->diffApply($diff);
        }
    }

    private function load($type)
    {
        l::ll(__CLASS__,__FUNCTION__,$type);

        switch($type)
        {
            case 'actual': $data = ActualNumber::dao()->collectFromUsages(); break;
            case 'saved': $data = ActualNumber::dao()->loadSaved(); break;
            default: throw new Exception("Unknown type");
        }

        $d = array();
        foreach($data as $l)
            $d[$l["number"]] = $l;

        if (!$d)
            throw new Exception("Data not load");

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

        foreach(array_diff(array_keys($saved), array_keys($actual)) as $l)
        {
            $d["deleted"][$l] = $saved[$l];
        }

        foreach(array_diff(array_keys($actual), array_keys($saved)) as $l)
            $d["added"][$l] = $actual[$l];


        foreach(["client_id", "region", "call_count", "number_type", "direction", "line7800_id", "is_blocked", "is_disabled"] as $field)
        {
            foreach($actual as $number => $l)
            {
                if(isset($saved[$number]) && $saved[$number][$field] != $l[$field]) 
                {
                    if (!isset($d["changed"][$number]["changed_fields"]))
                    {
                        $d["changed"][$number]["data_new"][$field] = $l;
                        $d["changed"][$number]["data_old"][$field] = $saved[$number];
                    }

                    $d["changed"][$number]["changed_fields"][$field] = $l[$field];
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
        l::ll(__CLASS__,__FUNCTION__, $numbers);

        foreach($numbers as $numberData)
        {
            $n = new ActualNumber();
            $n->setAttributes($numberData, false);
            $n->save();

            $this->add_event($numberData);
        }
    }

    private function applyDeleted($numbers)
    {
        l::ll(__CLASS__,__FUNCTION__, $numbers);

        foreach($numbers as $numberData)
        {
            ActualNumber::findOne(["number" => $numberData["number"]])->delete();
            $this->del_event($numberData);
        }
    }

    private function applyChanged($numbers)
    {
        l::ll(__CLASS__,__FUNCTION__, $numbers);

        foreach($numbers as $number => $data)
        {
            $n = ActualNumber::findOne(["number" => $number]);

            if ($n)
            {
                $n->setAttributes($data["changed_fields"], false);
                $n->save();

                $this->change_event($clientId, $number, $data);
            }
        }
    }

    private function add_event($data)
    {
        $params = NumberCreateParams::getParams($data["number"]);
        $s = [
            "client_id"    => (int) $data["client_id"],
            "did"          =>       $data["number"],
            "ds"           =>       $data["direction"],
            "cl"           => (int) $data["call_count"],
            "region"       => (int) $data["region"],
            "type"         =>       $params["type_connect"],
            "sip_accounts" =>       $params["sip_accounts"],
            "nonumber"     => (bool)$this->isNonumber($data["number"]),
            "virtual"      => (bool)$data["number_type"] == "vnumber"
            ];

        if ($s["nonumber"] && $this->is7800($s["did"]) && $params["line7800_id"])
        {
            $usage_line  = UsageVoip::findOne($data["line7800_id"]);
            if (!$usage_line)
                throw new Exception("Usage line not found");

            $s["nonumber_phone"] = $usage_line->E164;
        }

        if ($s["type"] == "multi")
        {
            $s["id_multitrunk"] = $params["multitrunk_id"];
        } elseif ($s["type"] == "vpbx") {
            $s["vpbx_id"] = $params["vpbx_id"];
        }

        \event::go("ats3__add_number", $s);

        return $s;
    }

    private function del_event($data)
    {
        $s = [
            "client_id" => $data["client_id"],
            "did" => $data["number"]
            ];

        \event::go("ats3__del_number", $s);
    }

    private function change_event($clientId, $number, $data)
    {
        $old = $data["data_old"];
        $new = $data["date_new"];
        $changed = $data["changed_fields"];

        $structClientChange = null;

        //change client_id
        if (isset($changedFields["client_id"]))
        {
            $structClientChange = [
                "old_client_id" => (int)$old["client_id"],
                "did" => $number,
                "client_id" => (int)$new["client_id"]
                ];

            \event::go("ats3__change_client", $structClientChange);
            unset($changedFields["client_id"]);
        }

        // номер заблокирован (есть только входящая связь)
        if (isset($changedFields["is_blocked"]))
        {
            $s = [
                "client_id" => (int)$new["client_id"],
                "number" => $number
            ];

            \event::go("ats3__blocked_number");

            unset($changedFields["is_blocked"]);
        }

        // номер временно отключен (отключение и входящей и исходящей связи)
        if (isset($changedFields["is_disabled"]))
        {
            $s = [
                "client_id" => (int)$new["client_id"],
                "number" => $number
            ];

            \event::go("ats3__disabled_number");

            unset($changedFields["is_disabled"]);
        }

        //change fields
        if ($changedFields)
        {
            $structChange = [
                "client_id"    => (int) $new["client_id"],
                "did"          =>       $number
                ];

            if (isset($changedFields["direction"]))
                $structChange["ds"] = $changedFields["direction"];

            if (isset($changedFields["call_count"]))
                $structChange["cl"] = (int)$changedFields["call_count"];

            if (isset($changedFields["region"]))
                $structChange["region"] = (int)$changedFields["region"];

            if (isset($changedFields["line7800_id"]))
            {
                $usage_line  = UsageVoip::findOne($changedFields["line7800_id"]);
                if (!$usage_line)
                    throw new Exception("Usage line not found");

                $structChange["nonumber_phone"] = $usage_line->E164;
            }

            \event::go("ats3__update_number", $structChange);
        }
    }

    private function isNonumber($number)
    {
        return (strlen($number) < 6) || $this->is7800($number);
    }

    private function is7800($number)
    {
        return (strpos($number, "7800") === 0);
    }
}

