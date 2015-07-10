<?php


echo date("r")."\n";
	define('NO_WEB',1);
	define('NUM',20);
	define('PATH_TO_ROOT','./');
    define('DEBUG_LEVEL', 0);
    require_once('./conf_yii.php');
    require_once('./include/sql.php');


    $db->Query("delete from client_super");
    $db->Query("delete from client_contragent");

    foreach($db->AllRecords("select id, client, company from clients") as $k => $c)
{
    if (!$c["company"])
        $c["company"] = $c["client"];

    if (strpos($c["client"], "/") !== false)
    {
        echo "\n".$c["client"];
        $main_card = substr($c['client'],0,-2);

        $cc = $db->GetRow($q = "select super_id, contragent_id from clients where client = '".$main_card."'");
        $db->QueryUpdate("clients", "id", array("id" => $c["id"], "super_id" => $cc["super_id"], "contragent_id" => $cc["contragent_id"]));

        //
    } else {
        // main card
        echo ".";


        $superId = $db->QueryInsert("client_super", array("name" => $c["company"]));
        $contragentId = $db->QueryInsert("client_contragent", array("name" => $c["company"], "country_id" => $c["country_id"], "super_id" => $superId));

        $db->QueryUpdate("clients", "id", array("id" => $c["id"], "super_id" => $superId, "contragent_id" => $contragentId));
    }

    if (($k % 100) == 0)
        echo "\n".$k;
}

