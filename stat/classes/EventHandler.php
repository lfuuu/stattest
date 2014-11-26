<?php
use app\dao\BillDao;
use app\classes\StatModule;

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

    public function updateBalance($clientId)
    {
        include_once INCLUDE_PATH."bill.php";

        $card = ClientCard::find($clientId);
        if ($card) {
            StatModule::newaccounts()->update_balance($card->id, $card->currency);
        }
    }

    public function updateSubscribeMass()
    {
        BillDao::me()->updateSubscriptionForAllClientAccounts();
    }

}
