<?php
use app\models\ClientAccount;

class EventHandler
{
    public static function updateBalance($clientId)
    {
        ClientAccount::dao()->updateBalance($clientId);
    }
}
