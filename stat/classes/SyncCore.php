<?php

use app\models\CoreSyncIds;


class SyncCore
{

    private static function getCoreApiUrl()
    {
        return "https://".CORE_SERVER."/core/api/";
    }

    public static function addSuperClient($superId)
    {
        $struct = SyncCoreHelper::getFullClientStruct($superId);
        $action = "import_user_from_stat";

        if ($struct)
        {
            $accountSync = new CoreSyncIds;
            $accountSync->id = $superId;
            $accountSync->type = "super_client";
            $accountSync->external_id = "*" . $superId;

            try{
                $data = JSONQuery::exec(self::getCoreApiUrl().$action, $struct);
            }catch(Exception $e)
            {
                $accountSync->save();
                throw $e;
            }

            if (isset($data["data"]))
            {
                $accountSync->external_id = $data["data"]["client_id"];
            }

            $accountSync->save();
        }
    }

    public static function addAccount($clientId, $isResetProductState = false)
    {
        $cl = ClientCard::find('first', array("id" => $clientId));

        if (!$cl)
            throw new Exception("Клиент не найден");

        $superClientSync = CoreSyncIds::findOne(["type" => "super_client", "id" => $cl->super_id]);
        if (!$superClientSync)
        {
            event::go("add_super_client", $cl->super_id);
            event::go("add_account", $cl->id, true);
            return;
        }


        if ($isResetProductState)
        {
            $clientProducts = ProductState::find("first", array("client_id" => $clientId));

            if ($clientProducts)
                $clientProducts->delete();
        }


        $accountSync = CoreSyncIds::findOne(["type" => "account", "id" => $cl->id]);
        if (!$accountSync)
        {
            // addition card
            $struct = SyncCoreHelper::getAccountStruct($cl);
            $action = "add_accounts_from_stat";
            if ($struct)
            {
                try{
                    $data = JSONQuery::exec(self::getCoreApiUrl().$action, $struct);

                    if (isset($data["success"]))
                    {
                        $accountSync = new CoreSyncIds;
                        $accountSync->id = $cl->id;
                        $accountSync->type = "account";
                        $accountSync->external_id = "*" . $cl->id;
                        $accountSync->save();
                    }
                } catch(Exception $e)
                {

                    if ($e->getCode() == 535)//"Клиент с контрагентом c id "70954" не существует"
                    {
                        event::go("add_super_client", $cl->super_id);
                        event::go("add_account", $cl->id, true);
                    }

                    if ($e->getCode() == 538)//Контрагент с идентификатором "73273" не существует
                    {
                        event::go("add_super_client", $cl->super_id);
                        event::go("add_account", $cl->id, true);
                    }

                    if ($e->getCode() != 532) //Контрагент с лицевым счётом "1557" уже существует
                    {
                        throw $e;
                    } //для синхронизации продуктов
                }
            }
        }
        self::_checkNeedSyncProducts($cl->client);

    }

    private static function _checkNeedSyncProducts($client)
    {
        echo "\n== [_checkNeedSyncProducts](".$client.")\n";
        self::checkProductState('phone', array(0, $client));

        $vpbxIds =
            Yii::$app->db->createCommand("
                SELECT
                    u.id
                FROM usage_virtpbx u
                LEFT JOIN tarifs_virtpbx t ON (t.id = u.tarif_id)
                LEFT JOIN server_pbx s ON (s.id = u.server_pbx_id)
                WHERE
                    u.client = '$client'
                    AND u.actual_from <= cast(now() AS date)
                    AND u.actual_to >= cast(now() AS date)
            ")->queryAll();
        foreach ($vpbxIds as $statProductId) {
            self::checkProductState('vpbx', array($statProductId, $client));
        }
    }

    public static function addEmail($param)
    {
        $email = ClientContact::find("first", array("id" => $param["contact_id"], "client_id" => $param["client_id"]));

        $struct = array();

        if ($email->is_official)
        {
            $client = ClientCard::find("first", array("id" => $param["client_id"]))->getClient();
            $struct = SyncCoreHelper::getEmailStruct($email->data, $client->password);
        }

        $action = "add_user_from_stat";

        if ($struct)
        {
            JSONQuery::exec(self::getCoreApiUrl().$action, $struct);
        }
    }

    public static function updateAdminPassword($clientId)
    {
        $struct = false;
        $action = "sync_user_password_from_stat";
        $cl = ClientCard::find('first', array("id" => $clientId));

        if ($cl)
        {
            $client = $cl->getClient();

            if ($cl->id == $client->id) // is main card
            {
                $struct = SyncCoreHelper::getEmailStruct($client->id."@mcn.ru", $client->password?:password_gen());
            }
        }
        if ($struct)
        {
            JSONQuery::exec(self::getCoreApiUrl().$action, $struct);
        }
    }

    public static function checkProductState($product, $param)
    {
        if ($product == "phone" && !defined("PHONE_SERVER") || !PHONE_SERVER) return;

        list($usageId, $client) = $param;

        $client = ClientCard::find("first", array("client" => $client));

        if (!$client) return false;

        if ($product == 'vpbx') {
            $statProductId = $usageId;
        } else {
            $statProductId = 0;
        }

        $currentState = SyncCoreHelper::getProductSavedState($client->id, $product, $statProductId);
        $newState = SyncCoreHelper::getProductState($client->id, $product, $statProductId);

        SyncCoreHelper::setProductSavedState($client->id, $product, $statProductId, (bool)$newState);

        $action = null;

        if (!$currentState && $newState)
        {
            $action = "add";
            $actionJSON = "add_products_from_stat";
            $struct = SyncCoreHelper::getAddProductStruct($client->id, $newState);
        }else if($currentState && !$newState)
        {
            $action = "remove";
            $actionJSON = "remove_product";
            $struct = SyncCoreHelper::getRemoveProductStruct($client->id, $product);
        }

        if ($action && $struct)
        {
            JSONQuery::exec(self::getCoreApiUrl().$actionJSON, $struct);
        }

        return $action;
    }

    public static function adminChanged($clientId)
    {
        $action = "update_admin_from_stat";

        $client = ClientCard::find("first", array("id" => $clientId));

        if (strpos($client->client, "/") !== false) {
            echo "\n not main card";
            return;
        }

        if ($client)
        {
            $email = $client->id."@mcn.ru";
            $cc = ClientContact::find("first", array("id" => $client->admin_contact_id, "is_official" => 1, "is_active" => 1, "type" => 'email'));

            if ($cc)
                $email = $cc->data;

            $struct = SyncCoreHelper::adminChangeStruct($client->super_id, $email, $client->password?:password_gen(), (bool)$client->admin_is_active);
            if ($struct)
            {
                JSONQuery::exec(self::getCoreApiUrl().$action, $struct);
            }

        }


    }
}
