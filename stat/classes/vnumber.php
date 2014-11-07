<?php

class vNumber
{

    public static function getAll()
    {
        global $db;
        $rr = $db->AllRecords($q ="select * from v_number where ".sqlClient()." order by enabled, number");

        $ns = self::getFree();
        foreach($rr as &$r)
            $r["to_del"] = isset($ns[$r["id"]]);

        return $rr;
    }

    public static function getFree($id = false)
    {
        global $db;
        
        $allNums = array();
        foreach($db->AllRecords("select id, number, call_count from v_number 
                    where ".sqlClient()." 
                    and enabled='yes' 
                    and (
                           number like '749%' # moskov number
                        || length(number) < 6 # or line_without_number
                        )") as $l)
            $allNums[$l["id"]] = $l;

        $usedNums = array();
        foreach($db->AllRecords(
                    "select *, (select group_concat(number_id) from v_number_mt where sip_id = s.id) as numbers_mt 
                    from v_sip s where 
                     atype in ('number','link') and ".sqlClient()
                    .($id !== false ? " and s.id != '".$id."'" : "")
                    ) as $n)
        {
            if($n["atype"] == "link")
            {
                foreach(explode(",", $n["numbers_mt"]) as $nId)
                {
                    unset($allNums[$nId]);
                    $usedNums[$nId] = 1;
                }
            }else{
                $usedNums[$n["number"]] = 1;
                unset($allNums[$n["number"]]);
            }
        }

        return $allNums;
    }
}
