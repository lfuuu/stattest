<?php

namespace app\classes;

use app\classes\api\ApiPhone;
use app\models\ActualNumber;
use app\models\UsageVoip;
use app\models\Region;

class ActaulizerVoipNumbers
{
    public static function me()
    {
        return new static();
    }

    private function __construct()
    {
        //
    }

    public function actualizeByNumber($number)
    {
        $this->checkSync($number);
    }

    public function actualizeAll()
    {
        $this->checkSync();
    }

    private function checkSync($number = null)
    {
        \l::ll(__CLASS__,__FUNCTION__, $number);

        if(
            $diff = $this->checkDiff(
                ActualNumber::dao()->loadSaved($number),
                ActualNumber::dao()->collectFromUsages($number)
            )
        )
        {
            $this->diffToSync($diff);
        }

    }

    public function sync($number = null)
    {
        \l::ll(__CLASS__,__FUNCTION__, $number);

        if (!$number) return;

        if(
            $diff = $this->diff(
                ActualNumber::dao()->loadSaved($number),
                ActualNumber::dao()->collectFromUsages($number)
            )
        )
        {
            $transaction = ActualNumber::getDb()->beginTransaction();
            try{

                $this->diffApply($diff);

                $transaction->commit();
            } catch(\Exception $e)
            {
                $transaction->rollback();
                throw $e;
            }
        }
    }

    private function checkDiff($saved, $actual)
    {
        \l::ll(__CLASS__,__FUNCTION__,/*$saved, $actual,*/ "...","...");

        $d = [];

        foreach(array_diff(array_keys($saved), array_keys($actual)) as $l)
        {
            $d[$l] = ["action" => "del"] + $saved[$l];
        }

        foreach(array_diff(array_keys($actual), array_keys($saved)) as $l)
            $d[$l] = ["action" => "add"] + $actual[$l];


        foreach(["client_id", "region", "call_count", "number_type", "direction", "line7800_id", "is_blocked", "is_disabled"] as $field)
        {
            foreach($actual as $number => $l)
            {
                if(isset($saved[$number]) && $saved[$number][$field] != $l[$field] && !isset($d[$number])) 
                {
                    $d[$number] = ["action" => "update"] + $l;
                }
            }
        }

        return $d;
    }

    private function diffToSync($diff)
    {
        foreach($diff as $data)
        {
            $this->event_go("ats3__sync", $data);       
        }
    }

    private function diff($saved, $actual)
    {
        \l::ll(__CLASS__,__FUNCTION__,/*$saved, $actual,*/ "...","...");

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
                        $d["changed"][$number]["data_new"] = $l;
                        $d["changed"][$number]["data_old"] = $saved[$number];
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
        \l::ll(__CLASS__,__FUNCTION__,$diff);

        if ($diff["added"])
            $this->applyAdd($diff["added"]);

        if ($diff["deleted"])
            $this->applyDeleted($diff["deleted"]);

        if ($diff["changed"])
            $this->applyChanged($diff["changed"]);
    }

    private function applyAdd($numbers)
    {
        \l::ll(__CLASS__,__FUNCTION__, $numbers);

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
        \l::ll(__CLASS__,__FUNCTION__, $numbers);

        foreach($numbers as $numberData)
        {
            ActualNumber::findOne(["number" => $numberData["number"]])->delete();
            $this->del_event($numberData);
        }
    }

    private function applyChanged($numbers)
    {
        \l::ll(__CLASS__,__FUNCTION__, $numbers);

        foreach($numbers as $number => $data)
        {
            $n = ActualNumber::findOne(["number" => $number]);

            if ($n)
            {
                $n->setAttributes($data["changed_fields"], false);
                $n->save();

                $this->change_event($number, $data);
            }
        }
    }

