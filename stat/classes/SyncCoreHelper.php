<?php

class SyncCoreHelper
{

    private static $allowClientStatusSQL = array("income", "negotiations", "work", "connecting", "testing", "debt", "operator");

    static function getFullClientStruct($superId) // only super client && conragent
    {
        echo "\n" . __FUNCTION__;
        global $db;

        $super = $db->GetRow("select id, name from client_super where id = '" . $superId . "'");

        $emails = array();

        $password = "";
        $main_card = "";
        $main_card_id = 0;

        $data = array("client" => array("id" => $super["id"], "name" => $super["name"]), "contragents" => array());

        $contragents = \app\models\ClientContragent::findAll(['super_id' => $superId]);
        foreach ($contragents as $contr) {
            $dataContragent = array("id" => $contr["id"], "name" => $contr["name"], "accounts" => array());

            $adminContactId = 0;
            foreach ($contr->getContracts() as $contract) {
                foreach ($contract->getAccounts() as $c) {
                    $isMainCard = strpos($c["client"], "/") === false;
                    if ($isMainCard) {
                        $password = $c["password"];
                        $main_card = $c["client"];
                        $main_card_id = $c["id"];
                        $adminContactId = $c["admin_contact_id"];
                    }

                    if (!$isMainCard && !in_array($c["status"], self::$allowClientStatusSQL)) continue;

                    //self::loadEmails($emails, $c["id"]);

                    /* add account event fiered next time
                    $_account = array("id" => $c["id"], "products" => self::getProducts($c["id"]));
                    $dataContragent["accounts"][] = $account;
                    */
                }
            }
            $data["contragents"][] = $dataContragent;
        }

        $adminEmail = $main_card_id . "@mcn.ru";

        if ($adminContactId) {
            $contact = app\models\ClientContact::findOne(["id" => $adminContactId, "type" => "email"]);

            if ($contact) {
                $adminEmail = $contact->data;
            }
        }

        $data["admin"] = array("email" => $adminEmail, "password" => $password ?: password_gen(), "active" => true);
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

    public static function getAccountStruct(\app\models\ClientAccount $cl)
    {
        echo "\n" . __FUNCTION__;

        if (!in_array($cl->status, self::$allowClientStatusSQL)) return false;

        return array(
            "contragent" => array("id" => $cl->contract->contragent_id),
            "accounts" => array(
                array(
                    "id" => $cl->id,
                    "products" => array()//self::getProducts($cl->id)
                )
            )
        );
    }

    static function loadEmails(&$emails, $clientId)
    {
        echo "\n" . __FUNCTION__;
        global $db;
        foreach ($db->AllRecords(
            "SELECT data
                    FROM `client_contacts` 
                    WHERE 
                    `client_id` = '" . $clientId . "'
                    AND `type` = 'email' 
                    AND `is_active` = '1' 
                    AND `is_official` = '1'
                    ") as $e) {
            $emails[$e["data"]] = 1;
        }
    }

    static function getEmailStruct($email, $password)
    {
        return array("email" => $email, "password" => $password);
    }

    static function getProductPhone($clientId)
    {
        global $db;

        if ($db->GetValue("SELECT
                    count(*)
                    FROM
                    usage_voip u, clients c
                    WHERE
                    c.id = '" . $clientId . "'
                    AND c.client = u.client
                    AND actual_from <= cast(now() AS date)
                    AND actual_to >= cast(now() AS date)") > 0
        ) {
            return array("server_host" => \Yii::$app->params['PHONE_SERVER'], "mnemonic" => "phone");
        }

        return false;
    }

    public static function getProductSavedState($clientId, $product, $returnObj = false)
    {
        $state = ProductState::find("first", array("client_id" => $clientId, "product" => $product));

        return $returnObj ? $state : (bool)$state;
    }

    public static function setProductSavedState($clientId, $product, $newState)
    {
        $oldState = self::getProductSavedState($clientId, $product, true);

        if (!$newState && $oldState) {
            $oldState->delete();
        }

        if ($newState) {
            if (!$oldState) {
                $newState = new ProductState();
                $newState->client_id = $clientId;
                $newState->product = $product;
                $newState->save();
            }
        }
    }

    public static function getProductState($clientId, $product)
    {
        switch ($product) {
            case 'phone':
                return self::getProductPhone($clientId);
            default:
                return false;
        }
    }

    public static function getAddProductStruct($clientId, $productStruct)
    {
        return array("account" => array("id" => $clientId), "products" => array($productStruct));
    }

    public static function getRemoveProductStruct($clientId, $product)
    {
        return array("account_id" => $clientId, "product" => $product);
    }

    public static function adminChangeStruct($superId, $email, $password, $isActive)
    {
        return array("client_id" => $superId, "admin" => array("email" => $email, "password" => $password, "active" => (bool)$isActive));
    }

}

