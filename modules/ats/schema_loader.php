<?php


class schemaLoader
{
    public function load($schemaId, $soundFiles = false, $aStatus = false, $isNormalOrderTimeBlock = false)
    {
        global $db;

        $d = array();


        $aTimes = $db->AllRecords("select * from r_time where schema_id = '".$schemaId."' order by id");
        $aNumbers = $db->AllRecords("select * from r_number where schema_id = '".$schemaId."' order by id");
        $aAction = $db->AllRecords("select * from r_action where schema_id = '".$schemaId."' order by id");


        foreach($db->AllRecords("select * from r_timeblock where schema_id = '".$schemaId."' order by id ".($isNormalOrderTimeBlock ? "" : "desc")) as $tb)
        {
            $tt = $tb;
            $tt["times"] = array();
            $tt["redirs"] = array();
            foreach($aTimes as $t)
                if($t["timeblock_id"] == $tb["id"])
                    $tt["times"][] = $t;

            if($tt["status"] == "alltime")
            {
                $tt["title"] = $tt["is_alltime"] == "no" ? helper_schedule::timesToText($tt["times"]) : "Все время";
            }else{
                $tt["title"] = $aStatus[$tt["status"]];
            }
            $tt["title"] = $tt["title"];

            $rr = array();
            foreach($aAction as $a)
                if($a["timeblock_id"] == $tb["id"])
                {
                    if($a["action"] == "redirect")
                    {
                        $nn = array();
                        foreach($aNumbers as $n)                
                            if($n["action_id"] == $a["id"])
                                $nn[] = $n["num"];
                        $a["nums"] = $nn;
                    }

                    $a["sound_name"] = $a["sound_id"] > 0 ? $soundFiles[$a["sound_id"]]["name"] : "";
                    $rr[] = $a;
                }

            $tt["redirs"] = $rr;

            if($tt["is_alltime"] == "yes")
                $tt["id"] = 0;

            $tt["is_alltime"] = $tt["is_alltime"] == "yes";

            $d[] = $tt;
        }

        return $d;
    }
}
