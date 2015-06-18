<?php

use app\classes\api\ApiVpbx;

class SyncVirtPbx
{
    public static function create($clientId, $usageId)
    {
        ApiVpbx::create($clientId, $usageId);
    }

    public static function stop($clientId, $usageId)
    {
        ApiVpbx::stop($clientId, $usageId);
    }

    public static function changeTarif($clientId, $usageId)
    {
        ApiVpbx::updateTariff($clientId, $usageId);
    }

    public static function addDid($clientId, $usageId, $number)
    {
        global $db;

        $region = $db->GetValue("select region from voip_numbers where number = '".$number."'");

        //if line without number, or trunk
        if (!$region)
            $region = $db->GetValue("SELECT region FROM `usage_voip` where E164 = '".$number."' and cast(now() as date) between actual_from and actual_to limit 1") ?: 99;

        return ApiVpbx::exec(
            ApiVpbx::getVpbxHost($usageId),
            'add_did', 
            [
                'client_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
                'numbers'   => [[$number, (int)$region]],
            ]
        );
    }

    public static function delDid($clientId, $usageId, $number)
    {
        global $db;

        $region = $db->GetValue("select region from voip_numbers where number = '".$number."'") ?: 99;

        return ApiVpbx::exec(
            ApiVpbx::getVpbxHost($usageId),
            'remove_did',
            [
                'client_id' => (int)$clientId,
                'stat_product_id' => (int)$usageId,
                'numbers'   => [[$number, (int)$region]],
            ]
        );
    }

    public static function setDiff($clientId, $usageId, &$diff)
    {
        if ($diff["add"])
        {
            $numbers = self::_getDiffNumbers($diff["add"]);

            ApiVpbx::exec(
                ApiVpbx::getVpbxHost($usageId),
                'add_did',
                [
                    'client_id' => (int)$clientId,
                    'stat_product_id' => (int)$usageId,
                    'numbers'   => $numbers,
                ]
            );
        }

        if ($diff["del"])
        {
            $numbers = self::_getDiffNumbers($diff["del"]);

            ApiVpbx::exec(
                ApiVpbx::getVpbxHost($usageId),
                'remove_did',
                [
                    'client_id' => (int)$clientId,
                    'stat_product_id' => (int)$usageId,
                    'numbers'   => $numbers,
                ]
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

}
