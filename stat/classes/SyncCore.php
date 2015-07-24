<?php

use app\models\CoreSyncIds;
use app\classes\api\ApiCore;

class SyncCore
{
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
                $data = ApiCore::exec($action, $struct);
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
        $account = \app\models\ClientAccount::findOne($clientId);

        if (!$account)
            throw new Exception("Клиент не найден");

        $superClientSync = CoreSyncIds::findOne(["type" => "super_client", "id" => $account->super_id]);
        if (!$superClientSync)
        {
            //event::go("add_super_client", $cl->super_id);
            //event::go("add_account", $cl->id, true);
            //return;
            SyncCore::AddSuperClient($account->super_id);
        }


        if ($isResetProductState)
        {
            $clientProducts = ProductState::find("first", array("client_id" => $clientId));

            if ($clientProducts)
                $clientProducts->delete();
        }


        $accountSync = CoreSyncIds::findOne(["type" => "account", "id" => $account->id]);
        if (!$accountSync)
        {
            // addition card
            $struct = SyncCoreHelper::getAccountStruct($account);
            $action = "add_accounts_from_stat";
            if ($struct)
            {
                try{
                    $data = ApiCore::exec($action, $struct);

                    if (isset($data["success"]))
                    {
                        $accountSync = new CoreSyncIds;
                        $accountSync->id = $account->id;
                        $accountSync->type = "account";
                        $accountSync->external_id = "*" . $account->id;
                        $accountSync->save();
                    }
                } catch(Exception $e)
                {

                    if ($e->getCode() == 535)//"Клиент с контрагентом c id "70954" не существует"
                    {
                        event::go("add_super_client", $account->super_id);
                        event::go("add_account", $account->id, true);
                    }

                    if ($e->getCode() == 538)//Контрагент с идентификатором "73273" не существует
                    {
                        event::go("add_super_client", $account->super_id);
                        event::go("add_account", $account->id, true);
                    }

                    if ($e->getCode() != 532) //Контрагент с лицевым счётом "1557" уже существует
                    {
                        throw $e;
                    } //для синхронизации продуктов
                }
            }
        }

    }

    public static function addEmail($param)
    {
        $email = ClientContact::find("first", array("id" => $param["contact_id"], "client_id" => $param["client_id"]));

        $struct = array();

        if ($email->is_official)
        {
            $account = \app\models\ClientAccount::findOne($param["client_id"]);
            $struct = SyncCoreHelper::getEmailStruct($email->data, $account->password);
        }

        $action = "add_user_from_stat";

        if ($struct)
        {
            ApiCore::exec($action, $struct);
        }
    }

    public static function checkProductState($product, $param)
    {
        if ($product == "phone" && !isset(\Yii::$app->params['PHONE_SERVER']) || !\Yii::$app->params['PHONE_SERVER']) return;

        list($usageId, $client) = $param;

        $account = \app\models\ClientAccount::findOne(['client' => $client]);

        if (!$account) return false;

        $currentState = SyncCoreHelper::getProductSavedState($account->id, $product);
        $newState = SyncCoreHelper::getProductState($account->id, $product);

        SyncCoreHelper::setProductSavedState($account->id, $product, (bool)$newState);

        $action = null;

        if (!$currentState && $newState)
        {
            $action = "add";
            $actionJSON = "add_products_from_stat";
            $struct = SyncCoreHelper::getAddProductStruct($account->id, $newState);
        }else if($currentState && !$newState)
        {
            $action = "remove";
            $actionJSON = "remove_product";
            $struct = SyncCoreHelper::getRemoveProductStruct($account->id, $product);
        }

        if ($action && $struct)
        {
            ApiCore::exec($actionJSON, $struct);
        }

        return $action;
    }

    public static function adminChanged($clientId)
    {
        $action = "update_admin_from_stat";

        $account = \app\models\ClientAccount::findOne($clientId);

        if (strpos($account->client, "/") !== false) {
            echo "\n not main card";
            return;
        }

        if ($account)
        {
            $email = $account->id."@mcn.ru";
            $cc = ClientContact::find("first", array("id" => $account->admin_contact_id, "is_official" => 1, "is_active" => 1, "type" => 'email'));

            if ($cc)
                $email = $cc->data;

            $struct = SyncCoreHelper::adminChangeStruct($account->super_id, $email, $account->password?:password_gen(), (bool)$account->admin_is_active);
            if ($struct)
            {
                ApiCore::exec($action, $struct);
            }

        }


    }
}