    private function add_event($data)
    {
        \l::ll(__CLASS__,__FUNCTION__, $data);

        $params = UsageVoip::find()->phone($data["number"])->actual()->one()->create_params;

        if (!$params || !($params = json_decode($params, true)))
        {
            $params = ["type_connect" => "line", "sip_accounts" => 1]; //by default
        }

        $s = [
            "client_id"    => (int) $data["client_id"],
            "did"          =>       $data["number"],
            "ds"           =>       $data["direction"],
            "cl"           => (int) $data["call_count"],
            "region"       => (int) $data["region"],
            "timezone"     =>       $this->getTimezoneByRegion($data["region"]),
            "type"         =>       $params["type_connect"],
            "sip_accounts" =>       $params["sip_accounts"],
            "nonumber"     => (bool)$this->isNonumber($data["number"])
            ];

        if ($s["nonumber"] && $this->is7800($s["did"]))
        {
            if (!$data["line7800_id"])
            {
                print_r($data);
                throw new \Exception("Usage line not set");
            }

            $usage_line  = UsageVoip::findOne($data["line7800_id"]);
            if (!$usage_line)
            {
                print_r($data);
                throw new \Exception("Usage line not found");
            }

            $s["nonumber_phone"] = $usage_line->E164;
        }

        if ($s["type"] == "multi")
        {
            $s["multitrunk_id"] = (int)$params["multitrunk_id"];
        } elseif ($s["type"] == "vpbx") {
            $s["vpbx_id"] = (int)$params["vpbx_id"];
        }

        $this->execQuery("add_did", $s);

        return $s;
    }

    private function del_event($data)
    {
        \l::ll(__CLASS__,__FUNCTION__, $data);

        $s = [
            "client_id" => $data["client_id"],
            "did" => $data["number"]
            ];

        $this->execQuery("disable_did", $s);
    }

    private function change_event($number, $data)
    {
        \l::ll(__CLASS__,__FUNCTION__, $number, $data);

        $old = $data["data_old"];
        $new = $data["data_new"];
        $changedFields = $data["changed_fields"];

        $structClientChange = null;

        //change client_id
        if (isset($changedFields["client_id"]))
        {
            $isMoved = UsageVoip::find()->phone($number)->actual()->one()->next_usage_id;

            if ($isMoved)
            {
                $structClientChange = [
                    "old_client_id" => (int)$old["client_id"],
                    "did" => $number,
                    "client_id" => (int)$new["client_id"]
                    ];

                $this->execQuery("edit_client_id", $structClientChange);
                unset($changedFields["client_id"]);
            } else {
                $this->del_event($old);
                $this->add_event($new);
                return true;
            }
        }

        // номер заблокирован (есть только входящая связь)
        if (isset($changedFields["is_blocked"]))
        {
            $s = [
                "client_id" => (int)$new["client_id"],
                "number" => $number
            ];

            $this->event_go("ats3__blocked_number");

            unset($changedFields["is_blocked"]);
        }

        // номер временно отключен (отключение и входящей и исходящей связи)
        if (isset($changedFields["is_disabled"]))
        {
            $s = [
                "client_id" => (int)$new["client_id"],
                "number" => $number
            ];

            $this->event_go("ats3__disabled_number", $s);

            unset($changedFields["is_disabled"]);
        }

        //change fields
        if ($changedFields)
        {
            $structChange = [
                "client_id"    => (int) $new["client_id"],
                "did"          =>       $number,
                "ds"           =>       $new["direction"],
                "cl"           => (int) $new["call_count"]
                ];

            if (isset($changedFields["region"]))
            {
                $structChange["region"] = (int)$changedFields["region"];
                $structChange["timezone"] = $this->getTimezoneByRegion($changedFields["region"]);
            }

            if (isset($changedFields["line7800_id"]))
            {
                $usage_line  = UsageVoip::findOne($changedFields["line7800_id"]);
                if (!$usage_line)
                    throw new \Exception("Usage line not found");

                $structChange["nonumber_phone"] = $usage_line->E164;
            }

            $this->execQuery("edit_did", $structChange);
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

    private function getTimezoneByRegion($regionId)
    {
        $region = Region::findOne($regionId);

        if ($region)
            return $region->timezone_name;

        return 'Europe/Moscow';
    }

    private function event_go($event, $data = null)
    {
        \l::ll(__CLASS__,__FUNCTION__, $event, $data);

        $code = md5($event."|||".json_encode($data));

        $row = UsageVoip::getDb()->createCommand("select id from event_queue where code=:code and status not in ('ok', 'stop')", [":code" => $code])->queryOne();

        if (!$row)
        {
            UsageVoip::getDb()->createCommand()->insert("event_queue", ["event" => $event, "param" => json_encode($data), "code" => $code])->execute();
        }
    }

    private function execQuery($action, $data)
    {
        if (!defined("ats3_silent")) {
            ApiPhone::exec($action, $data);
        }
    }
}

