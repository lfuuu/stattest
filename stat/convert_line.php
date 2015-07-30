<?php


define("PATH_TO_ROOT", "./");
define("NO_WEB", 1);



//$_SERVER['SERVER_NAME'] = "89.235.136.20";

include PATH_TO_ROOT."conf_yii.php";


die("Временно отключено\n");


foreach($db->AllRecords("select * from v_sip where type='line' and atype='number'") as $l)
{
    $num = $db->GetRow("select number_id, direction from v_number_mt where sip_id = '".$l["id"]."'");

    foreach($db->AllRecords("select id from v_sip where parent_id = '".$l["id"]."'") as $p)
    {
        if(!$db->GetValue("select sip_id from v_number_mt where sip_id = '".$p["id"]."'"))
        {
            $num["sip_id"] = $p["id"];
            $db->QueryInsert("v_number_mt", $num);
        }
        
    }
}




