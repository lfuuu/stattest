<?php

if(count($_SERVER["argv"]) < 2)
{
    die("\n\nPlease use: ".$_SERVER["PHP_SELF"]." { clean | test | copy | del}\n\n");
}

$a = "";

switch($_SERVER["argv"][1])
{
    case 'clean': $a = "clean"; break;
    case 'test': $a = "test"; break;
    case 'copy': $a = "copy"; break;
    case 'del': $a = "del";   break;
}

if(!$a)
    die("\n\nPlease use: ".$_SERVER["PHP_SELF"]." { test | copy | del}\n\n");



define("PATH_TO_ROOT", "./");
//define("print_sql", 1);
define("exception_sql",1);

include "./conf.php";

try{

if($a == "clean")
{
    $db->Query("truncate usage_virtpbx");

    echo "\nClean complete \n";
    exit();
}



$tarifIds = $db->GetValue("SELECT group_concat(id) FROM `tarifs_extra` WHERE `code` = 'welltime' AND `description` LIKE '%÷ÉÒÔÕÁÌØÎÁÑ áôó ĞÁËÅÔ%'");

if(!$tarifIds) die("VirtPBX tarifs not found");

$ll = $db->AllRecords("select * from usage_welltime where tarif_id in (".$tarifIds.")");

//ÓÏÏÔ×ÅÔÓÔ×ÉÅ ÏÄÎÏÇÏ ÔÁÒÉÆÁ × ÉÚÎÁŞÁÌØÎÏÊ ÕÓÌÕÇÅ, Ë ÔÁÒÉÆÕ × ĞÅÒÅÎÏÓÉÍÏÊ
$tts = array(
        "339" => "1",
        "340" => "2",
        "341" => "3"
        );

$notResp = array();

foreach($ll as &$l)
{
    if(!isset($tts[$l["tarif_id"]]))
    {
        die("Tarif id=".$l["tarif_id"]." not found");
    }else
        $l["tarif_id"] = $tts[$l["tarif_id"]];

    $l["ats_router"] = $l["router"];
    unset($l["router"], $l["ip"]);

}
unset($l);

if($a == "test") 
{
    if($notResp)
    {
        print_r($notResp);
        die("Not all rows responsed");
    }
    exit();
}else{
    if($notResp)
    {
        print_r($notResp);
        die("Not all rows responsed");
    }
}

if($a == "copy")
{

    foreach($ll as $l)
    {
        $db->QueryInsert("usage_virtpbx", $l);
    }

    echo "Copied ".count($ll)." records";
}

if($a == "del")
{
    $cc = 0;
    foreach($ll as $l)
    {
        $db->Query("delete from usage_welltime where id = '".$l["id"]."'");
        $cc += 1;
    }

    echo "Delete ".$cc." records";
}

}catch(Exception $e)
{
    die("ERROR: ".$e->GetMessage());
}


