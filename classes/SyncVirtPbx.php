<?php

class SyncVirtPbx
{
    private static $clientId = 0;

    public function create($clientId)
    {
        self::$clientId = $clientId;

        $tarif = self::getTarif();
        $numbers = self::getNumbers();

        $data = array(
                "client_id"  => self::$clientId,
                "numbers"    => $numbers,
                "phones"     => $tarif["num_ports"],
                "faxes"      => $tarif["is_fax"] ? 5 : 0,
                "record"     => (bool)$tarif["is_record"],
                "disk_space" => $tarif["space"]
                );

        return self::_send($tarif["ip"], "create", $data);
    }

    public function changeTarif($clientId, $usageId)
    {
        $tarif = self::getTarif($clientId, $usageId);

        $data = array(
                "client_id"  => $clientId,
                "phones"     => $tarif["num_ports"],
                "faxes"      => $tarif["is_fax"] ? 5 : 0,
                "record"     => (bool)$tarif["is_record"],
                );

        return self::_send($tarif["ip"], "update", $data);
    }

    public function addDid($clientId, $number)
    {
        global $db;

        $tarif = self::getTarif($clientId);
        $region = $db->GetValue("select region from voip_numbers where number = '".$number."'") ?: 99;

        return self::_send($tarif["ip"], "add_did", array(
                    "client_id" => $clientId,
                    "numbers"   => array(array($number, $region))
                    )
                );

    }

    public function delDid($clientId, $number)
    {
        global $db;

        $tarif = self::getTarif($clientId);
        $region = $db->GetValue("select region from voip_numbers where number = '".$number."'") ?: 99;

        return self::_send($tarif["ip"], "remove_did", array(
                    "client_id" => $clientId,
                    "numbers"   => array(array($number, $region))
                    )
                );

    }

    public function setDiff($clientId, &$diff)
    {
        self::$clientId = $clientId;

        $tarif = self::getTarif();

        if ($diff["add"])
        {
            $numbers = self::_getDiffNumbers($diff["add"]);

            self::_send($tarif["ip"], "add_did", array(
                        "client_id" => self::$clientId,
                        "numbers"   => $numbers
                        )
                    );
        }

        if ($diff["del"])
        {
            $numbers = self::_getDiffNumbers($diff["del"]);

            self::_send($tarif["ip"], "remove_did", array(
                        "client_id" => self::$clientId,
                        "numbers"   => $numbers
                        )
                    );
        }
    }

    private function _getDiffNumbers(&$d)
    {
        $numbers = array();
        foreach($d as $k => $number)
        {
            $numbers[$number["number"]] = 1;
        }

        return array_keys($numbers);
    }

    private function getTarif($clientId = null, $usageId = null)
    {
        global $db;

        if ($clientId === null)
            $clientId = self::$clientId;

        if ($usageId === null)
        {
            $usageId = $db->getValue("
                    SELECT
                        max(u.id) as virtpbx_id
                    FROM
                        usage_virtpbx u, clients c
                    WHERE
                            c.id = '".$clientId."'
                        AND c.client = u.client
                        AND actual_from <= cast(now() AS date)
                        AND actual_to >= cast(now() AS date)
                    ");
        }

        //

        $row = $db->GetRow("
                SELECT
                    t.num_ports,
                    space,
                    is_record,
                    is_fax,
                    s.ip
                FROM (select (
                        select 
                            id_tarif 
                        from 
                            log_tarif 
                        where 
                                id_service = u.id 
                            and service = 'usage_virtpbx' 
                            and date_activation < now() 
                        ORDER BY 
                        date_activation DESC, id DESC LIMIT 1
                        ) as tarif_id, 
                        server_pbx_id
                    FROM usage_virtpbx u
                    WHERE u.id = '".$usageId."' ) u
                LEFT JOIN tarifs_virtpbx t ON (t.id = u.tarif_id)
                LEFT JOIN server_pbx s ON (s.id = u.server_pbx_id)

                ");

        return $row;
    }

    private function getNumbers()
    {
        $list = VirtPbx::getList(self::$clientId);

        $numbers = array();
        foreach($list["numbers"] as $numberId => $n)
        {
            $number = ats2Numbers::getNumberById(self::$clientId, $numberId, true);
            $numbers[] = array($number["number"], $number["region"]);
        }

        return $numbers;
    }

    private function _send($address, $action, $data)
    {
        if (!defined("VIRTPBX_URL"))
            throw new Exception("Не установлен URL для связи с VPBX", 500);

        $url = VIRTPBX_URL;

        if (defined("VIRTPBX_TEST_ADDRESS"))
            $address = VIRTPBX_TEST_ADDRESS; 

        $url = strtr($url, array("[address]" => $address, "[action]" => $action));

        return JSONQuery::exec($url, $data);
    }

}
