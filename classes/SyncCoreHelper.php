<?php

class SyncCoreHelper
{

    private static $allowClientStatusSQL = array("work","connecting","testing");

    function getFullClientStruct($superId) // only super client && conragent
    {
        echo "\n".__FUNCTION__;
        global $db;

        $super = $db->GetRow("select id, name from client_super where id = '".$superId."'");

        $emails = array();

        $password = "";
        $main_card = "";
        $main_card_id = 0;

        $data = array("client" => array("id" => $super["id"], "name" => Encoding::toUtf8($super["name"])), "contragents" => array());

        foreach($db->AllRecords("select id, name from client_contragent where super_id = '".$superId."'")as $contr)
        {
            $dataContragent = array("id" => $contr["id"], "name" => Encoding::toUtf8($contr["name"]), "accounts" => array());

            foreach($db->AllRecords("select id, client, password, status  from clients where contragent_id = '".$contr["id"]."'") as $c)
            {
                $isMainCard = strpos($c["client"], "/") === false;
                if ($isMainCard)
                {
                    $password = $c["password"];
                    $main_card = $c["client"];
                    $main_card_id = $c["id"];
                }

                if (!$isMainCard && !in_array($c["status"], self::$allowClientStatusSQL)) continue;

                //self::loadEmails($emails, $c["id"]);

                /* add account event fiered next time
                $_account = array("id" => $c["id"], "products" => self::getProducts($c["id"]));
                $dataContragent["accounts"][] = $account;
                */
            }
            $data["contragents"][] = $dataContragent;
        }

        $data["admin"] = array("email" => $main_card_id."@mcn.ru", "password" => $password?:password_gen(), "active" => true);
        /*
        if ($emails && $password)
        {
            $data["admin"] = array();
            foreach(array_keys($emails) as $email)
            {
                $data["admin"][] = array("email" => $email, "password" => $password);
            }
        }
        */

        return $data;
    }

    public function getAccountStruct($cl)
    {
        echo "\n".__FUNCTION__;

        var_dump($cl->status);
        if (!$cl->isMainCard() && !in_array($cl->status, self::$allowClientStatusSQL)) return false;
         
        return array(
                "contragent" => array("id" => $cl->contragent_id), 
                "accounts" => array(
                    array(
                        "id" => $cl->id,
                        "products" => array()//self::getProducts($cl->id)
                        )
                    )
                );
    }

    function loadEmails(&$emails, $clientId)
    {
        echo "\n".__FUNCTION__;
        global $db;
        foreach($db->AllRecords(
                    "SELECT data 
                    FROM `client_contacts` 
                    WHERE 
                    `client_id` = '".$clientId."' 
                    AND `type` = 'email' 
                    AND `is_active` = '1' 
                    AND `is_official` = '1'
                    ") as $e)
        {
            $emails[$e["data"]] = 1;
        }
    }

    function getProducts($clientId)
    {
        echo "\n".__FUNCTION__;
        $products = array();

        if (($vpbx = self::getProductVPBX($clientId)))
            $products[] = $vpbx;

        if (($phone = self::getProductPhone($clientId)))
            $products[] = $phone;

        return $products;
    }

    function getEmailStruct($email, $password)
    {
        return array("email" => $email, "password" => $password);
    }


    function getProductVPBX($clientId)
    {
        global $db;
        $vpbxIP = $db->GetValue($q = "
                SELECT
                s.ip
                FROM (
                    SELECT
                    max(u.id) as virtpbx_id
                    FROM
                    usage_virtpbx u, clients c
                    WHERE
                    c.id = '".$clientId."'
                    AND c.client = u.client
                    AND actual_from <= cast(now() AS date)
                    AND actual_to >= cast(now() AS date)
                    ) a, usage_virtpbx u
                LEFT JOIN tarifs_virtpbx t ON (t.id = u.tarif_id)
                LEFT JOIN server_pbx s ON (s.id = u.server_pbx_id)
                WHERE u.id = a.virtpbx_id
                ");

        return $vpbxIP ? array("server_host" => (defined("VIRTPBX_TEST_ADDRESS") ? VIRTPBX_TEST_ADDRESS :$vpbxIP), "mnemonic" => "vpbx") : false;
    }

    function getProductPhone($clientId)
    {
        global $db;

        if ($db->GetValue("SELECT
                    count(*)
                    FROM
                    usage_voip u, clients c
                    WHERE
                    c.id = '".$clientId."'
                    AND c.client = u.client
                    AND actual_from <= cast(now() AS date)
                    AND actual_to >= cast(now() AS date)") > 0)
        {
            return array("server_host" => "lk.mcn.ru", "mnemonic" => "phone");
        }

        return false;
    }

    public function getProductSavedState($clientId, $product, $returnObj = false)
    {
        $state = ProductState::find("first", array("client_id" => $clientId, "product" => $product));

        return $returnObj ? $state : (bool)$state;
    }

    public function setProductSavedState($clientId, $product, $newState)
    {
        $oldState = self::getProductSavedState($clientId, $product, true);

        if (!$newState && $oldState)
        {
            $oldState->delete();
        }

        if ($newState)
        {
            if (!$oldState)
            {
                $newState = new ProductState();
                $newState->client_id = $clientId;
                $newState->product = $product;
                $newState->save();
            }
        }
    }

    public function getProductState($clientId, $product)
    {
        switch($product)
        {
            case 'vpbx': return self::getProductVPBX($clientId); 
            case 'phone': return self::getProductPhone($clientId); 
            default: return false;
        }
    }

    public function getAddProductStruct($clientId, $productStruct)
    {
        return array("account" => array("id" => $clientId), "products" => array($productStruct));
    }

    public function adminChangeStruct($superId, $email, $password, $isActive)
    {
        return array("client_id" => $superId, "admin" => array("email" => $email, "password" => $password, "active" => (bool)$isActive));
    }

}

