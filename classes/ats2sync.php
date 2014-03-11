<?php


class ats2sync
{
    public static function updateClient($clientIds)
    {
        global $db_ats;

        if(!is_array($clientIds))
            $clientIds = array($clientIds);

        foreach($clientIds as $id)
            if($id)
                $db_ats->Query("insert ignore into a_update_client (client_id) values (".$id.")");
    }
}
