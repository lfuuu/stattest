<?php


echo date("r")."\n";
	define('NO_WEB',1);
	define('PATH_TO_ROOT','./');
    define('DEBUG_LEVEL', 0);
    require_once(PATH_TO_ROOT.'conf_yii.php');



    foreach($db->AllRecords(
            "select 
                distinct super_id 
             from 
                clients 
             where 
                    status in ('work', 'connecting','testing') 
                and password !=''") as $idx => $s)
{
    //create super client
    try{
        SyncCore::AddSuperClient($s["super_id"]);
    }catch(Exception $e) {}

    foreach($db->AllRecords("select id, client, admin_contact_id from clients where 
                status in ('work', 'connecting','testing') 
                and super_id = '".$s["super_id"]."'
                and length(password) > 4 ") as $a)
    {
        // create account
        try{
            SyncCore::AddAccount($a["id"]);
        }catch(Exception $e) {}


        foreach(SyncCoreHelper::getProducts($a["id"]) as $prod)
        {
            if ($prod["mnemonic"] == "phone") continue;
            try{
                SyncCore::checkProductState($prod["mnemonic"], $a["client"]);
            }catch(Exception $e) {}
        }


        //set admin, if exists
        if ($a["admin_contact_id"])
        {
            try{
                SyncCore::adminChanged($a["id"]);
            }catch(Exception $e) {}
        }

    }
}
