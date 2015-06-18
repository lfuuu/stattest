<?php
use app\models\ClientAccount;

class EventHandler
{
    public static function companyChanged($clientId)
    {
        $card = ClientCard::find("first", array("id" => $clientId));

        if (!$card->isMainCard()) return false;

        $card->super->name = $card->company;
        $card->super->save();

        $card->contragent->name = $card->company;
        $card->contragent->country_id = $card->country_id;
        $card->contragent->save();
    }

    public static function updateBalance($clientId)
    {
        ClientAccount::dao()->updateBalance($clientId);
    }
}
