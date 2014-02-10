<?php


class EventHandler
{
    public function companyChanged($clientId)
    {
        $card = ClientCard::find("first", array("id" => $clientId));

        if (!$card->isMainCard()) return false;

        $card->super->name = $card->company;
        $card->super->save();

        $card->contragent->name = $card->company;
        $card->contragent->save();
    }
}
