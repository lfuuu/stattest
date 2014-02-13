<?php


class LkServiceOptions
{
    private $service = "";
    private $clientId = 0;

    public function __construct($service, $clientId)
    {
        $this->service = $service;
        $this->clientId = $clientId;
    }

    public function getOptions()
    {
        switch($this->service)
        {
            case 'vpbx': return $this->_vpbx();
            default: return array();
        }
    }

    private function _vpbx()
    {
        global $db;
        $data = array("connect" => 0, "tarif_change" => 0, "disable" => 0);


        $vpbx = $db->GetRow(
                $q = "
                SELECT
                    u.id,
                    IF ((`u`.`actual_from` <= NOW()) AND (`u`.`actual_to` > NOW()), 1, 0) AS `actual`
                FROM 
                    usage_virtpbx u, 
                    clients c
                WHERE
                        c.id = '".$this->clientId."'
                    and c.client = u.client
                order by actual desc, u.id desc
                limit 1
                ");


        if ($vpbx && $vpbx["actual"])
        {
            $data["tarif_change"] = 1;
            $data["disable"] = 1;
        } else {
            $data["connect"] = 1;
        }

        return $data;
    }
}
