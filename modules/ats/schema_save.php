<?php

$d = $_POST["data"];
$schemaName = iconv("utf-8", "koi8-r", $_POST["schema_name"]);
$schemaId = $_POST["schema_id"];

/*
$d = file_get_contents("1.xml");
$schemaName = 112;
$schemaId = 7;
*/

$x = simplexml_load_string($d);

//print_r($x);

$s = array();

foreach($x->section as $section)
{
    $_sec = array("id" => (string)$section->id, "status" => (string)$section->status, "time" => array());
    $redir = array();
    foreach($section->collection->item as $t)
    {
        $t = (string)$t;
        if(substr($t, 0, 5) == "time_")
        {
            if(preg_match_all("/time_\d+\[(\d+)\]\[([^\]]+)\]=(.*)/", $t, $o))
            {
                $_sec["time"][$o[1][0]][$o[2][0]] = $o[3][0];
            }
        }else{
            list($k, $v) = explode("=", $t);

            if(!in_array($k, array("action", "status", "sound_id", "timeout", "nums","strategy"))) continue;

            if($k == "action")
            {
                if($redir)
                {
                    $_sec["redir"][] = $redir;
                    $redir = array();
                }
            }
            if($k == "nums")
            {
                $redir[$k][] = $v;
            }else{
                $redir[$k] = $v;
            }
        }
    }
    if($redir)
    {
        $_sec["redir"][] = $redir;
        $redir = array();
    }
    $s[] = $_sec;
}

//printdbg($x);
//printdbg($s);

//$schemaId = $db->GetInsertId();

$db->Query("delete from r_timeblock where schema_id = '".$schemaId."'");
$db->Query("delete from r_time where schema_id = '".$schemaId."'");
$db->Query("delete from r_action where schema_id = '".$schemaId."'");
$db->Query("delete from r_number where schema_id = '".$schemaId."'");

$db->Query("update r_schema set name = '".mysql_escape_string($schemaName)."' where id = '".$schemaId."'");

foreach($s as $timeBlock)
{
    $tbId = $db->QueryInsert(
            "r_timeblock",
            array(
                "schema_id" => $schemaId,
                "is_alltime" => $timeBlock["id"] == 0 ? "yes" : "no",
                "status" => $timeBlock["status"]
                )
            );
    foreach($timeBlock["time"] as $t)
    {
        $t["schema_id"] = $schemaId;
        $t["timeblock_id"] = $tbId;
        $db->QueryInsert("r_time", $t);
    }

    if(isset($timeBlock["redir"]))
        foreach($timeBlock["redir"] as $r)
        {
            $r["schema_id"] = $schemaId;
            $actionId = $db->QueryInsert(
                    "r_action",
                    array(
                        "schema_id" => $schemaId,
                        "timeblock_id" => $tbId,
                        "action" => $r["action"],
                        "sound_id" => $r["sound_id"],
                        "strategy" => $r["strategy"],
                        "timeout" => $r["timeout"]
                        )
                    );

            if(isset($r["nums"]) && $r["nums"])
            {
                foreach($r["nums"] as $n)
                {
                    $db->QueryInsert(
                            "r_number",
                            array(
                                "schema_id" => $schemaId,
                                "action_id" => $actionId,
                                "num" => $n)
                            );
                }
            }
        }
}



