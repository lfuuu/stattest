<?php

use app\classes\JSONQuery;

class SyncVirtPbx
{
    public static function create($clientId, $usageId)
    {
        $tarif = self::getTarif($usageId);
        $numbers = self::getNumbers($clientId, $usageId);

        $data = array(
                "client_id"  => (int)$clientId,
                "stat_product_id"  => (int)$usageId,
                "numbers"    => $numbers,
                "phones"     => $tarif["num_ports"],
                "faxes"      => (int)$tarif["is_fax"] ? 5 : 0,
                "record"     => (bool)$tarif["is_record"],
                "enable_web_call" => (bool)$tarif["is_web_call"],
                "disk_space" => (int)$tarif["space"]
                );

        return self::_send($tarif["ip"], "create", $data);
    }

    public static function stop($clientId, $usageId)
    {
        $tarif = self::getTarif($usageId);

        $data = array(
                "client_id"  => (int)$clientId,
                "stat_product_id"  => (int)$usageId,
                );

        return self::_send($tarif["ip"], "delete", $data);
    }

    public static function changeTarif($clientId, $usageId)
    {
        $tarif = self::getTarif($usageId);

        $data = array(
                "client_id"  => (int)$clientId,
                "stat_product_id"  => (int)$usageId,
                "phones"     => $tarif["num_ports"],
                "faxes"      => $tarif["is_fax"] ? 5 : 0,
                "record"     => (bool)$tarif["is_record"],
                "enable_web_call" => (bool)$tarif["is_web_call"],
                );

        return self::_send($tarif["ip"], "update", $data);
    }

    public static function addDid($clientId, $usageId, $number)
    {
        global $db;

        $tarif = self::getTarif($usageId);
        $region = $db->GetValue("select region from voip_numbers where number = '".$number."'");

        //if line without number, or trunk
        if (!$region)
            $region = $db->GetValue("SELECT region FROM `usage_voip` where E164 = '".$number."' and cast(now() as date) between actual_from and actual_to limit 1") ?: 99;

        return self::_send($tarif["ip"], "add_did", array(
                    "client_id" => (int)$clientId,
                    "stat_product_id" => (int)$usageId,
                    "numbers"   => array(array($number, (int)$region))
                    )
                );

    }

    public static function delDid($clientId, $usageId, $number)
    {
        global $db;

        $tarif = self::getTarif($usageId);
        $region = $db->GetValue("select region from voip_numbers where number = '".$number."'") ?: 99;

        return self::_send($tarif["ip"], "remove_did", array(
                    "client_id" => $clientId,
                    "stat_product_id" => $usageId,
                    "numbers"   => array(array($number, (int)$region))
                    )
                );

    }

    public static function setDiff($clientId, $usageId, &$diff)
    {
        $tarif = self::getTarif($usageId);

        if ($diff["add"])
        {
            $numbers = self::_getDiffNumbers($diff["add"]);

            self::_send($tarif["ip"], "add_did", array(
                        "client_id" => (int)$clientId,
                        "stat_product_id" => (int)$usageId,
                        "numbers"   => $numbers
                        )
                    );
        }

        if ($diff["del"])
        {
            $numbers = self::_getDiffNumbers($diff["del"]);

            self::_send($tarif["ip"], "remove_did", array(
                        "client_id" => (int)$clientId,
                        "stat_product_id" => (int)$usageId,
                        "numbers"   => $numbers
                        )
                    );
        }
    }

    private static function _getDiffNumbers(&$d)
    {
        $numbers = array();
        foreach($d as $k => $number)
        {
            $numbers[$number["number"]] = 1;
        }

        return array_keys($numbers);
    }

    private static function getTarif($usageId)
    {
        global $db;

        $row = $db->GetRow("
                SELECT
                    t.num_ports,
                    space,
                    is_record,
                    is_fax,
                    s.ip,
                    t.is_web_call
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

    private static function getNumbers($clientId, $usageId)
    {
        $list = VirtPbx::getInfo($clientId, $usageId);

        $numbers = array();
        foreach($list["numbers"] as $numberId => $n)
        {
            $number = ats2Numbers::getNumberById($clientId, $numberId, true);
            $numbers[] = array($number["number"], (int)$number["region"]);
        }

        return $numbers;
    }

    /**
    * Функция забора статистики с ВАТСа.
    * 
    * @param $clientId int id лицевого счета
    * @param $date date-string(format: YYYY-MM-DD) за какой день статистику надо получить
    * @param $statisticFunction string вызываемая функция стаистики
    * @param $statisticField string поле, в которым храниться результат, в возвращаемых дланных
    * @return mix полученное занчение
    */
    public static function getStatistic($clientId, $usageId, $date, $statisticFunction = "get_total_space_usage", $statisticField = "total")
    {
        $tarif = self::getTarif($usageId);

        $data = array(
                "client_id" => (int)$clientId,
                "stat_product_id" => (int)$usageId,
                "date" => $date
                );

        $result = self::_send($tarif["ip"], $statisticFunction, $data);

        if (isset($result[$statisticField]))
        {
            return $result[$statisticField];
        } else {
            throw new Exception(
                    (isset($result["errors"]) && $result["errors"]) ? 
                    implode("; ", $result["error"]) :  
                    "Ошибка получения статистики"
                    );
        }

    }

    private static function _send($address, $action, $data)
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
