<?php

use app\models\ClientContragent;
use app\models\CoreSyncIds;
use app\classes\Utils;

class SyncCoreHelper
{

    private static $allowClientStatusSQL = array("income", "negotiations", "work", "connecting", "testing", "debt", "operator");

    static function getFullClientStruct($superId) // only super client && conragent
    {
        global $db;

        $super = $db->GetRow("select id, name from client_super where id = '" . $superId . "'");

        $emails = array();

        $passwordAccount = null;
        $main_card_id = 0;

        $data = array("client" => array("id" => $super["id"], "name" => $super["name"]), "contragents" => array());

        $adminEmail = "";
        /*
        $attrs = [
            "id" => $superId,
            "type" => "admin_email",
        ];
        $isNeedAdminSync = !((bool)CoreSyncIds::findOne($attrs));
         */

        $contragents = ClientContragent::findAll(['super_id' => $superId]);
        foreach ($contragents as $contr) {
            $dataContragent = [
                'id' => $contr->id,
                'name' => $contr->name,
                'country' => $contr->country->alpha_3,
                'accounts' => [],
            ];

            $adminContactId = 0;
            foreach ($contr->getContracts() as $contract) {
                foreach ($contract->getAccounts() as $c) {
                    $isMainCard = strpos($c["client"], "/") === false;

                    if ($isMainCard) {

                        if (/*$isNeedAdminSync && */!$adminEmail) {
                            $passwordAccount = $c;
                            $contacts  = $c->getOfficialContact();
                            if (isset($contacts["email"])) {
                                foreach($contacts["email"] as $email) {
                                    $adminEmail = $email;
                                    break;
                                }
                            }

                            if (!$main_card_id) {
                                $main_card_id = $c->id;
                            }
                        }
                    }

                    //if (!$isMainCard && !in_array($c["status"], self::$allowClientStatusSQL)) continue;

                    //self::loadEmails($emails, $c["id"]);

                    /* add account event fiered next time
                    $_account = array("id" => $c["id"], "products" => self::getProducts($c["id"]));
                    $dataContragent["accounts"][] = $account;
                    */
                }
            }
            $data["contragents"][] = $dataContragent;
        }

        if (!$adminEmail && $main_card_id || !$passwordAccount) {
            $adminEmail = $main_card_id . "@mcn.ru";
        }

        if ($passwordAccount && !$passwordAccount->password) {
            $passwordAccount->password = Utils::password_gen();
            $passwordAccount->save();
        }

        $data["admin"] = array(
            "email" => $adminEmail, 
            "password" => $passwordAccount->password,
            "active" => false
        );

        return $data;
    }

    public static function getAccountStruct(\app\models\ClientAccount $cl)
    {
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
            return array("mnemonic" => "phone");
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
}

