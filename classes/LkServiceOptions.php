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

        $vpbx = $db->GetValue($q = "select u.id from usage_virtpbx u, clients c where c.id = '".$this->clientId."' and c.client = u.client");

        if ($vpbx)
        {
            $data["tarif_change"] = 1;
            $data["disable"] = 1;
        } else {
            $data["connect"] = 1;
        }

        return $data;
    }
}
