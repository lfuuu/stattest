<?php



class SyncCore
{

    public function addSuperClient($superId)
    {
        $struct = SyncCoreHelper::getFullClientStruct($superId);
        $action = "import_user_from_stat";

        if ($struct)
        {
            JSONQuery::exec(CORE_API_URL.$action, $struct);
        }

    }

    public function addAccount($clientId)
    {
        $cl = ClientCard::find('first', array("id" => $clientId));
        // addition card
        $struct = SyncCoreHelper::getAccountStruct($cl);
        $action = "add_accounts_from_stat";
        if ($struct)
        {
            try{
                JSONQuery::exec(CORE_API_URL.$action, $struct);
            } catch(Exception $e)
            {
                echo "-----------";
                if ($e->getCode() == 538)//Контрагент с идентификатором "73273" не существует
                {
                    event::go("add_super_client", $cl->super_id);
                    event::go("add_account", $cl->id);
                }
                echo "-----------";
            }
        }
    }

    public function addEmail($param)
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
            JSONQuery::exec(CORE_API_URL.$action, $struct);
        }
    }

    public function updateAdminPassword($clientId)
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
            JSONQuery::exec(CORE_API_URL.$action, $struct);
        }


    }

    public function checkProductState($product, $client)
    {
        $client = ClientCard::find("first", array("client" => $client));

        if (!$client) return false;

        $action = "add_products_from_stat";

        $currentState = SyncCoreHelper::getProductSavedState($client->id, $product);
        $newState = SyncCoreHelper::getProductState($client->id, $product);

        SyncCoreHelper::setProductSavedState($client->id, $product, (bool)$newState);

        if (!$currentState && $newState)
        {
            $struct = SyncCoreHelper::getAddProductStruct($client->id, $newState);

            if ($struct)
            {
                JSONQuery::exec(CORE_API_URL.$action, $struct);
            }
        }
    }

    public function adminChanged($clientId)
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
                JSONQuery::exec(CORE_API_URL.$action, $struct);
            }

        }


    }
}
